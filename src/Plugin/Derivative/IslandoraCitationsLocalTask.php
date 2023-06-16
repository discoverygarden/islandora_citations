<?php

declare(strict_types = 1);

namespace Drupal\islandora_citations\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides local task definitions for paragraphs_type entity bundles.
 *
 * @see \Drupal\devel\Controller\EntityDebugController
 * @see \Drupal\devel\Routing\RouteSubscriber
 */
class IslandoraCitationsLocalTask extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Creates objects.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The translation manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, TranslationInterface $string_translation) {
    $this->entityTypeManager = $entity_type_manager;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id): self {
    return new self(
      $container->get('entity_type.manager'),
      $container->get('string_translation')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition): array {
    $this->derivatives = [];

    /** @var  \Drupal\Core\Entity\EntityTypeInterface $entity_type */
    $entity_type = $this->entityTypeManager->getDefinition('paragraphs_type');
    $entity_type_id = $entity_type->id();

    if ($entity_type->hasLinkTemplate('canonical')) {
      $template_key = 'canonical';
    }
    elseif ($entity_type->hasLinkTemplate('edit-form')) {
      $template_key = 'edit_form';
    }
    if (!empty($template_key)) {
      $this->derivatives[$entity_type_id . '.csl_map'] = [
        'route_name' => 'entity.' . $entity_type_id . '.csl_map',
        'title' => $this->t('CSL Map'),
        'base_route' => 'entity.' . $entity_type_id . '.' . $template_key,
        'weight' => 99,
      ];
    }

    foreach ($this->derivatives as &$entry) {
      $entry += $base_plugin_definition;
    }

    return $this->derivatives;
  }

}
