<?php

namespace Drupal\islandora_citations\Controller;

use Drupal\base_field_override_ui\BaseFieldOverrideUI;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Field\Entity\BaseFieldOverride;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for islandora_citations module routes.
 */
class IslandoraCitationsController extends ControllerBase {

  use StringTranslationTrait;

  /**
   * Drupal's entity field manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected EntityFieldManagerInterface $entityFieldManager;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return parent::create($container)
      ->setEntityFieldManager($container->get('entity_field.manager'));
  }

  /**
   * Setter for the entity field manager service.
   *
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   The entity field manager service to set.
   *
   * @return $this
   */
  public function setEntityFieldManager(EntityFieldManagerInterface $entityFieldManager) : static {
    $this->entityFieldManager = $entityFieldManager;
    return $this;
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

    $rows = [];
    foreach ($fields as $field_definition) {

      if (!empty($field_definition->getTargetBundle())) {
        $data = $field_definition->getThirdPartySetting('islandora_citations', 'csl_field');
        $dataForMappedEntities = $field_definition->getThirdPartySetting('islandora_citations', 'use_entity_checkbox');
        $rows[] = [
          $field_definition->getName(),
          $data ? implode(',', $data) : ($dataForMappedEntities ? 'Mapped from entity' : '-'),
          [
            'data' => $this->getLinkToField($node_type, $field_definition),
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
   * Helper; generate link to the field configuration page.
   *
   * @param string $bundle
   *   The bundle with which the field is associated.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition of which to link to the configuration page.
   *
   * @return \Drupal\Core\Link|\Drupal\Core\StringTranslation\TranslatableMarkup
   *   A link to a page to configure the given field, or a string.
   */
  protected function getLinkToField(string $bundle, FieldDefinitionInterface $field_definition) {
    /** @var \Drupal\Core\Field\Entity\BaseFieldOverride|\Drupal\field\Entity\FieldConfig $config */
    $config = $field_definition->getConfig($bundle);
    if ($config instanceof BaseFieldOverride) {
      if ($this->moduleHandler()->moduleExists('base_field_override_ui')) {
        return $config->isNew() ?
          new Link(
            $this->t('Add'),
            BaseFieldOverrideUI::getAddRouteInfo($config),
          ) :
          new Link(
            $this->t('Edit'),
            BaseFieldOverrideUI::getEditRouteInfo($config),
          );
      }
      else {
        return $this->t('Not applicable');
      }
    }
    else {
      return $this->t(
        '<a href=":link">@name</a>',
        [
          ':link' => "fields/{$config->id()}",
          '@name' => $this->t('Edit'),
        ],
      );
    }
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
    $fields = $this->entityFieldManager->getFieldDefinitions('paragraph', $paragraphs_type);

    $rows = [];
    foreach ($fields as $field_definition) {
      if (!empty($field_definition->getTargetBundle())) {
        $data = $field_definition->getThirdPartySetting('islandora_citations', 'csl_field');
        $rows[] = [$field_definition->getName(),
          $data ? implode(',', $data) : '-',
          [
            'data' => $this->getLinkToField($paragraphs_type, $field_definition),
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
