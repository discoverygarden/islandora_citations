<?php

namespace Drupal\dgi_citation\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Dgi_citation form.
 *
 * @property \Drupal\dgi_citation\DgiCitationInterface $entity
 */
class DgiCitationForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    $form = parent::form($form, $form_state);

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $this->entity->label(),
      '#description' => $this->t('Label for the dgi citation.'),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $this->entity->id(),
      '#machine_name' => [
        'exists' => '\Drupal\dgi_citation\Entity\DgiCitation::load',
      ],
      '#disabled' => !$this->entity->isNew(),
    ];

    $form['csl'] = [
      '#type' => 'textarea',
      '#title' => $this->t('CSL'),
      '#default_value' => $this->entity->get('csl'),
      '#description' => $this->t('CSL of the dgi citation.'),
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
