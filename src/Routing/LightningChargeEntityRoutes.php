<?php

namespace Drupal\lightning_charge_entity\Routing;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManagerInterface;
use Drupal\lightning_charge_entity\LightningChargeEntityConstants;
use Drupal\lightning_charge_entity\LightningChargeEntityServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;

/**
 * Provides the LightningChargeEntityRoutes class.
 */
class LightningChargeEntityRoutes implements ContainerInjectionInterface {

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
    LightningChargeEntityServiceInterface $service
  ) {
    $this->service = $service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('lightning_charge_entity')
    );
  }

  /**
   * Returns an array of route objects.
   *
   * @return \Symfony\Component\Routing\Route[]
   *   An array of route objects.
   */
  public function routes() {
    $output = [];

    $entity_types = $this->service->getEnabledEntityTypes();

    foreach ($entity_types as $k => $v) {
      $prefix = $v->getLinkTemplate('canonical');

      if ($prefix) {
        //$prefix = str_replace('{' . $k . '}', '{entity}', $prefix);
        $path = $prefix . '/invoices';

        $defaults = [
          '_title' => 'Invoices',
          '_title_callback' => 'Drupal\lightning_charge_entity\Controller\DefaultController::titleCallback',
          '_controller' => 'Drupal\lightning_charge_entity\Controller\DefaultController::invoices',
        ];

        $requirements = [
          '_permission' => LightningChargeEntityConstants::PERMISSION_INVOICES,
        ];

        $options = [
          'parameters' => [
            $k => [
              'type' => 'entity:' . $k,
            ],
          ],
        ];

        $route = new Route($path, $defaults, $requirements, $options);

        $output['lightning_charge_entity.' . $k . '.invoices'] = $route;
      }
    }

    return $output;
  }

}
