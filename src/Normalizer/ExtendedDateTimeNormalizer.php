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
          // Check for open-ended date range
          // Handle YYYY-MM-DD/.. format.
          if (str_contains($dateValue['value'], '/..') !== FALSE) {
            $end = $this->formatDateVariables(
              explode('-', date('Y-m-d'))
            );
            $start = $this->formatDateVariables(
              explode('-', $edtf->getStartDate()->iso8601())
            );
          }
          elseif (str_contains($dateValue['value'], '../') !== FALSE) {
            // Handle ../YYYY-MM-DD format.
            $start = $this->formatDateVariables(
              explode('-', date('Y-m-d'))
            );
            $end = $this->formatDateVariables(
              explode('-', $edtf->getEndDate()->iso8601())
            );
          }
          elseif (str_ends_with($dateValue['value'], '/') || str_starts_with($dateValue['value'], '/')) {
            // Handle /YYYY-MM-DD or YYYY-MM-DD/ format.
            // As the format is not supported, we will not map.
            return [];
          }
          else {
            $start = $this->formatDateVariables(
              explode('-', $edtf->getStartDate()->iso8601())
            );
            $end = $this->formatDateVariables(
              explode('-', $edtf->getEndDate()->iso8601())
            );
          }

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
