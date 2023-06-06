<?php

namespace Drupal\islandora_citations\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\islandora_citations\IslandoraCitationInterface;

/**
 * Defines the islandora_citations entity type.
 *
 * @ConfigEntityType(
 *   id = "islandora_citations",
 *   label = @Translation("Islandora Citation"),
 *   label_collection = @Translation("Islandora Citation"),
 *   label_singular = @Translation("Islandora Citation"),
 *   label_plural = @Translation("Islandora Citation"),
 *   label_count = @PluralTranslation(
 *     singular = "@count islandora_citations",
 *     plural = "@count islandora_citations",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\islandora_citations\IslandoraCitationListBuilder",
 *     "form" = {
 *       "add" = "Drupal\islandora_citations\Form\IslandoraCitationForm",
 *       "add-file" = "Drupal\islandora_citations\Form\IslandoraCitationFileForm",
 *       "edit" = "Drupal\islandora_citations\Form\IslandoraCitationForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     }
 *   },
 *   config_prefix = "islandora_citations",
 *   admin_permission = "administer islandora_citations",
 *   links = {
 *     "collection" = "/admin/structure/islandora-citationn",
 *     "add-form" = "/admin/structure/islandora-citation/add",
 *     "add-form-file" = "/admin/structure/islandora-citation/add-file",
 *     "edit-form" = "/admin/structure/islandora-citation/{islandora_citations}",
 *     "delete-form" = "/admin/structure/islandora-citation/{islandora_citations}/delete"
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "csl"
 *   }
 * )
 */
class IslandoraCitation extends ConfigEntityBase implements IslandoraCitationInterface {

  /**
   * The islandora_citations ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The islandora_citations label.
   *
   * @var string
   */
  protected $label;

  /**
   * The islandora_citations status.
   *
   * @var bool
   */
  protected $status;

  /**
   * The islandora_citations csl.
   *
   * @var string
   */
  protected $csl;

  /**
   * {@inheritdoc}
   */
  public function getCslText() {
    return $this->csl;
  }

  /**
   * {@inheritdoc}
   */
  public function setCslText($csl_text) {
    $this->csl = $csl_text;
    return $this;
  }

}
