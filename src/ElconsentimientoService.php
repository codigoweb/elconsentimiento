<?php

namespace Drupal\elconsentimiento;

use Drupal;
use Drupal\Component\Serialization\Json;
use Drupal\elconsentimiento\Event\ElconsentimientoEvents;
use Drupal\elconsentimiento\Event\ElconsentimientoStatusEvent;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;

class ElconsentimientoService {

  /**
   * The elconsentimiento module config object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Constructs a ElconsentimientoController object.
   *
   */
  public function __construct() {
    $this->config =  Drupal::config('elconsentimiento.settings');
  }

  /**
   * Helper function to issue a HTTP request with GUZZLE.
   *
   * @param string $method
   *   HTTP method, one of GET, POST, PUT or DELETE.
   * @param string|\Drupal\Core\Url $url
   *   A Url object or system path.
   * @param array $args
   *   The command arguments, headers, body, etc.
   *
   * @return \GuzzleHttp\Command\ResultInterface
   *   The service result.
   */
  private function request($method, $url, array $args = []) {

    try {
      $response = Drupal::httpClient()->request($method, $url, $args);
    } catch (RequestException $exception) {
      Drupal::messenger()->addError(t('There was an error executing the "%command" command. "%error"', ['%command' => $method, '%error' => $exception->getMessage()]));
      return FALSE;
    }
    return $response;
  }

  /**
   * Return an access token.
   *
   * @return string
   *   The access token.
   */
  private function getAccessToken() {

//    $cid = 'elconsentimiento.es.login';
//    $data_cache = Drupal::cache()->get($cid);
//    if (!empty($data_cache)) {
//      return $data_cache;
//    }

    $credentials = [
      'username' => $this->config->get('admin_user'),
      'password' => $this->config->get('admin_pwd'),
    ];
    if (empty($credentials["username"]) || empty($credentials["password"])) {
      return [];
    }
    $url = $this->config->get('url') . $this->config->get('login');
    $headers = [
      'Accept' => 'application/json',
    ];
    $body = json_encode($credentials, JSON_PRETTY_PRINT);
    $args = ['verify' => TRUE, 'body' => $body, 'headers' => $headers];
    $response = $this->request('POST', $url, $args);
    $data = $response->getHeader('Authorization');

    //    Drupal::cache()->set($cid, $data);
    return $data;
  }

  /**
   * Helper function.
   *
   * @param string $method
   *   HTTP method, one of GET, POST, PUT or DELETE.
   * @param string|\Drupal\Core\Url $url
   *   A Url object or system path.
   * @param string $body
   *   The Body data for POST method.
   *
   * @return array
   *   The service response.
   */
  protected function get($url) {
    $token = $this->getAccessToken();
    if (empty($token)) {
      return [];
    }

    $headers = [
      'Accept' => '*/*',
      'Content-Type' => 'application/json',
      'Accept-Encoding' => 'deflate, gzip, br',
      'Authorization' => $token
    ];

    $args = ['verify' => TRUE, 'headers' => $headers];
    $response = $this->request('GET', $url, $args);
    if ($response instanceof Response) {
      $result = (string) $response->getBody();
      return $result;
    }
  }

  /**
   * Get User.
   *
   * @return array
   *   The service response.
   */
  public function getUser() {
    $url = $this->config->get('url'). $this->config->get('user');
    return $this->get($url);
  }

  /**
   * Get all Devices.
   *
   * @return array
   *   The service response.
   */
  public function getDevices() {
    $url = $this->config->get('url'). $this->config->get('devices');
    return $this->get($url);
  }

  /**
   * Get all Documents.
   *
   * @return array
   *   The service response.
   */
  public function getDocuments() {
    $url = $this->config->get('url'). $this->config->get('documents');
    return $this->get($url);
  }

  /**
   * Get single Document.
   *
   * @return array
   *   The service response.
   */
  public function getDocument($uuid) {
    $result = '';
    $paht = str_replace('{uuid}', $uuid, $this->config->get('get_document'));
    $url = $this->config->get('url'). $paht;
    $data = $this->get($url);
    if (!empty($data)) {
      $result = Json::decode($data);
    }
    return $result;
  }

  /**
   * Get single Document.
   *
   * @return array
   *   The service response.
   *
   * Values of status
   * id: 1, name: pending
   * id: 2, name: signed
   * id: 3, name: rejected
   * id: 4, name: generating
   */
  public function getDocumentStatus($uuid) {
    $paht = str_replace('{uuid}', $uuid, $this->config->get('get_document'));
    $url = $this->config->get('url'). $paht;
    $data = $this->get($url);
    if (!empty($data)) {
      $decoded_data = Json::decode((string) $data);
      return $decoded_data->status;
    }
  }

  /**
   * POST Document.
   *
   * @return array
   *   The service response.
   */
  public function postDocument($body) {
    $url = $this->config->get('url'). $this->config->get('post_document');
    $token = $this->getAccessToken();

    $headers = [
      'Accept' => '*/*',
      'Content-Type' => 'application/json',
      'Accept-Encoding' => 'deflate, gzip, br',
      'Authorization' => $token
    ];

//    $body = json_encode($body, JSON_PRETTY_PRINT);

    $args = ['debug' => FALSE, 'verify' => TRUE, 'body' => $body, 'headers' => $headers];
    $response = $this->request('POST', $url, $args);

    $data = $response->getBody()->getContents();
    if (!empty($data)) {
      $decoded_data = Json::decode((string) $data);
        $uuid = $decoded_data['uuid'];
      return $uuid;
    }
  }

  /**
   * Get all templates.
   *
   * @return array
   *   The service response.
   */
  public function getTemplates() {
    $url = $this->config->get('url'). $this->config->get('templates');
    return $this->get($url);
  }

  /**
   * Get single template.
   *
   * @return array
   *   The service response.
   */
  public function getTemplate($uuid) {
    $result = '';
    $paht = str_replace('{uuid}', $uuid, $this->config->get('template'));
    $url = $this->config->get('url'). $paht;
    $data = $this->get($url);
    if (!empty($data)) {
      $result = Json::decode($data);
    }
    return $result;
  }

  /**
   * Get single template variables.
   *
   * @return array
   *   The service response.
   */
  public function getTemplateVariables($uuid) {
    $result = '';
    $paht = str_replace('{uuid}', $uuid, $this->config->get('template_variables'));
    $url = $this->config->get('url'). $paht;
    $data = $this->get($url);
    if (!empty($data)) {
      $result = Json::decode($data);
    }
    return $result;
  }

  /**
   * Callback called from POST rest resource.
   *
   * @param array $payload
   *
   * @return void
   */
  public function statusCallback($payload) {

    if (!empty($payload)) {
      $uuid = $payload['uuid'];
      $signerId = $payload['signerId'];
      $statusId = $payload['status']['id'];
      if (!empty($statusId)) {
        if ($this->config->get('debug_mode') == 1) {
          \Drupal::logger('ElconsentimientoService')
            ->info('statusCallback. UUID: @uuid, status: @status', ['@uuid' => $uuid, '@status' => $statusId]);
        }
        // Allow other modules to do something when document status was changed.
        $event = new ElconsentimientoStatusEvent($uuid, $statusId, $signerId);
        /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher */
        $event_dispatcher = \Drupal::service('event_dispatcher');
        $event_dispatcher->dispatch(ElconsentimientoEvents::ELCONSENTIMIENTO_STATUS_CHANGED, $event);
      }
    }
  }

  /**
   * Callback called from POST rest resource.
   *
   * @param string $uuid
   * The UUID of template
   *
   * @param array $signer
   * Array with signer data
   *
   * @param array $vars
   * Array with values to replace in variables. Example:
   * &vars = ['monitor' => 'DOCTOR NAME', 'center_name' => 'CENTER NAME']
   *
   * @return string
   */
  public function buildPost($uuid, $signer, $vars) {
    $variables = $this->getTemplateVariables($uuid);
    // Some variables must be replaced values
    if (!empty($vars)) {
      foreach ($variables as &$variable) {
        if (!empty($value = $vars[$variable->name])) {
          $variable->value = $value;
        }
      }
    }
    $result['folder'] = $this->config->get('folder');
    $result['templateUuid'] = $uuid;
    $result['documentType'] = $this->config->get('document_type');
    $result['variables'] = $variables;
    $result['signers'][] = $signer;
    $result['notificationURL'] = $this->config->get('notificationURL');
    $result['fileName'] = $this->config->get('fileName');
    return json_encode($result);

  }

}
