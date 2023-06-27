<?php

namespace Drupal\islandora_citations\Normalizer;

/**
 * Converts TextItem fields to an array including computed values.
 */
class TextItemNormalizer extends NormalizerBase {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = 'Drupal\text\Plugin\Field\FieldType\TextItemBase';

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = []) {
    $attributes = [];
    foreach ($object->getProperties(TRUE) as $field) {
      $value = $this->serializer->normalize($field, $format, $context);
      if (is_object($value)) {
        $value = $this->serializer->normalize($value, $format, $context);
      }

      $field_value = check_markup($value, 'citation_html', $langcode = '', $filter_types_to_skip = []);
      if ($field->getName() == 'value') {
        $field_values = $field_value->__toString();
        foreach ($context['csl-map'] as $cslField) {
          $attributes[$cslField] = $field_values;
        }
      }
    }
    return $attributes;
  }

}
