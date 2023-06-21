<?php

namespace Drupal\islandora_citations\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;

/**
 * Plugin implementation of the 'CSL Organizations' formatter.
 *
 * @FieldFormatter(
 *   id = "islandora_citations_csl_organizations",
 *   label = @Translation("CSL Organizations"),
 *   field_types = {
 *     "typed_relation"
 *   }
 * )
 */
class CslOrganizationsFormatter extends EntityReferenceFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    $cslFields = $items->getFieldDefinition()->getThirdPartySetting('islandora_citations', 'csl_field');
    if (empty($cslFields) || empty($this->getEntitiesToView($items, $langcode))) {
      return [];
    }
    foreach ($cslFields as $value) {
      $label = [];
      foreach ($this->getEntitiesToView($items, $langcode) as $entity) {
        $label[] = $entity->getName();
      }
      $element[$value] = $label;
    }
    return $element;
  }

}
