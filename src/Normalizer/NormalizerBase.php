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
    return $format === static::FORMAT && parent::supportsNormalization($data, $format, $context);
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
        $name_values = strpos($name, ',') !== FALSE ?
          $this->getFirstNameAndLastName($name, ',') :
          $this->getFirstNameAndLastName($name);

        return !empty($name_values) ? $name_values : ['family' => $name];

      case 'institution':
        return ['family' => $name];
    }

    return $name;
  }

  /**
   * Get the first name and last name from name.
   *
   * @param string $name
   *   The Person name.
   * @param string $separator
   *   The separator used to explode name.
   */
  protected function getFirstNameAndLastName($name, $separator = ' ') {
    $names = explode($separator, $name);
    $name_index_count = count($names);

    // If the separator is a comma, the last name is the first index.
    if ($separator === ',') {
      return [
        'family' => trim($names[1]),
        'given' => trim($names[0]),
      ];
    }
    else {
      $first_name = [];
      $last_name = '';

      if ($name_index_count > 1) {
        // The last index in the names array would be considered as the last
        // name.
        $last_name = $names[$name_index_count - 1];

        // The rest all make up the first name.
        for ($i = 0; $i < $name_index_count - 1; $i++) {
          $first_name[] = $names[$i];
        }
      }
      else {
        // Handling the case where only a single word name is provided.
        $first_name = $names['0'];
      }

      $first_name = is_array($first_name) ? implode(' ', $first_name) : $first_name;
      return [
        'given' => trim($last_name),
        'family' => trim($first_name),
      ];
    }
  }

}
