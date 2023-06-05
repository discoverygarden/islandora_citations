<?php

namespace Drupal\islandora_citations\Form;

use Drupal\Core\Form\FormStateInterface;
use \DOMDocument;

/**
 * IslandoraCitationFileForm form.
 *
 * Class of Islandora citation file upload form.
 */
class IslandoraCitationFileForm extends IslandoraCitationForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    $form = parent::form($form, $form_state);

    unset($form['csl']);

    $form['file'] = [
      '#type' => 'file',
      '#title' => $this->t('File'),
      '#description' => $this->t('Allowed types: @extensions.', ['@extensions' => 'csl, xml']),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($file = $this->extractFile()) {
      $content = file_get_contents($file->getRealPath());
      
      if ($this->isXMLContentValid($content)) {
        $csl = simplexml_load_string($content);
        $form_state->setValue('csl', $content);
        parent::validateForm($form, $form_state);
      } else {
        $form_state->setErrorByName('file', $this->t('The uploaded file does not contain valid CSL.'));
      }
    }
    else {
      $form_state->setErrorByName('file', $this->t('The file could not be uploaded.'));
    }
  }

/**
 * @param string $xmlContent A well-formed XML string
 * @param string $version 1.0
 * @param string $encoding utf-8
 * @return bool
 */
  function isXMLContentValid($xmlContent, $version = '1.0', $encoding = 'utf-8'){
    if (trim($xmlContent) == '') {
        return false;
    }
    libxml_use_internal_errors(true);
    $doc = new DOMDocument($version, $encoding);
    $doc->loadXML($xmlContent);
    $errors = libxml_get_errors();
    libxml_clear_errors();
    return empty($errors);
  }

  /**
   * Extract valid file from request.
   *
   * @return null|\Symfony\Component\HttpFoundation\File\UploadedFile
   *   Uploaded file or NULL if file not uploaded.
   */
  protected function extractFile() {
    $all_files = $this->getRequest()->files->get('files', []);
    if (!empty($all_files['file'])) {
      /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
      $file = $all_files['file'];
      if ($file->isValid()) {
        return $file;
      }
    }

    return NULL;
  }

}