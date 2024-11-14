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
   *
   * @throws \Exception
   */
  protected function formatNameVariables($name, $type): array {
    return match ($type) {
      'person' => $this->getNameParts($name),
      'institution' => ['family' => $name],
      default => ['family' => $name],
    };

  }

  /**
   * Gets the first name and last name from name.
   *
   * @param string $name
   *   The person's name.
   *
   * @return array
   *   An array of name parts.
   *
   * @throws \Exception
   */
  protected function getNameParts(string $name): array {
    try {
      // If name has a comma, we assume that it's
      // formatted as Lastname,Firstname
      // This is kind of dicey, names might just contain commas.
      // @todo but that's an edge case which can be handled when
      // encountered.
      if (str_contains($name, ',')) {
        $nameParts = explode(',', $name);
        $firstName = $nameParts[1] ?? '';
        $lastName = $nameParts[0] ?? '';
      }
      else {
        $name = trim($name);
        $lastName = (!str_contains($name, ' ')) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
        $firstName = trim(preg_replace('#' . preg_quote($lastName, '#') . '#', '', $name));
      }

      if (empty($firstName) || empty($lastName)) {
        throw new \Exception('Name is not formatted properly');
      }

      return [
        'given' => trim($firstName),
        'family' => trim($lastName),
      ];
    }
    catch (\Exception $e) {
      return ['family' => $name];
    }

  }

}
