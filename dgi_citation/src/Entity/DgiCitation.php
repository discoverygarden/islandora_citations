<?php

namespace Drupal\dgi_citation\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\dgi_citation\DgiCitationInterface;

/**
 * Defines the dgi_citation entity type.
 *
 * @ConfigEntityType(
 *   id = "dgi_citation",
 *   label = @Translation("DGI Citation"),
 *   label_collection = @Translation("DGI Citation"),
 *   label_singular = @Translation("DGI Citation"),
 *   label_plural = @Translation("DGI Citation"),
 *   label_count = @PluralTranslation(
 *     singular = "@count dgi_citation",
 *     plural = "@count dgi_citations",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\dgi_citation\DgiCitationListBuilder",
 *     "form" = {
 *       "add" = "Drupal\dgi_citation\Form\DgiCitationForm",
 *       "add-file" = "Drupal\dgi_citation\Form\DgiCitationFileForm",
 *       "edit" = "Drupal\dgi_citation\Form\DgiCitationForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     }
 *   },
 *   config_prefix = "dgi_citation",
 *   admin_permission = "administer dgi_citation",
 *   links = {
 *     "collection" = "/admin/structure/dgi-citation",
 *     "add-form" = "/admin/structure/dgi-citation/add",
 *     "add-form-file" = "/admin/structure/dgi-citation/add-file",
 *     "edit-form" = "/admin/structure/dgi-citation/{dgi_citation}",
 *     "delete-form" = "/admin/structure/dgi-citation/{dgi_citation}/delete"
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
class DgiCitation extends ConfigEntityBase implements DgiCitationInterface {

  /**
   * The dgi_citation ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The dgi_citation label.
   *
   * @var string
   */
  protected $label;

  /**
   * The dgi_citation status.
   *
   * @var bool
   */
  protected $status;

  /**
   * The dgi_citation csl.
   *
   * @var string
   */
  protected $csl;

}
