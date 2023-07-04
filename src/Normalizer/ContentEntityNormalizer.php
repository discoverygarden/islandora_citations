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
    $normalized_field_items = [];
    foreach ($entity->getFields(TRUE) as $field_item_list) {
      $definition = $field_item_list->getFieldDefinition();

      // Do not process if there are no third party settings.
      if (!($definition instanceof ThirdPartySettingsInterface)) {
        continue;
      }

      $thirdPartySetting = $definition->getThirdPartySetting('islandora_citations', 'csl_field');

      // Do not process if field is not mapped.
      if (empty($thirdPartySetting)) {
        continue;
      }

      $context['csl-map'] = $thirdPartySetting;

      // Defer the field normalization to other individual normalizers.
      $normalized_field_items += $this->serializer->normalize($field_item_list, $format, $context);

    }
    $normalized_field_items['type'] = 'book';
    return $normalized_field_items;
  }

}
