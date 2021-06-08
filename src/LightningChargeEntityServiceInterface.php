<?php

namespace Drupal\lightning_charge_entity;

use Drupal\Core\Entity\EntityInterface;

/**
 * Provides the LightningChargeEntityServiceInterface interface.
 */
interface LightningChargeEntityServiceInterface {

  /**
   * Gets the entity types.
   *
   * @return \Drupal\Core\Entity\EntityType[]
   *   An array of entity types.
   */
  public function getEntityTypes();

  /**
   * Gets the entity type options.
   *
   * @return array
   *   An array of entity type options.
   */
  public function getEntityTypeOptions();

  /**
   * Gets the enabled entity types.
   *
   * @return \Drupal\Core\Entity\EntityType[]
   *   An array of enabled entity types.
   */
  public function getEnabledEntityTypes();

  /**
   * Gets the enabled entity type options.
   *
   * @return array
   *   An array of enabled entity type options.
   */
  public function getEnabledEntityTypeOptions();

  /**
   * Gets the entity type bundles.
   *
   * @param string $entity_type
   *   A string containing the entity type.
   *
   * @return array
   *   An array of entity type bundles.
   */
  public function getEntityTypeBundles($entity_type);

  /**
   * Gets the entity type bundle options.
   *
   * @param string $entity_type
   *   A string containing the entity type.
   *
   * @return array
   *   An array of entity type bundle options.
   */
  public function getEntityTypeBundleOptions($entity_type);

  /**
   * Gets the enabled entity type bundles.
   *
   * @param string $entity_type
   *   A string containing the entity type.
   *
   * @return array
   *   An array of enabled entity type bundles.
   */
  public function getEnabledEntityTypeBundles($entity_type);

  /**
   * Gets the enabled entity type bundle options.
   *
   * @param string $entity_type
   *   A string containing the entity type.
   *
   * @return array
   *   An array of enabled entity type bundle options.
   */
  public function getEnabledEntityTypeBundleOptions($entity_type);

  /**
   * Gets the entity type view modes.
   *
   * @param string $entity_type
   *   A string containing the entity type.
   *
   * @return array
   *   An array of entity type view modes.
   */
  public function getEntityTypeViewModes($entity_type);

  /**
   * Gets the enabled view modes for a bundle.
   *
   * @param string $entity_type
   *   A string containing the entity type.
   * @param string $bundle
   *   A string containing the bundle.
   *
   * @return array
   *   An array of enabled bundle view modes.
   */
  public function getEnabledViewModes($entity_type, $bundle);

  /**
   * Returns a boolean indicating if the functionality is enabled.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Provides the entity.
   * @param null|string $view_mode
   *   Provides the view mode.
   *
   * @return bool
   *   A boolean indicating if the functionality is enabled.
   */
  public function isEnabled(EntityInterface $entity, $view_mode = NULL);

  /**
   * Gets the price modes.
   *
   * @param bool $inherit
   *   A boolean indicating whether to display the inherit mode.
   *
   * @return array
   *   An array of price modes.
   */
  public function getPriceModes($inherit = TRUE);

  /**
   * Gets the price mode.
   *
   * @param array $params
   *   An array of lookup parameters containing the following keys:
   *     entity_type:  A string containing the entity type.
   *     bundle: A string containing the bundle.
   *     view_mode: A string containing the view mode.
   *     entity: The entity object.
   *
   * @return array
   *   The price mode.
   */
  public function getPriceMode(array $options = []);

  /**
   * Gets the price.
   *
   * @param array $options
   *   An array of lookup parameters containing the following keys:
   *     entity_type:  A string containing the entity type.
   *     bundle: A string containing the bundle.
   *     view_mode: A string containing the view mode.
   *     entity: The entity object.
   * @param bool $price_mode
   *   A boolean indicating whether to check the price mode.
   *
   * @return array
   *   The price.
   */
  public function getPrice(array $options, bool $price_mode = TRUE);

  /**
   * Gets the override setting.
   *
   * @param array $params
   *   An array of lookup parameters containing the following keys:
   *     entity_type:  A string containing the entity type.
   *     bundle: A string containing the bundle.
   *     view_mode: A string containing the view mode.
   *     entity: The entity object.
   *
   * @return array
   *   The price.
   */
  public function getOverrides(array $options);

  /**
   * Gets the instances override setting.
   *
   * @param array $params
   *   An array of lookup parameters containing the following keys:
   *     entity_type:  A string containing the entity type.
   *     bundle: A string containing the bundle.
   *     view_mode: A string containing the view mode.
   *     entity: The entity object.
   *
   * @return array
   *   An array of instance overrides.
   */
  public function getInstancesOverrides(array $options = []);

  /**
   * Gets the invoices for an entity.
   *
   * @param EntityInterface $entity
   *   The entity object.
   * @param string $view_mode
   *   A string containing the view mode.
   * @param string $hash
   *   A variable passed by reference to receive the hash.
   * @param mixed $account
   *   The account object.
   *
   * @return \Drupal\lightning_charge\Invoice[]
   *   An array of invoices.
   */
  public function getInvoices(EntityInterface $entity, $view_mode, &$hash, $account = NULL);

  /**
   * Gets the invoices for an entity.
   *
   * @param EntityInterface $entity
   *   The entity object.
   *
   * @return \Drupal\lightning_charge\Invoice[]
   *   An array of invoices.
   */
  public function getEntityInvoices(EntityInterface $entity);

  /**
   * Creates an invoice.
   *
   * @param array $props
   *   An array of invoice properties.
   *
   * @return \Drupal\lightning_charge\InvoiceInterface
   *   The invoice object.
   */
  public function createInvoice($props = []);

  /**
   * Saves an entities payment settings.
   *
   * @param EntityInterface $entity
   *   Provides the entity.
   * @param array $values
   *   Provides the payment settings.
   *
   * @return $this
   */
  public function saveEntity($entity, $values);

  /**
   * Gets an entities payment settings.
   *
   * @param EntityInterface $entity
   *   Provides the entity.
   *
   * @return array
   */
  public function getEntity($entity);

}
