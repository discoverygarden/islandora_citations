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
    foreach ($cslFields as $key => $value) {
      foreach ($items as $delta => $item) {

        $data[] = $value . ' => ' . $item->value;
      }
    }

    $element = [
      '#markup' => implode(',', $data),
    ];

    return $element;
  }

}
