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
      $context['csl-map'] = '';
      $context['use-entity'] = '';
      $context['rel-csl-map'] = '';
      $context['er-reference'] = '';
      $definition = $field_item_list->getFieldDefinition();

      // Do not process if there are no third party settings.
      if (!($definition instanceof ThirdPartySettingsInterface)) {
        continue;
      }

      $thirdPartySetting = $definition->getThirdPartySetting('islandora_citations', 'csl_field');
      $eRthirdpartySettings = $definition->getThirdPartySetting('islandora_citations', 'use_entity_checkbox');

      // Do not process if field is not mapped.
      if (empty($thirdPartySetting) && empty($eRthirdpartySettings)) {
        continue;
      }

      $context['csl-map'] = $thirdPartySetting;
      $context['use-entity'] = $eRthirdpartySettings ? $eRthirdpartySettings : NULL;
      if ($context['use-entity'] && ($definition->getType() === 'entity_reference_revisions' || $definition->getType() == 'entity_reference')) {
        $context['er-reference'] = TRUE;
        $data = $this->serializer->normalize($field_item_list, $format, $context);
        if (count($data > 1)) {
          for ($i = 1; $i < count($data); $i++) {
            $keys = array_keys($data);
            foreach ($data[$keys[$i]] as $key => $value) {
              $data[0][$key] = $data[0][$key] . "," . $value;
              unset($data[$i]);
            }
          }
        }
        $normalized_field_items += $data;
      }
      elseif ($context['use-entity'] && $definition->getType() === 'typed_relation') {
        $context['rel-csl-map'] = TRUE;
        $data = $this->serializer->normalize($field_item_list, $format, $context);
        $key = array_key_first($data);
        if (array_key_exists($key, $normalized_field_items)) {
          foreach ($normalized_field_items[$key] as $index => $value) {
            $normalized_field_items[$key][$index]['family'] = $normalized_field_items[$key][$index]['family'] . ',' . $data[$key][$index]['family'];
          }
        }
        else {
          $normalized_field_items += $data;
        }
      }
      else {
        $normalized_field_items += $this->serializer->normalize($field_item_list, $format, $context);
      }
      // Defer the field normalization to other individual normalizers.
    }
    return $normalized_field_items;
  }

}
