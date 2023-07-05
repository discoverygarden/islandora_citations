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
    if ($field_item->getFieldDefinition()->getType() == 'entity_reference_revisions') {
      // dump($this->serializer->normalize($entity, 'csl-json'));.
      return $this->serializer->normalize($entity, 'csl-json', $context);
    }
    else {
      if ($context['use-entity']) {
        return $this->serializer->normalize($entity, 'csl-json');
      }
      else {
        return $entity->label();
      }
    }
  }

}
