<?php

namespace Drupal\lightning_charge_entity\Events;

use Drupal\Component\EventDispatcher\Event;

/**
 * Provides the LightningChargeEntityTypesEvent class.
 */
class LightningChargeEntityTypesEvent extends Event {

  /**
   * Provides the types.
   *
   * @var array
   */
  protected $registry = [];

  /**
   * Adds a type.
   *
   * @param string $key
   *   A string containing the key.
   * @param string $value
   *   A string containing the value.
   *
   * @return $this
   */
  public function add($key, $value) {
    $this->registry[$key] = $value;

    return $this;
  }

  /**
   * Gets the types.
   *
   * @return array
   */
  public function get() {
    return $this->registry;
  }

}
