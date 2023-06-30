<?php

namespace Drupal\islandora_citations\Normalizer;

use Drupal\Component\Render\MarkupInterface;
use Drupal\text\Plugin\Field\FieldType\TextItemBase;

/**
 * Converts TextItem fields to an array including computed values.
 */
class TextItemNormalizer extends NormalizerBase {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = TextItemBase::class;

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = []) {
    $attributes = [];
    $field_value = $object->getValue();

    if (isset($field_value['value'])) {
      $processed_value = check_markup($field_value['value'], 'citation_html', $langcode = '', $filter_types_to_skip = []);
      if ($processed_value instanceof MarkupInterface) {
        $field_values = $processed_value->__toString();
        foreach ($context['csl-map'] as $cslField) {
          $attributes[$cslField] = $field_values;
        }
      }
    }
    return $attributes;
  }

}
