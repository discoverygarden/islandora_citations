<?php

namespace Drupal\islandora_citations\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'CSL Field' formatter.
 *
 * @FieldFormatter(
 *   id = "islandora_citations_csl_field",
 *   label = @Translation("CSL Field"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class CslFieldFormatter extends FormatterBase {

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

        $element[$value] = $item->value;
      }
    }
    return $element;
  }

}
