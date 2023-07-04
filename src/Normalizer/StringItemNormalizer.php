<?php

namespace Drupal\islandora_citations\Normalizer;

/**
 * Converts StringItem fields to an array including computed values.
 */
class StringItemNormalizer extends NormalizerBase {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = 'Drupal\Core\Field\Plugin\Field\FieldType\StringItemBase';

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = []) {
    $value = NULL;
    foreach ($object->getProperties(TRUE) as $field) {
      $value = $this->serializer->normalize($field, $format, $context);
      if (is_object($value)) {
        $value = $this->serializer->normalize($value, $format, $context);
      }
    }
    return $value;
  }

}
