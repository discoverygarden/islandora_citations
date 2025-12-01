<?php

namespace Drupal\islandora_citations\Form;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\islandora_citations\IslandoraCitationsHelper;
use Drupal\path_alias\AliasManagerInterface;
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
   * Constructor.
   */
  public function __construct(
    IslandoraCitationsHelper $citationHelper,
    RouteMatchInterface $route_match,
    EntityTypeManagerInterface $entity_type_manager,
    AliasManagerInterface $pathAliasManager,
    LoggerInterface $logger,
  ) {
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
  public function buildForm(array $form, FormStateInterface $form_state, $configuration = NULL) {
    $cslItems = $this->citationHelper->getCitationEntityList();

    if (!$form_state->isRebuilding()) {
      $default_csl = isset($cslItems[$configuration['default_csl']]) ? $configuration['default_csl'] : FALSE;
      if ($default_csl === FALSE) {
        $this->logger->error("Default CSL, {$configuration['default_csl']} not found in citation styles.");
      }
    }
    else {
      $default_csl = $form_state->getValue('csl_list');
    }
    $csl = $default_csl ? $this->getCitation($default_csl) : '';

    $form['#cache']['contexts'][] = 'url';
    $form['#theme'] = 'display_citations';

    $form['csl_list'] = [
      '#type' => 'select',
      '#options' => $cslItems,
      '#empty_option' => $this->t('- Select csl -'),
      '#default_value' => $default_csl,
      '#ajax' => [
        'callback' => '::renderAjaxCitation',
        'wrapper' => 'formatted-citation',
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

    return $form;
  }

  /**
   * Render CSL response on ajax call.
   */
  public function renderAjaxCitation(array $form, FormStateInterface $form_state) {
    return $form['formatted-citation'];
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
  public function getCitation($csl_name) {
    try {
      // Method call to render citation.
      $rendered = $this->renderCitation($csl_name);
      return $rendered['data'] ?? NULL;
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

    // Pass the current date to Accessed.
    $current_date = new DrupalDateTime('now');

    $date_parts = [
      $current_date->format('Y'),
      $current_date->format('m'),
      $current_date->format('d'),
    ];

    $citationItems[0]->accessed = (object) ['date-parts' => [$date_parts]];

    $style = $this->citationHelper->loadStyle($csl_name);
    return $this->citationHelper->renderWithCiteproc($citationItems, $style);
  }

}
