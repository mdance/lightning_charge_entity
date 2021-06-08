<?php

namespace Drupal\lightning_charge_entity;

/**
 * Provides the LightningChargeEntityConstants class.
 */
class LightningChargeEntityConstants {

  /**
   * Provides the entity settings table.
   *
   * @var string
   */
  const TABLE = 'lightning_charge_entity';

  /**
   * Provides the invoices table.
   *
   * @var string
   */
  const TABLE_INVOICES = 'lightning_charge_entity_invoices';

  /**
   * Provides the configuration key.
   *
   * @var string
   */
  const KEY_SETTINGS = 'lightning_charge_entity.settings';

  /**
   * Provides the metadata type.
   *
   * @var string
   */
  const TYPE = 'lightning_charge_entity';

  /**
   * Provides the wrapper prefix.
   *
   * @var string
   */
  const PREFIX = 'lightning-charge-entity-';

  /**
   * Provides the inherit price mode.
   */
  const PRICE_MODE_INHERIT = 'inherit';

  /**
   * Provides the custom price mode.
   */
  const PRICE_MODE_CUSTOM = 'custom';

  /**
   * Provides the donation price mode.
   */
  const PRICE_MODE_DONATION = 'donation';

  /**
   * Provides the administration permission.
   */
  const PERMISSION_ADMIN = 'administer lightning_charge_entity';

  /**
   * Provides the instances administration permission.
   */
  const PERMISSION_INSTANCES = 'administer lightning_charge_entity instances';

  /**
   * Provides the view invoices permission.
   */
  const PERMISSION_INVOICES = 'view lightning_charge_entity invoices';

}
