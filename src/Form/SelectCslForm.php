<?php

namespace Drupal\islandora_citations\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\islandora_citations\IslandoraCitationsHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implementing a ajax form.
 */
class SelectCslForm extends FormBase {

  /**
   * Citation helper service.
   *
   * @var Drupal\islandora_citations\IslandoraCitationsHelper
   */
  protected $citationHelper;
  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * {@inheritdoc}
   */
  public function __construct(IslandoraCitationsHelper $citationHelper, RouteMatchInterface $route_match) {
    $this->citationHelper = $citationHelper;
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('islandora_citations.helper'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'select_csl_ajax_submit';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['csl_list'] = [
      '#type' => 'select',
      '#options' => $this->citationHelper->getCitationEntityList(),
      '#empty_option' => $this->t('- Select csl -'),
      '#ajax' => [
        'callback' => '::getCitation',
        'wrapper' => 'formatted-citation',
        'method' => 'html',
        'event' => 'change',
      ],
      '#attributes' => ['aria-label' => $this->t('Select CSL')],
      '#theme_wrappers' => [],
    ];
    $form['formatted-citation'] = [
      '#type' => 'item',
      '#markup' => '<div id="formatted-citation"></div>',
      '#theme_wrappers' => [],
    ];
    $form['#cache']['contexts'][] = 'url';
    return $form;
  }

  /**
   * Setting the message in our form.
   */
  public function getCitation(array $form, FormStateInterface $form_state) {
    $csl_name = $form_state->getValue('csl_list');
    if ($csl_name == '') {
      return [
        '#children' => '',
      ];
    }
    $entity = $this->routeMatch->getParameter('node');
    $citationItems[] = $this->citationHelper->encodeEntityForCiteproc($entity);
    $style = $this->citationHelper->loadStyle($csl_name);
    $rendered = $this->citationHelper->renderWithCiteproc($citationItems, $style);
    $response = [
      '#children' => $rendered,
    ];

    return $response;
  }

  /**
   * Submitting the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
