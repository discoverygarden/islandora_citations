<?php

namespace Drupal\islandora_citations\Normalizer;

use Drupal\taxonomy\TermInterface;

/**
 * Converts Typed relation fields to an array including computed values.
 */
class TypedRelationNormalizer extends NormalizerBase {
  const PERSON_VOCAB = 'person';

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

    $parent = $object->getParent()->entity;
    if ($parent instanceof TermInterface) {
      $rel_types = $object->getRelTypes();
      $rel_type = $this->formatRelTypes($rel_types[$object->rel_type]);
      $label = $parent->getName();
      unset($context['csl-map']);
      $context['csl-map'][$rel_type] = $rel_type;
      if ($parent->bundle() === self::PERSON_VOCAB) {
        return $this->formatNameVariables($label, 'person');
      }
      else {
        return $this->formatNameVariables($label, 'institution');
      }

    }

    return $attributes;
  }

  /**
   * Creates rel type mappings.
   *
   * Works for now but should be moved to third party settings.
   */
  private function formatRelTypes($rel_type) {
    $rel_type = strtolower(trim(preg_replace("/\([^)]+\)/", "", $rel_type)));

    if ($rel_type == 'creator') {
      return 'author';
    }

    return $rel_type;
  }

}
