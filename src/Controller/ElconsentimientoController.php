<?php

namespace Drupal\elconsentimiento\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;

/**
 * Class ElconsentimientoController.
 *
 * @package Drupal\elconsentimiento\Controller
 */
class ElconsentimientoController extends ControllerBase {

  /**
   * ElconsentimientoController Http Client.
   *
   * @var \Drupal\elconsentimiento\ElconsentimientoService
   */
  protected $service;

  /**
   * Constructs a ElconsentimientoController object.
   *
   */
  public function __construct() {
    $this->service = Drupal::service('elconsentimiento.manager');
  }

  /**
   * Build response.
   *
   * @param string $response
   *   The response content.
   *
   * @return array
   *   A render array.
   */
  private function buildResponse($response) {

    $output = [
      '#type' => 'fieldset',
      'body' => [
        '#markup' => '<p>' . $response . '</p>',
      ],
    ];

    return $output;
  }

  /**
   * Find User.
   *
   * @return array
   *   The service response.
   */
  public function findUser() {
    $content = $this->service->getDocuments();
    return $this->buildResponse($content);
  }

  /**
   * Find all Devices.
   *
   * @return array
   *   The service response.
   */
  public function findDevices() {
    $content = $this->service->getDocuments();
    return $this->buildResponse($content);
  }

  /**
   * Find all Documents.
   *
   * @return array
   *   The service response.
   */
  public function findDocuments() {
    $content = $this->service->getDocuments();
    return $this->buildResponse($content);
  }

  /**
   * Find single Document.
   *
   * @return array
   *   The service response.
   */
  public function findDocument($uuid) {
    $content = $this->service->getDocument($uuid);
    return $this->buildResponse($content);
  }

  /**
   * Find all templates.
   *
   * @return array
   *   The service response.
   */
  public function findTemplates() {
    $content = $this->service->getTemplates();
    return $this->buildResponse($content);
  }

  /**
   * Find single template.
   *
   * @return array
   *   The service response.
   */
  public function findTemplate($uuid) {
    $content = $this->service->getTemplate($uuid);
    return $this->buildResponse($content);
  }

  /**
   * Find single template variables.
   *
   * @return array
   *   The service response.
   */
  public function findTemplateVariables($uuid) {
    $content = $this->service->getTemplateVariables($uuid);
    return $this->buildResponse($content);
  }

  /**
   * Find single template variables.
   *
   * @return array
   *   The service response.
   */
  public function statusCallback($data) {
    $content = $this->service->statusCallback($data);
  }


}
