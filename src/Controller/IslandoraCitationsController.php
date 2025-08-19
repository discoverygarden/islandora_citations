<?php

namespace Drupal\islandora_citations\Controller;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Field\Entity\BaseFieldOverride;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\base_field_override_ui\BaseFieldOverrideUI;
use Drupal\node\Entity\NodeType;
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
   * @param \Drupal\node\Entity\NodeType $node_type
   *   Node type.
   *
   * @return array
   *   Form array.
   */
  public function provideArguments(NodeType $node_type) {

    $header = [
      'col1' => $this->t('Field'),
      'col2' => $this->t('CSL Field'),
      'col3' => $this->t('Operation'),
    ];
    $fields = $this->entityFieldManager->getFieldDefinitions('node', $node_type->id());

    $rows = [];
    foreach ($fields as $field_definition) {

      if (!empty($field_definition->getTargetBundle())) {
        $data = $field_definition->getThirdPartySetting('islandora_citations', 'csl_field');
        $dataForMappedEntities = $field_definition->getThirdPartySetting('islandora_citations', 'use_entity_checkbox');
        $rows[] = [
          $field_definition->getName(),
          $data ? implode(',', $data) : ($dataForMappedEntities ? 'Mapped from entity' : '-'),
          [
            'data' => $this->getLinkToField('node', $node_type, $field_definition),
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
   * @param string $type
   *   The type of entity to which the field is associated.
   * @param \Drupal\Core\Config\Entity\ConfigEntityBundleBase $bundle
   *   The bundle with which the field is associated.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition of which to link to the configuration page.
   *
   * @return \Drupal\Core\Link|\Drupal\Core\StringTranslation\TranslatableMarkup
   *   A link to a page to configure the given field, or a string.
   */
  protected function getLinkToField(string $type, ConfigEntityBundleBase $bundle, FieldDefinitionInterface $field_definition) {
    $add_destination = static function (Url $url) {
      $query = $url->getOption('query');
      $query['destination'] = Url::fromRoute('<current>')->toString();
      $url->setOption('query', $query);
      return $url;
    };
    /** @var \Drupal\Core\Field\Entity\BaseFieldOverride|\Drupal\field\Entity\FieldConfig $config */
    $config = $field_definition->getConfig($bundle->id());
    if ($config instanceof BaseFieldOverride) {
      if ($this->moduleHandler()->moduleExists('base_field_override_ui')) {
        return $config->isNew() ?
          new Link(
            $this->t('Add'),
            $add_destination(BaseFieldOverrideUI::getAddRouteInfo($config)),
          ) :
          new Link(
            $this->t('Edit'),
            $add_destination(BaseFieldOverrideUI::getEditRouteInfo($config)),
          );
      }

      return $this->t('Not applicable');
    }
    else {
      return new Link(
        $this->t('Edit'),
        $add_destination(Url::fromRoute("entity.field_config.{$type}_field_edit_form", [
          $bundle->getEntityTypeId() => $bundle->id(),
          'field_config' => $config->id(),
        ])),
      );
    }
  }

  /**
   * Provide arguments for FieldConfigUpdate.
   *
   * @param \Drupal\paragraphs\Entity\ParagraphsType $paragraphs_type
   *   Paragraph type.
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

    $rows = [];
    foreach ($fields as $field_definition) {
      if (!empty($field_definition->getTargetBundle())) {
        $data = $field_definition->getThirdPartySetting('islandora_citations', 'csl_field');
        $rows[] = [$field_definition->getName(),
          $data ? implode(',', $data) : '-',
          [
            'data' => $this->getLinkToField('paragraph', $paragraphs_type, $field_definition),
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
