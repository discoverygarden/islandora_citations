<?php

namespace Drupal\islandora_citations\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Entity\EntityFieldManager;

/**
 * Returns responses for islandora_citations module routes.
 */
class IslandoraCitationsController extends ControllerBase {

  use StringTranslationTrait;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManager
   */

  protected $entityFieldManager;

  /**
   * Construct.
   *
   * @param \Drupal\Core\Entity\EntityFieldManager $entityFieldManager
   *   The entity type manager service.
   */
  public function __construct(EntityFieldManager $entityFieldManager) {
    $this->entityFieldManager = $entityFieldManager;
  }

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
      'col1' => $this->t('Field'),
      'col2' => $this->t('CSL Field'),
      'col3' => $this->t('Operation'),
    ];
    $entityFieldManager = $this->entityFieldManager;
    $fields = $entityFieldManager->getFieldDefinitions('node', $node_type);

    foreach ($fields as $field_definition) {
      if (!empty($field_definition->getTargetBundle())) {
        $rows[] = [$field_definition->getName(),
          $field_definition->getThirdPartySetting('islandora_citations', 'csl_field') ? $field_definition->getThirdPartySetting('islandora_citations', 'csl_field') : "-",
          [
            'data' => new FormattableMarkup('<a href=":link">@name</a>',
          [
            ':link' => 'fields/node.' . $node_type . '.' . $field_definition->getName(),
            '@name' => $this->t('Edit'),
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
