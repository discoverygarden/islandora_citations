<?php

namespace Drupal\islandora_citations\Form;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\islandora_citations\IslandoraCitationsHelper;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implementing a ajax form.
 */
class SelectCslForm extends FormBase {

  /**
   * Citation helper service.
   *
   * @var Drupal\islandora_citations\IslandoraCitationsHelper
   */
  protected $citationHelper;

  /**
   * CSL type value from block.
   *
   * @var string
   */
  private $blockCSLType;

  /**
   * CSL type value from block.
   *
   * @var string
   */
  private $blockCSLAccessedDateFormate;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Definition of path alias manager.
   *
   * @var \Drupal\path_alias\AliasManager
   */
  protected $pathAliasManager;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * {@inheritdoc}
   */
  public function __construct(IslandoraCitationsHelper $citationHelper,
                              RouteMatchInterface $route_match,
                              EntityTypeManagerInterface $entity_type_manager,
                              AliasManagerInterface $pathAliasManager,
                              LoggerInterface $logger) {
    $this->citationHelper = $citationHelper;
    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entity_type_manager;
    $this->pathAliasManager = $pathAliasManager;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('islandora_citations.helper'),
      $container->get('current_route_match'),
      $container->get('entity_type.manager'),
      $container->get('path_alias.manager'),
      $container->get('logger.factory')->get('islandora_citations')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'select_csl_ajax_submit';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $block_storage = $this->entityTypeManager->getStorage('block');
    // Check if the value is set in block, newly added field.
    // Pass it in renderCitation.
    $blocks = $block_storage->loadMultiple();
    $cslItems = $this->citationHelper->getCitationEntityList();
    $default_csl = array_values($cslItems)[0];
    foreach ($blocks as $block) {
      $settings = $block->get('settings');
      if (isset($settings['id'])) {
        if ($settings['id'] == 'islandora_citations_display_citations') {
          $default_csl = !empty($settings['default_csl']) ? $settings['default_csl'] : array_values($cslItems)[0];
          $this->blockCSLType = $settings['default_csl_type'];
          $this->blockCSLAccessedDateFormate = $settings['csl_accessed_date_format'] ?? '';
        }
      }
    }
    // Check default csl exist or not.
    if (!array_key_exists($default_csl, $cslItems)) {
      $default_csl = array_values($cslItems)[0];
    }
    $csl = !empty($default_csl) ? $this->getDefaultCitation($default_csl) : '';

    // We receive error message as a string, and then we display same string
    // as output.
    // We expect output in a specific format when there is no error as below
    // <div class="csl-bib-body">
    // <div class="csl-entry">“Text_Output”</div>
    // </div>.
    // Based on `csl` text output, we will do the error handling.
    // When HTML output is not as expected, add a form element which indicates
    // we received error.
    if (!str_starts_with($csl, '<div class="csl-bib-body">')) {
      // Add a custom markup element to the form.
      $form['error_handling_element'] = [
        '#markup' => 'Form with error',
      ];

      // Log error message.
      $this->logger->error($csl);

      return $form;
    }

    $form['csl_list'] = [
      '#type' => 'select',
      '#options' => $cslItems,
      '#empty_option' => $this->t('- Select csl -'),
      '#default_value' => $default_csl,
      '#ajax' => [
        'callback' => '::renderAjaxCitation',
        'wrapper' => 'formatted-citation',
        'method' => 'html',
        'event' => 'change',
      ],
      '#attributes' => ['aria-label' => $this->t('Select CSL')],
      '#theme_wrappers' => [],
    ];
    $form['formatted-citation'] = [
      '#type' => 'item',
      '#markup' => '<div id="formatted-citation">' . $csl . '</div>',
      '#theme_wrappers' => [],
    ];

    $form['actions']['submit'] = [
      '#type' => 'button',
      '#value' => $this->t('Copy to Clipboard'),
      '#attributes' => [
        'onclick' => 'return false;',
        'class' => ['clipboard-button'],
        'data-clipboard-target' => '#formatted-citation',
      ],
      '#attached' => [
        'library' => [
          'islandora_citations/drupal',
        ],
      ],
    ];

    $form['#cache']['contexts'][] = 'url';
    $form['#theme'] = 'display_citations';
    return $form;
  }

  /**
   * Render CSL response on ajax call.
   */
  public function renderAjaxCitation(array $form, FormStateInterface $form_state) {
    $csl_name = $form_state->getValue('csl_list');
    if ($csl_name == '') {
      return [
        '#children' => '',
      ];
    }

    try {
      // Method call to render citation.
      $rendered = $this->renderCitation($csl_name);
      $response = [
        '#children' => $rendered['data'],
      ];

      return $response;
    }
    catch (\Throwable $e) {
      return $e->getMessage();
    }
  }

  /**
   * Submitting the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * Fetching results for default csl.
   *
   * @param string $csl_name
   *   Block default csl name.
   */
  public function getDefaultCitation($csl_name) {
    if (empty($csl_name)) {
      return $this->t('Select CSL');
    }
    try {
      // Method call to render citation.
      $rendered = $this->renderCitation($csl_name);
      return $rendered['data'];
    }
    catch (\Throwable $e) {
      return $e->getMessage();
    }
  }

  /**
   * Get rendered data.
   *
   * @param string $csl_name
   *   Block default csl name.
   *
   * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
   */
  private function renderCitation($csl_name): ?array {
    $entity = $this->routeMatch->getParameter('node');
    $citationItems[] = $this->citationHelper->encodeEntityForCiteproc($entity);
    $blockCSLType = $this->blockCSLType;
    if (!isset($citationItems[0]->type)) {
      $citationItems[0]->type = $blockCSLType;
    }

    // If no data for URL field, pass node url.
    if (empty($citationItems[0]->URL)) {
      $node_url = $this->pathAliasManager->getAliasByPath('/node/' . $entity->id());
      $citationItems[0]->URL = Url::fromUserInput($node_url)->setAbsolute()->toString();
    }

    // If Accessed is configured, add current date.
    if (!empty($this->blockCSLAccessedDateFormate)) {
      $current_date = new DrupalDateTime('now');
      $citationItems[0]->URL = $citationItems[0]->URL . ' ' .
        $current_date->format($this->blockCSLAccessedDateFormate);
    }

    $style = $this->citationHelper->loadStyle($csl_name);
    return $this->citationHelper->renderWithCiteproc($citationItems, $style);
  }

}
