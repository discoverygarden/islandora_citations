<?php

namespace Drupal\islandora_citations\Normalizer;

/**
 * Converts Typed relation fields to an array including computed values.
 */
class TypedRelationNormalizer extends NormalizerBase {


  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = 'Drupal\controlled_access_terms\Plugin\Field\FieldType\TypedRelation';

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = []) {

    $attributes = [];
    foreach ($object->getProperties(TRUE) as $name => $field) {

      if ($name == 'rel_type') {
        if ($field->getParent()) {
          $rel_types = $field->getParent()->getRelTypes();
          $rel_type = $rel_types[$field->getParent()->rel_type];
          $attributes[$rel_type][] = $field->getParent()->entity->getName();
        }
      }
    }
    return $attributes;
  }

}
