<?php

namespace Drupal\islandora_citations\Normalizer;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\TypedData\Type\DateTimeInterface;

/**
 * Converts values for datetime objects to CSL indexed array.
 *
 * @internal
 */
class DateTimeNormalizer extends NormalizerBase {

  /**
   * Date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * {@inheritdoc}
   */
  protected $supportedInterfaceOrClass = DateTimeInterface::class;

  /**
   * The system's date configuration.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $systemDateConfig;

  /**
   * Constructs a new DateTimeNormalizer instance.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   A config factory for retrieving required config objects.
   * @param Drupal\Core\Datetime\DateFormatter $dateFormatter
   *   The date formatter.
   */
  public function __construct(ConfigFactoryInterface $config_factory, DateFormatter $dateFormatter) {
    $this->systemDateConfig = $config_factory->get('system.date');
    $this->dateFormatter = $dateFormatter;
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($datetime, $format = NULL, array $context = []) {
    assert($datetime instanceof DateTimeInterface);
    $element = [];
    $drupal_date_time = $datetime->getDateTime()->setTimezone($this->getNormalizationTimezone());
    if ($drupal_date_time === NULL) {
      return $drupal_date_time;
    }
    if (empty($context['csl-map'])) {
      return $drupal_date_time;
    }
    foreach ($context['csl-map'] as $cslField) {
      $date = $this->dateFormatter->format($drupal_date_time->getTimestamp(), 'custom', 'Y-m-d');
      $element[$cslField] = $date;
    }
    return $element;
  }

  /**
   * Gets the timezone to be used during normalization.
   *
   * @see ::normalize
   * @see \Drupal\Core\Datetime\DrupalDateTime::prepareTimezone()
   *
   * @returns \DateTimeZone
   *   The timezone to use.
   */
  protected function getNormalizationTimezone() {
    $default_site_timezone = $this->systemDateConfig->get('timezone.default');
    return new \DateTimeZone($default_site_timezone);
  }

}
