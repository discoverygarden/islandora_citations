<?php

namespace Drupal\islandora_citations\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Component\Render\FormattableMarkup;

/**
 * Returns responses for islandora_citations module routes.
 */
class IslandoraCitationsController extends ControllerBase {

  /**
   * Provide arguments for FieldConfigUpdate.
   *
   * @param string $node_type
   *   Node type.
   *
   * @return array
   *   Form array.
   */
  public function provideArguments($node_type) {

    $header = [
      'col1' => t('Field'),
      'col2' => t('CSL Field'),
      'col3' => t('Operation'),
    ];
    $entityFieldManager = \Drupal::service('entity_field.manager');
    $fields = $entityFieldManager->getFieldDefinitions('node', $node_type);

    foreach ($fields as $field_name => $field_definition) {
      if (!empty($field_definition->getTargetBundle())) {
        $rows[] = [$field_definition->getName(),
          $field_definition->getThirdPartySetting('islandora_citations', 'csl_id') ? $field_definition->getThirdPartySetting('islandora_citations', 'csl_id') : "-",
          [
            'data' => new FormattableMarkup('<a href=":link">@name</a>',
          [
            ':link' => 'fields/node.' . $node_type . '.' . $field_definition->getName(),
            '@name' => t('Edit'),
          ]),
          ],
        ];
      }
    }
    return [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    ];
  }

}
