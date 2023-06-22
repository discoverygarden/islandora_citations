<?php

namespace Drupal\islandora_citations\Normalizer;

use Drupal\serialization\Normalizer\NormalizerBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\TypedData\TypedDataInternalPropertiesHelper;

/**
 * Normalizes/denormalizes Drupal content entities into an array structure.
 */
class ContentEntityNormalizer extends NormalizerBase {

  /**
   * The formats that the Normalizer can handle.
   *
   * @var array
   */
  protected $format = ['csl-json'];

  /**
   * {@inheritdoc}
   */
  protected $supportedInterfaceOrClass = ContentEntityInterface::class;

  /**
   * {@inheritdoc}
   */
  public function normalize($entity, $format = NULL, array $context = []) {
    $context += [
      'account' => NULL,
    ];

    $object = TypedDataInternalPropertiesHelper::getNonInternalProperties($entity->getTypedData());

    // Called as the entity is referenced in a field.
    // The field configuration is for only one property.
    // eg. `term:uuid`.
    if (!empty($context['field'])) {
      $field_context = explode(':', $context['field']['field_name'], 2);
    }
    if (!empty($field_context[1])) {
      $field_items = $object[$field_context[1]];
      if ($field_items->access('view', $context['account'])) {
        $context['field']['field_name'] = $field_context[1];
        $attributes = $this->serializer->normalize($field_items, $format, $context);
      }
    }
    return $attributes;
  }

}
