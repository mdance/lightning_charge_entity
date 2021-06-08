<?php

namespace Drupal\lightning_charge_entity\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\lightning_charge_entity\LightningChargeEntityServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LightningChargeEntityLocalTasks extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

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
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('lightning_charge_entity')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $this->derivatives = [];

    $entity_types = $this->service->getEnabledEntityTypes();

    foreach ($entity_types as $k => $v) {
      $route_name = "lightning_charge_entity.$k.invoices";
      $parent_id = "entity.$k.canonical";

      $this->derivatives[$k] = [
        'route_name' => $route_name,
        'title' => $this->t('Invoices'),
        'base_route' => $parent_id,
        'weight' => 110,
      ];
    }

    return $this->derivatives;
  }
}
