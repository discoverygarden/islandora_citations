<?php

namespace Drupal\islandora_citations\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, FormBuilderInterface $formBuilder, IslandoraCitationsHelper $citationHelper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
    $this->formBuilder = $formBuilder;
    $this->citationHelper = $citationHelper;
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
      $container->get('islandora_citations.helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $cite_this_form = $this->formBuilder->getForm('Drupal\islandora_citations\Form\SelectCslForm');
    if ($cite_this_form['error_handling_element']['#markup']) {
      // Hide the entire block.
      return NULL;
    }

    if (!empty($this->citationHelper->getCitationEntityList())) {
      $build['form'] = $cite_this_form;
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
        '#options' => $cslTypesNames,
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

}
