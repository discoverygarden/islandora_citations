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
    if (empty($items)) {
      return [];
    }

    foreach ($items as $item) {

      $rel_types = $item->getRelTypes();
      $rel_type = $rel_types[$item->rel_type] ?? $item->rel_type;

      $element[$rel_type][] = $item->entity->getName();
    }
    return $element;
  }

}
