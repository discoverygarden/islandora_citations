<?php

namespace Drupal\dgi_citation\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Dgi_citation form.
 *
 * @property \Drupal\dgi_citation\DgiCitationInterface $entity
 */
class DgiCitationFileForm extends DgiCitationForm {

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
  public function save(array $form, FormStateInterface $form_state) {
    $result = parent::save($form, $form_state);
    $message_args = ['%label' => $this->entity->label()];
    $message = $result == SAVED_NEW
      ? $this->t('Created new dgi_citation %label.', $message_args)
      : $this->t('Updated dgi_citation %label.', $message_args);
    $this->messenger()->addStatus($message);
    $form_state->setRedirectUrl($this->entity->toUrl('collection'));
    return $result;
  }

}
