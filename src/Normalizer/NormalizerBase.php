<?php

namespace Drupal\islandora_citations\Normalizer;

use Drupal\serialization\Normalizer\NormalizerBase as SerializationNormalizerBase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Base class for Normalizers.
 */
abstract class NormalizerBase extends SerializationNormalizerBase implements NormalizerInterface {

  /**
   * {@inheritdoc}
   */
  const FORMAT = 'csl-json';

  /**
   * {@inheritdoc}
   */
  public function supportsNormalization($data, $format = NULL) {
    return $format === static::FORMAT && parent::supportsNormalization($data, $format);
  }

}
