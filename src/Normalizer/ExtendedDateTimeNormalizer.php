<?php

namespace Drupal\islandora_citations\Normalizer;

use Drupal\controlled_access_terms\Plugin\Field\FieldType\ExtendedDateTimeFormat;

/**
 * Converts EDTF fields to an array including computed values.
 */
class ExtendedDateTimeNormalizer extends NormalizerBase {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = ExtendedDateTimeFormat::class;

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = []) {
    $dateValue = $object->getValue();
    if (!empty($dateValue['value'])) {
      // Check if date is a range. We only support date at this point.
      if(!preg_match('/[^a-zA-Z0-9-]+/', $dateValue['value'], $matches)) {
        // Only contains dashes
        $dateParts = explode('-', $dateValue['value']);
      }
//      else {
//        // Contains special character
//        // @todo support other formats such as /date, date/, date ~ etc.
//      }

    }

    return $this->formatDateVariables($dateParts ?? $dateValue);
  }

}
