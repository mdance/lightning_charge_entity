<?php

namespace Drupal\lightning_charge_entity;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the LightningChargeEntityPermissions class.
 */
class LightningChargeEntityPermissions implements ContainerInjectionInterface {

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
   * Returns an array of permissions.
   *
   * @return array
   *   An array of permissions.
   */
  public function buildPermissions() {
    $output = [];

    $output[LightningChargeEntityConstants::PERMISSION_INSTANCES] = [
      'title' => 'Administer Lightning Charge Instance Prices',
    ];

    // @todo Add instance permissions

    return $output;
  }

}
