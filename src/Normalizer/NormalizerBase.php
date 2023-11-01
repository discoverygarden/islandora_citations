<?php

namespace Drupal\islandora_citations\Normalizer;

use Drupal\serialization\Normalizer\NormalizerBase as SerializationNormalizerBase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Base class for Normalizers.
 */
abstract class NormalizerBase extends SerializationNormalizerBase implements NormalizerInterface {

  /**
   * Supported format for normalizer.
   */
  const FORMAT = 'csl-json';

  /**
   * {@inheritdoc}
   */
  public function supportsNormalization($data, ?string $format = NULL, array $context = []): bool {
    return $format === static::FORMAT && parent::supportsNormalization($data, $format);
  }

  /**
   * Gets the type of csl variable.
   */
  protected function getCslVariableType($variableName) {
    $schema = \Drupal::service('islandora_citations.helper')->loadCslJsonSchema();

    if (isset($schema['items']['properties'][$variableName]['type'])) {
      return is_array($schema['items']['properties'][$variableName]['type']) ? $schema['items']['properties'][$variableName]['type'][0] : $schema['items']['properties'][$variableName]['type'];
    }
    elseif (isset($schema['items']['properties'][$variableName]['$ref'])) {
      return 'object';
    }
    else {
      return NULL;
    }
  }

  /**
   * Formats date variables as per csl json.
   */
  protected function formatDateVariables($date): array {
    return ['date-parts' => [$date]];
  }

  /**
   * Formats name variables as per csl json.
   */
  protected function formatNameVariables($name, $type): array {
    switch ($type) {
      case 'person':
        if (strpos($name, ',') !== FALSE) {
          $names = explode(',', $name);
          return is_array($names) ?
            ['family' => $names[1], 'given' => $names[0]] :
            ['family' => $name];
        }
        else {
          $names = explode(' ', $name);
          return is_array($names) ?
            ['given' => $names[1], 'family' => $names[0]] :
            ['family' => $name];
        }

      case 'institution':
        return ['family' => $name];
    }

    return $name;
  }

}
