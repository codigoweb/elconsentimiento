<?php


namespace Drupal\elconsentimiento\Event;


use Symfony\Component\EventDispatcher\Event;

class ElconsentimientoStatusEvent extends Event {

  /**
   * UUID.
   *
   * @var string
   */
  protected $uuid;

  /**
   * Status.
   *
   * @var integer
   */
  protected $status;

  /**
   * Singer ID.
   *
   * @var string
   */
  protected $signer_id;

  /**
   * ElconsentimientoStatusEvent constructor.
   * @param $status
   */
  public function __construct($uuid, $status, $signer_id ) {
    $this->uuid = $uuid;
    $this->status = $status;
    $this->signer_id = $signer_id;
    if (\Drupal::config('.elconsentimiento.settings')->get('debug_mode') == 1) {
      \Drupal::logger('ElconsentimientoStatusEvent')
        ->info('Construct. UUID: @uuid, status: @status', ['@uuid' => $uuid, '@status' => $status]);
    }
  }

  /**
   * Return UUID
   * @return string
   */
  public function getUuid(){
    return $this->uuid;
  }

  /**
   * Return status
   * @return integer
   */
  public function getStatus(){
    return $this->status;
  }

  /**
   * Return signer ID
   * @return string
   */
  public function getSignerId(){
    return $this->signer_id;
  }

}
