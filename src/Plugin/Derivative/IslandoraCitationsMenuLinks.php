<?php

declare(strict_types=1);

namespace Drupal\islandora_citations\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides local task definitions for all paragraphs_type entity bundles.
 *
 * @see \Drupal\devel\Controller\EntityDebugController
 * @see \Drupal\devel\Routing\RouteSubscriber
 */
class IslandoraCitationsMenuLinks extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Creates objects.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The translation manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, TranslationInterface $string_translation, ModuleHandlerInterface $module_handler) {
    $this->entityTypeManager = $entity_type_manager;
    $this->stringTranslation = $string_translation;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id): self {
    return new self(
      $container->get('entity_type.manager'),
      $container->get('string_translation'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition): array {
    $links = [];
    if ($this->moduleHandler->moduleExists('admin_toolbar_tools')) {
      $paragraphs_type = $this->entityTypeManager->getStorage('paragraphs_type')
        ->loadMultiple();
      foreach ($paragraphs_type as $id) {
        $current_id = $id->id();
        $content_entity_bundle_root = "admin_toolbar_tools.extra_links:entity.paragraphs_type.edit_form.$current_id";

        $links[$current_id] = [
          'route_name' => 'entity.paragraphs_type.csl_map',
          'title' => $this->t('Citations Map Configuration'),
          'parent' => $content_entity_bundle_root,
          'route_parameters' => ['paragraphs_type' => $current_id],
          'weight' => 3,
        ] + $base_plugin_definition;
      }
    }
    return $links;
  }

}
