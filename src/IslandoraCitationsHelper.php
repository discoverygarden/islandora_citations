<?php

namespace Drupal\islandora_citations;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use DrupalFinder\DrupalFinder;
use Psr\Log\LoggerInterface;
use Seboettg\CiteProc\CiteProc;
use Seboettg\CiteProc\StyleSheet;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Helper functions for citations.
 */
class IslandoraCitationsHelper {

  /**
   * Islandora citations entity storage instance.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected EntityStorageInterface $citationsStorage;

  /**
   * File system object.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected FileSystemInterface $fileSystem;

  /**
   * Serializer service object.
   *
   * @var \Symfony\Component\Serializer\SerializerInterface
   */
  protected SerializerInterface $serializer;

  /**
   * Logger object.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected LoggerInterface $logger;

  /**
   * Constructs a new Islandora Citations helper object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $manager
   *   EntityTypeManager interface.
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   File system interface.
   * @param \Symfony\Component\Serializer\SerializerInterface $serializer
   *   Serializer.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger.
   */
  public function __construct(EntityTypeManagerInterface $manager, FileSystemInterface $fileSystem, SerializerInterface $serializer, LoggerInterface $logger) {
    $this->citationsStorage = $manager->getStorage('islandora_citations');
    $this->fileSystem = $fileSystem;
    $this->serializer = $serializer;
    $this->logger = $logger;
  }

  /**
   * Loads csl json schema from file.
   */
  public function loadCslJsonSchema() {
    $schema = &drupal_static(__FUNCTION__);

    if (!isset($schema)) {
      $schema = json_decode(file_get_contents(__DIR__ . '/../data/csl-data.json'), 1);
    }
    return $schema;
  }

  /**
   * Cet citations styles from config entity.
   */
  public function getCitationEntityList(): array {
    $citationIds = $this->citationsStorage->getQuery()->execute();
    $citationEntities = $this->citationsStorage->loadMultiple($citationIds);
    $citationList = [];
    foreach ($citationEntities as $citationEntity) {
      $citationList[$citationEntity->id()] = $citationEntity->label();
    }

    return $citationList;
  }

  /**
   * Load styles from citations style language.
   */
  public function getAllCslsFromCiteproc(): array {
    $drupalFinder = new DrupalFinder();
    $drupalFinder->locateRoot(DRUPAL_ROOT);
    $vendorDir = $drupalFinder->getVendorDir();
    $cslStyleDirectory = $vendorDir . '/citation-style-language/styles';
    $styleList = $this->fileSystem->scanDirectory($cslStyleDirectory, '/\.csl/', ['recurse' => FALSE]);
    if (!empty($styleList)) {
      $cslList = array_column($styleList, 'name');
      return array_combine($cslList, $cslList);
    }

    return [];
  }

  /**
   * Load style string from entity or file.
   *
   * @throws \Seboettg\CiteProc\Exception\CiteProcException
   */
  public function loadStyle($styleName, $styleType = 'entity') {
    if ($styleType == 'entity') {
      $entity = $this->citationsStorage->load($styleName);
      if ($entity instanceof ConfigEntityInterface) {
        return $entity->getCslText();
      }
    }
    else {
      return StyleSheet::loadStyleSheet($styleName);
    }
  }

  /**
   * Render entity with citeproc.
   */
  public function renderWithCiteproc(array $data, string $style, string $mode = 'bibliography') {
    try {
      $citeProc = new CiteProc($style);
      $rendered_array['data'] = $citeProc->render($data, $mode);
      $rendered_array['styles'] = $citeProc->renderCssStyles();
      return $rendered_array;
    }
    catch (\Exception $e) {
      $this->logger->error($e->getMessage());
    }
  }

  /**
   * Encode entity as a csl json array.
   *
   * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
   * @throws \Exception
   */
  public function encodeEntityForCiteproc(EntityInterface $entity): object {
    try {
      $cslEncodedData = $this->serializer->normalize($entity, 'csl-json');
      return (object) $cslEncodedData;
    }
    catch (\Exception $e) {
      $this->logger->error($e->getMessage());
    }
  }

}
