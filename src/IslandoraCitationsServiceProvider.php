<?php

namespace Drupal\islandora_citations;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;

/**
 * Adds CSL-json as known format.
 */
class IslandoraCitationsServiceProvider implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {

    if ($container->has('http_middleware.negotiation') && is_a(
        $container->getDefinition('http_middleware.negotiation')
          ->getClass(), '\Drupal\Core\StackMiddleware\NegotiationMiddleware', TRUE
      )
    ) {
      $container->getDefinition('http_middleware.negotiation')
        ->addMethodCall('registerFormat', ['csl-json', ['application/csl+json']]);
    }
  }

}
