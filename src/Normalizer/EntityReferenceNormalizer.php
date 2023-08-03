<?php

namespace Drupal\islandora_citations\Normalizer;

use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;

/**
 * Expands entity reference field values to their referenced entity.
 */
class EntityReferenceNormalizer extends NormalizerBase {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = EntityReferenceItem::class;

  /**
   * {@inheritdoc}
   */
  public function normalize($field_item, $format = NULL, array $context = []) {
    assert($field_item instanceof EntityReferenceItem);
    $entity = $field_item->get('entity')->getValue();
    $entity_context['mapped-entity'] = TRUE;
    if ($field_item->getFieldDefinition()->getType() == 'entity_reference_revisions') {
      return $this->serializer->normalize($entity, 'csl-json', $entity_context);
    }
    else {
      if ($context['use-entity']) {
        return $this->serializer->normalize($entity, 'csl-json', $entity_context);
      }
      else {
        return $entity->label();
      }
    }
  }

}
