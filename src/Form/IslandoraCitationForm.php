<?php

namespace Drupal\islandora_citations\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * IslandoraCitationForm form.
 *
 * Class of Islandora citation form.
 */
class IslandoraCitationForm extends EntityForm {

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
      '#description' => $this->t('Label for the islandora citation.'),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $this->entity->id(),
      '#machine_name' => [
        'exists' => '\Drupal\islandora_citations\Entity\IslandoraCitation::load',
      ],
      '#disabled' => !$this->entity->isNew(),
    ];

    $form['csl'] = [
      '#type' => 'textarea',
      '#title' => $this->t('CSL'),
      '#default_value' => $this->entity->get('csl'),
      '#description' => $this->t('CSL of the islandora citation.'),
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
      ? $this->t('Created new islandora_citation %label.', $message_args)
      : $this->t('Updated islandora_citation %label.', $message_args);
    $this->messenger()->addStatus($message);
    $form_state->setRedirectUrl($this->entity->toUrl('collection'));
    return $result;
  }

}
