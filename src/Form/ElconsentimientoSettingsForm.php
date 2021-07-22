<?php


namespace Drupal\elconsentimiento\Form;

use Drupal;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use stdClass;

class ElconsentimientoSettingsForm extends ConfigFormBase {

  /**
   * The form elements.
   *
   * @var array
   */
  protected $formElements;

  /**
   * ElconsentimientoController Http Client.
   *
   * @var \Drupal\elconsentimiento\ElconsentimientoService
   */
  protected $service;

  public function __construct(ConfigFactoryInterface $config_factory) {
    parent::__construct($config_factory);
    $this->formElements = [
      'login' => 'Login',
      'user' => 'GET user',
      'devices' => 'GET devices',
      'documents' => 'GET documents',
      'get_document' => 'GET single document',
      'post_document' => 'POST document',
      'templates' => 'GET templates',
      'template' => 'GET single template',
      'template_variables' => 'GET single template variables',
    ];
    $this->service = Drupal::service('elconsentimiento.manager');

  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      '.elconsentimiento.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'elconsentimiento_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('elconsentimiento.settings');

    $method_options = ['GET' => 'GET', 'POST' => 'POST'];

    $form['general'] = [
      '#title' => $this->t('General Settings'),
      '#type' => 'fieldset',
    ];

    $form['general']['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL/path'),
      '#required' => TRUE,
      '#default_value' => $config->get('url'),
    ];

    $host = Drupal::request()->getSchemeAndHttpHost();
    $form['general']['calback_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Callback URL'),
      '#required' => FALSE,
      '#disabled' => TRUE,
      '#default_value' => $host . '/api/v1/elconsentimiento/callback',
    ];

    $form['general']['admin_user'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username'),
      '#required' => TRUE,
      '#default_value' => $config->get('admin_user'),
    ];

    $form['general']['admin_pwd'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Password'),
      '#required' => TRUE,
      '#default_value' => $config->get('admin_pwd'),
    ];

    $template_options = $this->getTempalesOptions();

    $form['general']['template_uuid'] = [
      '#type' => 'select',
      '#options' => $template_options,
      '#title' => $this->t('Template'),
      '#required' => FALSE,
      '#default_value' => $config->get('template_uuid'),
    ];

    $form['general']['document_type'] = [
      '#type' => 'select',
      '#title' => t('Signature Type'),
      '#options' => [
        3 => 'Remote',
      ],
      '#required' => FALSE,
      '#default_value' => $config->get('document_type'),
    ];

    $form['general']['folder'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Folder'),
      '#required' => TRUE,
      '#default_value' => $config->get('folder'),
    ];

    $form['general']['notificationURL'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Notification URL'),
      '#required' => TRUE,
      '#default_value' => $config->get('notificationURL'),
    ];

    $form['general']['fileName'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Filename'),
      '#required' => TRUE,
      '#default_value' => $config->get('fileName'),
    ];

    $form['general']['debug_mode'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable debug mode'),
      '#default_value' => $config->get('debug_mode'),
      '#description' => $this->t('Check this to enable debug information. The debug information will be stored in "dblog".'),
    ];

    $form['paths'] = [
      '#title' => t('Paths'),
      '#type' => 'fieldset',
    ];
    foreach ($this->formElements as $element => $title) {
      $form['paths'][$element] = [
        '#type' => 'textfield',
        '#title' => t($title),
        '#required' => TRUE,
        '#default_value' => $config->get($element),
      ];
    }

    // Template setting
    $form['template'] = [
      '#title' => $this->t('Template Settings'),
      '#type' => 'fieldset',
    ];

    // Tests
    $form['test'] = [
      '#title' => $this->t('Tests'),
      '#type' => 'fieldset',
    ];

    // Delete all webform:demo_event_registration nodes.
    $uuid = '1bc7830a-2238-44fd-a73e-b3f0492ba89b';
    $entity_ids = \Drupal::entityQuery('node')
      ->condition('type', 'paciente')
//      ->condition('field_consent_form_uuid', $uuid)
      ->condition('nid', 264)
      ->execute();
    if ($entity_ids) {
      /** @var \Drupal\node\Entity\Node[] $nodes */
      $nodes = \Drupal\node\Entity\Node::loadMultiple($entity_ids);
      foreach ($nodes as $node) {
        if ($node->hasField('field_consent_form_status')) {
          $node->set('field_consent_form_status', 1);
          $node->set('field_consent_form_uuid', '1bc7830a-2238-44fd-a73e-b3f0492ba89b');
//          $node->save();
        }
      }
    }

    $uuid = $config->get('template_uuid');

    if (!empty($uuid)) {
      $signer['idn'] = '12345678A';
      $signer['typeOfIdn'] = "NIF";
      $signer['name'] = 'Firmante Test';
      $signer['lastName'] = "Test";
      $variables['monitor'] = "MONITOR";
      $variables['center_name'] = "CENTER_NAME";
//      $signer['device'] = 'D82310699-1668-VIDM0004';
      $signer['email'] = 'javier.gomez+25@navandu.com';
      $signer['phone'] = '+34630028510';
      $signer['newSigner'] = false;
      $template = $this->service->buildPost($uuid, $signer, $variables);
    }

    $form['test']['test_post_document'] = [
      '#type' => 'textarea',
      '#title' => $this->t('POST document Body TEST'),
      '#description' => t('Enter a body to test.'),
      '#required' => FALSE,
      '#default_value' => !empty($template) ? $template : '',
    ];

    $form['test']['test_uuid'] = [
      '#type' => 'textfield',
      '#title' => $this->t('UUID'),
      '#description' => t('UUID to test.'),
      '#required' => FALSE,
    ];

    $form['actions']['action_test_post_document'] = [
      '#type' => 'submit',
      '#value' => $this->t('Test POST document'),
      '#submit' => ['::submitTestPsotDocument'],
    ];

    $form['actions']['action_test_document_status'] = [
      '#type' => 'submit',
      '#value' => $this->t('Test document status'),
      '#submit' => ['::submitTestDocumentStatus'],
    ];

    $form['actions']['action_test_template'] = [
      '#type' => 'submit',
      '#value' => $this->t('Test template'),
      '#submit' => ['::submitTestTemplate'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('elconsentimiento.settings');
    $config->delete();

    $config->set('url', $form_state->getValue('url'))
      ->set('admin_user', $form_state->getValue('admin_user'))
      ->set('admin_pwd', $form_state->getValue('admin_pwd'))
      ->set('debug_mode', $form_state->getValue('debug_mode'))
      ->set('template_uuid', $form_state->getValue('template_uuid'))
      ->set('document_type', $form_state->getValue('document_type'))
      ->set('folder', $form_state->getValue('folder'))
      ->set('notificationURL', $form_state->getValue('notificationURL'))
      ->set('fileName', $form_state->getValue('fileName'))
      ->save();

    foreach ($this->formElements as $element => $title) {
      $config->set($element, $form_state->getValue($element))->save();
    }


    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitTestPsotDocument(array &$form, FormStateInterface $form_state) {
    $data = $form_state->getValue('test_post_document');
    $uuid = $this->service->postDocument($data);
    Drupal::messenger()->addMessage('UUID: ' . $uuid);
  }

  /**
   * {@inheritdoc}
   */
  public function submitTestDocumentStatus(array &$form, FormStateInterface $form_state) {
    $uuid = $form_state->getValue('test_uuid');
    $status = $this->service->getDocumentStatus($uuid);
    if ($status instanceof stdClass) {
      $status_id = $status->id;
      $status_name = $status->name;
      Drupal::messenger()
        ->addMessage(t('<p>UUID: <em>@uuid</em></p><p>Status: <em>@status_id</em> <em>@status_name</em></p>', [
          '@status_id' => $status_id,
          '@status_name' => $status_name,
          '@uuid' => $uuid,
        ]));
    }
    else {
      Drupal::messenger()
        ->addMessage(t('Data not found for UUID: <em>@uuid</em>', ['@uuid' => $uuid]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitTestTemplate(array &$form, FormStateInterface $form_state) {
    $uuid = $form_state->getValue('test_uuid');
    $template = $this->service->getTemplate($uuid);
    if ($template instanceof stdClass) {
      $a = (array) $template;
      Drupal::messenger()
        ->addMessage(t('UUID: <em>@uuid</em>. Template Name: <em>@name</em>', [
          '@uuid' => $uuid,
          '@name' => $template->name,
        ]));
    }
    else {
      Drupal::messenger()
        ->addMessage(t('Template not found. UUID: <em>@uuid</em>', ['@uuid' => $uuid]));
    }
  }

  private function getTempalesOptions() {
    $result = [];
    $templates = $this->service->getTemplates();
    if (!empty($templates)) {
      $templates = json_decode($templates);
      foreach ($templates as $template) {
        if ($template instanceof stdClass) {
          $uuid = strval($template->uuid);
          $result[$uuid] = $template->name;
        }
      }
    }
    return $result;
  }

}
