<?php

namespace Drupal\elconsentimiento\Plugin\rest\resource;

use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides a resource to get status document callback.
 *
 * @RestResource(
 *   id = "elconsentimiento_rest_resource",
 *   label = @Translation("Elconsentimiento rest resource"),
 *   uri_paths = {
 *     "create" = "/api/v1/elconsentimiento/callback"
 *   }
 * )
 */
class ElconsentimientoRestResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->logger = $container->get('logger.factory')->get('elconsentimiento');
    $instance->currentUser = $container->get('current_user');
    return $instance;
  }

    /**
     * Responds to POST requests.
     *
     * @param string $payload
     *
     * @return \Drupal\rest\ModifiedResourceResponse
     *   The HTTP response object.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     *   Throws exception expected.
     */
    public function post($payload) {

      // You must to implement the logic of your REST Resource here.
      // Use current user after pass authentication to validate access.
      if (!$this->currentUser->hasPermission('restful post elconsentimiento_rest_resource')) {
          throw new AccessDeniedHttpException();
      }

      // Call service to update status
      \Drupal::service('elconsentimiento.manager')->statusCallback($payload);

      return new ModifiedResourceResponse('Ok', 200);
    }

}
