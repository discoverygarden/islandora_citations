<?php

namespace Drupal\islandora_citations\Normalizer;

use Drupal\controlled_access_terms\Plugin\Field\FieldType\ExtendedDateTimeFormat;
use EDTF\EdtfFactory;
use EDTF\Model\ExtDate;
use EDTF\Model\Interval;

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
      $parser = EdtfFactory::newParser();
      $parsed = $parser->parse($dateValue['value']);

      if ($parsed->isValid()) {
        $edtf = $parsed->getEdtfValue();

        // Check if it's an interval.
        if ($edtf instanceof Interval) {
          $start = $this->formatDateVariables(explode('-', $edtf->getStartDate()->iso8601()));
          $end = $this->formatDateVariables(explode('-', $edtf->getEndDate()->iso8601()));

          $date_parts['date-parts'] = [
            'start' => $start['date-parts'][0],
            'end' => $end['date-parts'][0],
          ];

          return $date_parts;

        }
        elseif ($edtf instanceof ExtDate) {
          // Handle singular dates.
          return $this->formatDateVariables(explode('-', $edtf->iso8601()));
        }
      }
    }

    return [];
  }

}
