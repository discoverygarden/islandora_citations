<?php

namespace Drupal\islandora_citations\Plugin\Field\FieldFormatter;

use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'CSL Date' formatter.
 *
 * @FieldFormatter(
 *   id = "islandora_citations_csl_date",
 *   label = @Translation("CSL Date"),
 *   field_types = {
 *     "datetime"
 *   }
 * )
 */
class CslDateFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * Date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * Constructs a StringFormatter instance.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings settings.
   * @param Drupal\Core\Datetime\DateFormatter $dateFormatter
   *   The date formatter.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, DateFormatter $dateFormatter) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->dateFormatter = $dateFormatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    $cslFields = $items->getFieldDefinition()->getThirdPartySetting('islandora_citations', 'csl_field');
    if (empty($cslFields) || empty($items->value)) {
      return [];
    }
    foreach ($cslFields as $value) {
      foreach ($items as $item) {
        $datetime = new DrupalDateTime($item->value, 'UTC');
        $date = $this->dateFormatter->format($datetime->getTimestamp(), 'custom', 'Y-m-d');

        $data[] = $value . ' => ' . $date;
      }
    }

    $element = [
      '#markup' => implode(',', $data),
    ];

    return $element;
  }

}
