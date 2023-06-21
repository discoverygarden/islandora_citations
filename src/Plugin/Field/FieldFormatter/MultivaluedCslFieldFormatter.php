<?php

namespace Drupal\islandora_citations\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'Multivalued CSL Field' formatter.
 *
 * @FieldFormatter(
 *   id = "islandora_citations_multivalued_csl_field",
 *   label = @Translation("Multivalued CSL Field"),
 *   field_types = {
 *     "string",
 *     "datetime",
 *   }
 * )
 */
class MultivaluedCslFieldFormatter extends FormatterBase {

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
      $data = [];
      foreach ($items as $item) {
        $data[] = $item->value;
      }
      $element[$value] = implode(',', $data);
    }
    return $element;
  }

}
