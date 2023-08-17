<?php

namespace Drupal\islandora_citations\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\islandora_citations\IslandoraCitationsHelper;
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
   * {@inheritdoc}
   */
  public function __construct(IslandoraCitationsHelper $citationHelper, RouteMatchInterface $route_match, EntityTypeManagerInterface $entity_type_manager) {
    $this->citationHelper = $citationHelper;
    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('islandora_citations.helper'),
      $container->get('current_route_match'),
      $container->get('entity_type.manager')
      $container->get('current_route_match'),
      $container->get('entity_type.manager')
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
    $blocks = $block_storage->loadMultiple();
    $default_csl = '';
    foreach ($blocks as $block) {
      $settings = $block->get('settings');
      if (isset($settings['id'])) {
        if ($settings['id'] == 'islandora_citations_display_citations') {
          $default_csl = !empty($settings['default_csl']) ? $settings['default_csl'] : '';
        }
      }
    }
    if (empty($this->citationHelper->getCitationEntityList())) :
      $form['add_citation'] = [
        '#type' => 'link',
        '#title' => [
          '#markup' => $this->t('Please add CSL from here'),
        ],
        '#url' => Url::fromRoute('entity.islandora_citations.add_form'),
      ];
    else :
      $form['csl_list'] = [
        '#type' => 'select',
        '#options' => $this->citationHelper->getCitationEntityList(),
        '#empty_option' => $this->t('- Select csl -'),
        '#default_value' => $default_csl,
        '#default_value' => $default_csl,
        '#ajax' => [
          'callback' => '::renderCitation',
          'wrapper' => 'formatted-citation',
          'method' => 'html',
          'event' => 'change',
        ],
        '#attributes' => ['aria-label' => $this->t('Select CSL')],
        '#theme_wrappers' => [],
      ];
      $default_citation = !empty($default_csl) ? $this->getDefaultCitation($default_csl) : '';
      $form['formatted-citation'] = [
        '#type' => 'item',
        '#markup' => '<div id="formatted-citation">' . !empty($default_csl) ? $this->getDefaultCitation($default_csl) : '' . '</div>',
        '#theme_wrappers' => [],
      ];
      $form['actions']['submit'] = [
        '#type' => 'button',
        '#value' => $this->t('Copy Citation'),
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
    endif;
    $form['#cache']['contexts'][] = 'url';
    $form['#theme'] = 'display_citations';
    return $form;
  }

  /**
   * Render CSL response on ajax call.
   */
  public function renderCitation(array $form, FormStateInterface $form_state) {
    $csl_name = $form_state->getValue('csl_list');
    if ($csl_name == '') {
      return [
        '#children' => '',
      ];
    }
    $entity = $this->routeMatch->getParameter('node');
    $citationItems[] = $this->citationHelper->encodeEntityForCiteproc($entity);


    $style = $this->citationHelper->loadStyle($csl_name);

    $rendered = $this->citationHelper->renderWithCiteproc($citationItems, $style);

    $response = [
      '#children' => $rendered['data'],
    ];
    return $form['data'] = $response;
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
    $entity = $this->routeMatch->getParameter('node');
    if (empty($csl_name)) {
      return $this->t('Select CSL');
    }
    try {
      $citationItems[] = $this->citationHelper->encodeEntityForCiteproc($entity);
      $style = $this->citationHelper->loadStyle($csl_name);
      $rendered = $this->citationHelper->renderWithCiteproc($citationItems, $style);
      return $rendered['data'];
    }
    catch (\Throwable $e) {
    catch (\Throwable $e) {
      return $e->getMessage();
    }
  }

}
