<?php

namespace Drupal\islandora_citations\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'CSL Persons' formatter.
 *
 * @FieldFormatter(
 *   id = "islandora_citations_csl_persons",
 *   label = @Translation("CSL Persons"),
 *   field_types = {
 *     "typed_relation"
 *   }
 * )
 */
class CslPersonsFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    if (empty($items)) {
      return [];
    }

    foreach ($items as $item) {
      $rel_types = $item->getRelTypes();
      $rel_type = $rel_types[$item->rel_type] ?? $item->rel_type;
      $name = $this->getNames($item->entity->getName());
      $element[$rel_type][] = $name;
    }
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function getNames($name) {
    if (strpos($name, ',') !== FALSE) {
      $names = explode(',', $name);
      return is_array($names) ? $names[1] . ' || ' . $names[0] : $name;
    }
    else {
      $names = explode(' ', $name);
      return is_array($names) ? $names[0] . ' || ' . $names[1] : $name;
    }
  }

}
