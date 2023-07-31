<?php

namespace Drupal\islandora_citations\Plugin\Block;

use Drupal\Core\Block\BlockBase;
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
   * @param \Drupal\Core\Form\FormBuilderInterface $formBuilder
   *   The form builder instance.
   * @param \Drupal\islandora_citations\IslandoraCitationsHelper $citationHelper
   *   The CitationHelper instance.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $formBuilder, IslandoraCitationsHelper $citationHelper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
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
      $container->get('form_builder'),
      $container->get('islandora_citations.helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['form'] = $this->formBuilder->getForm('Drupal\islandora_citations\Form\SelectCslForm');
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'default_csl' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    if (empty($this->citationHelper->getCitationEntityList())) :
      $form['add_citation'] = [
        '#type' => 'link',
        '#title' => [
          '#markup' => $this->t('Please add CSL from here'),
        ],
        '#url' => Url::fromRoute('entity.islandora_citations.add_form'),
      ];
      else :
        $config = $this->getConfiguration();
        $defaultCSL = $config['default_csl'];
        $form['csl_list'] = [
          '#type' => 'select',
          '#title' => $this->t('Select default CSL'),
          '#options' => $this->citationHelper->getCitationEntityList(),
          '#empty_option' => $this->t('- Select csl -'),
          '#attributes' => ['aria-label' => $this->t('Select CSL')],
          '#default_value' => $defaultCSL,
        ];
      endif;

      return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->configuration['default_csl'] = $values['csl_list'];
  }

}
