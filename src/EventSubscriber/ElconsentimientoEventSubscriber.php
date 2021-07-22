<?php


namespace Drupal\elconsentimiento\EventSubscriber;


use Drupal\elconsentimiento\Event\ElconsentimientoEvents;
use Drupal\elconsentimiento\Event\ElconsentimientoStatusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Sample EventSubscriber to be implemented in other modules.
 */
class ElconsentimientoEventSubscriber implements EventSubscriberInterface {

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents() {
    $events = [
      ElconsentimientoEvents::ELCONSENTIMIENTO_STATUS_CHANGED => ['onStatusChange'],
    ];
    return $events;
  }

  public function onStatusChange(ElconsentimientoStatusEvent $event) {
    $uuid = $event->getUuid();
    $status = $event->getStatus();
    $signerID = $event->getSignerId();

    if (\Drupal::config('elconsentimiento.settings')->get('debug_mode') == 1) {
      \Drupal::logger('ElconsentimientoEventSubscriber')
        ->info('onStatusChange. UUID: @uuid, status: @status', ['@uuid' => $uuid, '@status' => $status]);
    }
  }
}
