<?php

namespace Drupal\islandora_citations\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\islandora_citations\IslandoraCitationsHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a display citations block.
 *
 * @Block(
 *   id = "islandora_citations_display_citations",
 *   admin_label = @Translation("Display Citations"),
 *   category = @Translation("Custom")
 * )
 */
class DisplayCitationsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Citation helper service.
   *
   * @var Drupal\islandora_citations\IslandoraCitationsHelper
   */
  protected $citationHelper;

  /**
   * The current route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Construct a new DisplayCitationsBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\Core\Form\FormBuilderInterface $formBuilder
   *   The form builder instance.
   * @param \Drupal\islandora_citations\IslandoraCitationsHelper $citationHelper
   *   The CitationHelper instance.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, FormBuilderInterface $formBuilder, IslandoraCitationsHelper $citationHelper, RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
    $this->formBuilder = $formBuilder;
    $this->citationHelper = $citationHelper;
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('form_builder'),
      $container->get('islandora_citations.helper'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $cite_this_form = $this->formBuilder->getForm('Drupal\islandora_citations\Form\SelectCslForm');
    $build['form'] = $cite_this_form;

    if ($cite_this_form['error_handling_element']['#markup'] == 1) {
      // Hide entire block due to error.
      return [];
    }

    if (!empty($this->citationHelper->getCitationEntityList())) {
      return $build;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'default_csl' => '',
      'default_csl_type' => 'Webpage',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    if (empty($this->citationHelper->getCitationEntityList())) {
      $form['add_citation'] = [
        '#type' => 'link',
        '#title' => [
          '#markup' => $this->t('Please add CSL from here'),
        ],
        '#url' => Url::fromRoute('entity.islandora_citations.add_form'),
      ];
    }
    else {
      $config = $this->getConfiguration();
      $defaultCSL = $config['default_csl'];
      $defaultCSLType = $config['default_csl_type'];
      $form['csl_list'] = [
        '#type' => 'select',
        '#title' => $this->t('Select default CSL'),
        '#options' => $this->citationHelper->getCitationEntityList(),
        '#empty_option' => $this->t('- Select csl -'),
        '#attributes' => ['aria-label' => $this->t('Select CSL')],
        '#default_value' => $defaultCSL,
      ];
      $cslTypesVocab = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree('csl_type');
      foreach ($cslTypesVocab as $vocab) {
        $cslTypesNames[$vocab->name] = $vocab->name;
      }
      $form['field_csl_type'] = [
        '#type' => 'select',
        '#title' => $this->t('Object Type (Citation)'),
        '#options' => $cslTypesNames ?? [],
        '#empty_option' => $this->t('- Select Citation -'),
        '#attributes' => ['aria-label' => $this->t('Select Citation')],
        '#default_value' => $defaultCSLType,
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->configuration['default_csl'] = $values['csl_list'];
    $this->configuration['default_csl_type'] = $values['field_csl_type'];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    // Retrieve the node ID.
    $node = $this->routeMatch->getParameter('node');
    $node_id = $node ? $node->id() : NULL;

    // Return cache tags.
    if ($node_id) {
      return ['node:' . $node_id];
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['route']);
  }

}
