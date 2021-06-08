<?php

namespace Drupal\lightning_charge_entity\Events;

/**
 * Provides the LightningChargeEntityEvents class.
 */
final class LightningChargeEntityEvents {

  /**
   * Register supported invoice types.
   *
   * @Event
   */
  const TYPES = 'lightning_charge_entity_types';

}
