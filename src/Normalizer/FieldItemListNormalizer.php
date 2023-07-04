<?php

namespace Drupal\islandora_citations\Normalizer;

use Drupal\Core\Field\FieldItemListInterface;

/**
 * Normalizes field item list into an array structure.
 */
class FieldItemListNormalizer extends NormalizerBase {

  /**
   * {@inheritdoc}
   */
  protected $supportedInterfaceOrClass = FieldItemListInterface::class;

  /**
   * {@inheritdoc}
   */
  public function normalize($field_item_list, $format = NULL, array $context = []) {
    /** @var \Drupal\Core\Field\FieldItemListInterface $field_item_list */
    // If the field can hold multiple values we want them as a list. If not,
    // as a plain value. For that we need to check how the field is defined to
    // see the cardinality value.
    $cardinality = $field_item_list
      ->getFieldDefinition()
      ->getFieldStorageDefinition()
      ->getCardinality();
    $context['cardinality'] = $cardinality;

    $field_item_values = [];

    foreach ($field_item_list as $field_item) {
      foreach ($context['csl-map'] as $cslField) {
        /** @var \Drupal\Core\Field\FieldItemInterface $field_item */
        $field_item_values[$cslField][] = $this->serializer->normalize($field_item, $format, $context);
        ;
      }
    }

    $this->normalizeCslMultiValueFields($field_item_values);

    return $field_item_values;
  }

  /**
   * {@inheritdoc}
   */
  private function normalizeCslMultiValueFields(&$field_item_values) {
    foreach ($field_item_values as $cslKey => $cslValueArray) {
      $fieldType = $this->getCslVariableType($cslKey);

      // If the variable type if string, comma separate the values.
      switch ($fieldType) {
        case 'string':
        case 'number':
          $field_item_values[$cslKey] = implode(', ', $cslValueArray);
          break;

        case 'array':
          foreach ($cslValueArray as $key => $arrayValue) {
            $field_item_values[$cslKey][$key] = (object) $arrayValue;
          }
          break;

        case 'object':
          // Cannot have multi value fields here, so just taking the first item.
          $field_item_values[$cslKey] = (object) ($cslValueArray[0] ?? $cslValueArray);
          break;
      }
    }
  }

}
