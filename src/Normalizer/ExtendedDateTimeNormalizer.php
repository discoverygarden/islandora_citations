<?php

namespace Drupal\islandora_citations\Normalizer;

use Drupal\controlled_access_terms\Plugin\Field\FieldType\ExtendedDateTimeFormat;
use EDTF\EdtfFactory;
use EDTF\Model\ExtDate;

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
        // XXX: Only support singular EDTF dates at this time, exit out if it's
        // an interval or set.
        if ($edtf instanceof ExtDate) {
          return $this->formatDateVariables(explode('-', $edtf->iso8601()));
        }
      }  
    }
    return [];
  }

}
