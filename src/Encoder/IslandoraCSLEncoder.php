<?php

namespace Drupal\islandora_citations\Encoder;

use Symfony\Component\Serializer\Encoder\JsonEncoder as SymfonyJsonEncoder;

/**
 * Encodes to CSL-json.
 *
 * Respond to csl-json format requests using the JSON encoder.
 */
class IslandoraCSLEncoder extends SymfonyJsonEncoder {

  /**
   * The formats that this Encoder supports.
   *
   * @var string
   * @see src/IslandoraCitationsServiceProvider.php
   */
  protected $format = 'csl-json';

  /**
   * {@inheritdoc}
   */
  public function supportsEncoding($format) {

    return $format == $this->format;
  }

  /**
   * {@inheritdoc}
   */
  public function supportsDecoding($format) {

    return $format == $this->format;
  }

}
