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
        if (strpos($name, ',') !== FALSE) {
          $names = explode(',', $name);
          // Get the first name and last name.
          $name_details = is_array($names) ? $this->getFirstNameAndLastName($names) : [];

          return !empty($name_details) ?
            ['family' => $name_details['last_name'], 'given' => $name_details['first_name']] :
            ['family' => $name];
        }
        else {
          $names = explode(' ', $name);
          // Get the first name and last name.
          $name_details = is_array($names) ? $this->getFirstNameAndLastName($names) : [];

          return !empty($name_details) ?
            ['given' => $name_details['last_name'], 'family' => $name_details['first_name']] :
            ['family' => $name];
        }

      case 'institution':
        return ['family' => $name];
    }

    return $name;
  }

  /**
   * Get the first name and last name from name array.
   *
   * @param array $names
   *   An array of name indexes.
   */
  protected function getFirstNameAndLastName(array $names) {
    if (empty($names)) {
      return [];
    }

    $name_index_count = count($names);
    // Last index in the names array would be considered as the last name.
    $last_name = $name_index_count > 1 ? $names[$name_index_count - 1] : '';

    // Rest all make up the first name.
    $first_name = [];
    if ($name_index_count > 1) {
      for ($i = 0; $i < $name_index_count - 1; $i++) {
        $first_name[] = $names[$i];
      }
    }
    else {
      $first_name = $names[0];
    }

    return [
      'first_name' => is_array($first_name) ? implode(' ', $first_name) : $first_name,
      'last_name' => $last_name,
    ];
  }

}
