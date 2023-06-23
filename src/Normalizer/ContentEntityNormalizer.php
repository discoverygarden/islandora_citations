<?php

namespace Drupal\islandora_citations\Normalizer;

use Drupal\Core\Config\Entity\ThirdPartySettingsInterface;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Normalizes/denormalizes Drupal content entities into an array structure.
 */
class ContentEntityNormalizer extends NormalizerBase {

  /**
   * {@inheritdoc}
   */
  protected $supportedInterfaceOrClass = ContentEntityInterface::class;

  /**
   * {@inheritdoc}
   */
  public function normalize($entity, $format = NULL, array $context = []) {
    foreach ($entity->getFields(TRUE) as $field_item_list) {
      $definition = $field_item_list->getFieldDefinition();

      // Do not process if there are no third party settings.
      if (!($definition instanceof ThirdPartySettingsInterface)) {
        continue;
      }

      $thirdPartySetting = $definition->getThirdPartySetting('islandora_citations', 'csl_field');

      // Do not process if there are field is not mapped.
      if (empty($thirdPartySetting)) {
        continue;
      }

      $field_item_values = [];
      foreach ($field_item_list as $field_item) {
        /** @var \Drupal\Core\Field\FieldItemInterface $field_item */
        $field_item_values[] = $this->serializer->normalize($field_item, $format, $context);
      }

      $cardinality = $definition->getFieldStorageDefinition()->getCardinality();

      if ($cardinality == 1) {
        // Single valued field.
      }
      else {
        // Multi-value field.
      }

      return $field_item_values;

    }

  }

}
