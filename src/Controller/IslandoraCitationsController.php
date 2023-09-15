<?php

namespace Drupal\islandora_citations\Controller;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityFieldManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;

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
    $fields = $this->entityFieldManager->getFieldDefinitions('node', $node_type);

    foreach ($fields as $field_definition) {

      if (!empty($field_definition->getTargetBundle())) {
        $data = $field_definition->getThirdPartySetting('islandora_citations', 'csl_field');
        $dataForMappedEntities = $field_definition->getThirdPartySetting('islandora_citations', 'use_entity_checkbox');
        $rows[] = [$field_definition->getName(),
            $data ? implode(',', $data) : ($dataForMappedEntities ? 'Mapped from entity' : '-'),
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
      '#caption' => $this->t('For CSL fields that do not support multiple values like dates, only the first value of the field will be considered.'),
      '#header' => $header,
      '#rows' => $rows,
    ];
  }

  /**
   * Provide arguments for FieldConfigUpdate.
   *
   * @param string $paragraphs_type
   *   Node type.
   *
   * @return array
   *   Form array.
   */
  public function paragraphsArguments($paragraphs_type) {

    $header = [
      'col1' => $this->t('Field'),
      'col2' => $this->t('CSL Field'),
      'col3' => $this->t('Operation'),
    ];
    $fields = $this->entityFieldManager->getFieldDefinitions('paragraph', $paragraphs_type->id());

    foreach ($fields as $field_definition) {
      if (!empty($field_definition->getTargetBundle())) {
        $data = $field_definition->getThirdPartySetting('islandora_citations', 'csl_field');
        $rows[] = [$field_definition->getName(),
          $data ? implode(',', $data) : '-',
          [
            'data' => new FormattableMarkup('<a href=":link">@name</a>',
          [
            ':link' => 'fields/paragraph.' . $paragraphs_type->id() . '.' . $field_definition->getName(),
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
