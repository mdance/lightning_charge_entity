<?php

namespace Drupal\lightning_charge_entity\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormState;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\lightning_charge\Form\InvoicesForm;
use Drupal\lightning_charge_entity\LightningChargeEntityServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides the DefaultController class.
 */
class DefaultController extends ControllerBase {

  /**
   * Provides the form builder service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Provides the route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Provides the module service.
   *
   * @var \Drupal\lightning_charge_entity\LightningChargeEntityServiceInterface
   */
  protected $service;

  /**
   * {@inheritDoc}
   */
  public function __construct(
    FormBuilderInterface $form_builder,
    RouteMatchInterface $route_match,
    LightningChargeEntityServiceInterface $service
  ) {
    $this->formBuilder = $form_builder;
    $this->routeMatch = $route_match;
    $this->service = $service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder'),
      $container->get('current_route_match'),
      $container->get('lightning_charge_entity')
    );
  }

  /**
   * Provides the title callback.
   *
   * @return string
   *   A string containing the title.
   */
  public function titleCallback() {
    $output = $this->t('Invoices');

    $parameters = $this->routeMatch->getParameters();

    $entity = NULL;

    foreach ($parameters as $parameter) {
      if ($parameter instanceof EntityInterface) {
        $entity = $parameter;
        break;
      }
    }

    if ($entity) {
      $args = [];

      $args['@label'] = $entity->label();

      $output = $this->t('@label Invoices', $args);
    }

    return $output;
  }

  /**
   * Views the invoices for an entity.
   *
   * @param EntityInterface $entity
   *   Provides the entity.
   *
   * @return mixed
   *   A render array.
   */
  public function invoices(Request $request) {
    $parameters = $this->routeMatch->getParameters();

    $entity = NULL;

    foreach ($parameters as $parameter) {
      if ($parameter instanceof EntityInterface) {
        $entity = $parameter;
        break;
      }
    }

    $invoices = $this->service->getEntityInvoices($entity);

    $form_state = new FormState();

    $build_info = [
      'args' => [],
    ];

    $args = &$build_info['args'];

    $args['request'] = $request;
    $args['invoices'] = $invoices;
    $args['options'] = [
      'show_order' => FALSE,
    ];

    $form_state->setBuildInfo($build_info);

    $form = $this->formBuilder->buildForm(InvoicesForm::class, $form_state, $invoices);

    return $form;
  }

}
