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

    $bundle = $object->getParent()->entity->bundle();
    if ($bundle == 'corporate_body') {
      $rel_types = $object->getRelTypes();
      $rel_type = $rel_types[$object->rel_type];
      $attributes[$rel_type][] = $this->getNames($object->getParent()->entity->getName());
    }
    elseif ($bundle == 'person') {
      $rel_types = $object->getRelTypes();
      $rel_type = $rel_types[$object->rel_type];
      $attributes[$rel_type][] = $object->getParent()->entity->getName();
    }
    else {
      $rel_types = $object->getRelTypes();
      $rel_type = $rel_types[$object->rel_type];
      $attributes[$rel_type][] = $this->getNames($object->getParent()->entity->getName());
    }
    return $attributes;
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
