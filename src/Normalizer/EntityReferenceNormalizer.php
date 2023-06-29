<?php

namespace Drupal\islandora_citations\Normalizer;

use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\serialization\Normalizer\EntityReferenceFieldItemNormalizer;
use Drupal\taxonomy\Entity\Term;

/**
 * Expands entity reference field values to their referenced entity.
 */
class EntityReferenceNormalizer extends EntityReferenceFieldItemNormalizer {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = 'Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem';

  /**
   * {@inheritdoc}
   */
  public function normalize($field_item, $format = NULL, array $context = []) {
    assert($field_item instanceof EntityReferenceItem);
    $entity = $field_item->get('entity')->getValue();

    if ($context['use-entity']) {
      return $this->serializer->normalize($entity, 'csl-json');
    }
    else {
      foreach ($context['csl-map'] as $cslField) {
        $attributes[$cslField] = ($entity instanceof Term) ? $entity->getName() : $entity->getTitle();
      }
      return $attributes;
    }
  }

}
