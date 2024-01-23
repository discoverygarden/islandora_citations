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
  public function normalize($object, $format = NULL, array $context = []) {
    $normalized_field_items = [];
    foreach ($object->getFields(TRUE) as $field_item_list) {

      /** @var \Drupal\Core\Field\FieldDefinitionInterface $fieldDefinition */
      $fieldDefinition = $field_item_list->getFieldDefinition();

      // Do not process if there are no third party settings.
      if (!($fieldDefinition instanceof ThirdPartySettingsInterface)) {
        continue;
      }

      $mapFromSelectedCsl = $fieldDefinition->getThirdPartySetting('islandora_citations', 'csl_field');
      $mapFromEntity = $fieldDefinition->getThirdPartySetting('islandora_citations', 'use_entity_checkbox');

      // Do not process if field is not mapped.
      if (empty($mapFromSelectedCsl) && empty($mapFromEntity)) {
        continue;
      }

      // Add values to context, so that they can be passed to other normalizers.
      $context['csl-map'] = $mapFromSelectedCsl ?? NULL;
      $context['map-typed-rel'] = $mapFromEntity && $fieldDefinition->getType() === 'typed_relation';
      $context['use-entity'] = $mapFromEntity && $fieldDefinition->getType() !== 'typed_relation';

      // Defer normalization to field normalizers.
      $normalized_field_value = $this->serializer->normalize($field_item_list, $format, $context);

      if (empty($normalized_field_items)) {
        $normalized_field_items = $normalized_field_value;
      }
      else {
        foreach ($normalized_field_items as $cslKey => $cslValue) {
          if (isset($normalized_field_value[$cslKey])) {
            $normalized_field_items[$cslKey] = array_merge($cslValue, $normalized_field_value[$cslKey]);
          }
          else {
            $normalized_field_items += $normalized_field_value;
          }
        }
      }
    }

    // We do not need to do this for use entity
    // because it's already done when referenced entity is normalized.
    if (!isset($context['mapped-entity'])) {
      $this->normalizeCslFieldsForCiteProc($normalized_field_items);
    }

    return $normalized_field_items;
  }

  /**
   * Normalizes the array so that citeproc can accept it.
   */
  private function normalizeCslFieldsForCiteProc(&$normalized_field_items) {
    foreach ($normalized_field_items as $cslKey => $cslValueArray) {
      $fieldType = $this->getCslVariableType($cslKey);

      // If the variable type is string, comma separate the values.
      switch ($fieldType) {
        case 'string':
        case 'number':
          $values = [];
          if (is_array($cslValueArray)) {
            foreach ($cslValueArray as $key => $arrayValue) {
              $values[] = is_array($arrayValue) ? $arrayValue[array_key_first($arrayValue)] : $arrayValue;
            }
          }
          else {
            $values = $cslValueArray;
          }
          $normalized_field_items[$cslKey] = is_array($values) ? implode(', ', $values) : $values;
          break;

        case 'array':
          foreach ($cslValueArray as $key => $arrayValue) {
            $normalized_field_items[$cslKey][$key] = (object) $arrayValue;
          }
          break;

        case 'object':
          // Cannot have multi value fields here, so just taking the first item.
          $normalized_field_items[$cslKey] = (object) ($cslValueArray[0] ?? $cslValueArray);
          break;
      }
    }
  }

}
