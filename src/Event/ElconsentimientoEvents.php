<?php


namespace Drupal\elconsentimiento\Event;

/**
 * Defines events for the elconsentimiento module.
 * Class ElconsentimientoEvents
 * @package Drupal\elconsentimiento\Event
 */

class ElconsentimientoEvents {

  /**
   * Name of the event fired after document status changed.
   *
   * @Event
   * @see \Drupal\elconsentimiento\Event\ElconsentimientoEvents
   * @var string
   *
   */
  const ELCONSENTIMIENTO_STATUS_CHANGED = 'elconsentimiento.status_changed';
}
