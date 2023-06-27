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

    // We also want to check if the field has only one property (typically
    // called 'value'). If that is the case, we will just return the content,
    // and not bother with the property name.
    $field_item_values = [];
    $context['cardinality'] = $cardinality;
    foreach ($field_item_list as $field_item) {
      /** @var \Drupal\Core\Field\FieldItemInterface $field_item */

      $field_item_values[] = $this->serializer->normalize($field_item, $format, $context);
    }
    // If this is a single field, just consider the first item.
    if ($cardinality == 1) {
      $normalized_field['content'] = ['value' => reset($field_item_values)];
    }
    else {
      foreach ($context['csl-map'] as $cslField) {
        $content[$cslField] = implode(',', $field_item_values);
      }
      $normalized_field['content'] = ['value' => $content];
      // Comma separated values.
      // LinkedRelation - array values.
      // Paragraph - comma separated same field values.
    }
    return $normalized_field;
  }

}
