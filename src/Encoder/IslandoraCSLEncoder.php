<?php

namespace Drupal\islandora_citations\Encoder;

use Drupal\serialization\Encoder\JsonEncoder as SerializationJsonEncoder;

/**
 * Encodes to CSL-json.
 *
 * Respond to csl-json format requests using the JSON encoder.
 */
class IslandoraCSLEncoder extends SerializationJsonEncoder {

  /**
   * The formats that this Encoder supports.
   *
   * @var string
   */
  protected static $format = ['csl-json'];

}
