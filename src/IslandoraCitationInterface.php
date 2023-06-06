<?php

namespace Drupal\islandora_citations;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining a islandora_citation entity type.
 */
interface IslandoraCitationInterface extends ConfigEntityInterface {

  /**
   * Get text of CSL style.
   *
   * @return string
   *   CSL text
   */
  public function getCslText();

  /**
   * Set text of CSL style.
   *
   * @param string $csl_text
   *   The new CSL text.
   *
   * @return $this
   */
  public function setCslText($csl_text);

}
