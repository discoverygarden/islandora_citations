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
    $field_item_values = [];

    /** @var \Drupal\Core\Field\FieldItemListInterface $field_item_list */
    foreach ($field_item_list as $field_item) {
      // If value is empty, do not process.
      if ($field_item->isEmpty()) {
        continue;
      }

      $context['normalized-field-list'] = $field_item_values;

      // If there are multiple csl fields mapped, get array values for each one.
      if ($context['csl-map']) {
        foreach ($context['csl-map'] as $cslField) {
          /** @var \Drupal\Core\Field\FieldItemInterface $field_item */
          $field_item_values[$cslField][] = $this->serializer->normalize($field_item, $format, $context);
        }
      }
      else {
        $field_item_values = $this->serializer->normalize($field_item, $format, $context);
      }
    }

    return $field_item_values;
  }

}
