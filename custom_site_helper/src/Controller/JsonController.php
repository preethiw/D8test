<?php

namespace Drupal\custom_site_helper\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Access\AccessResult;
use Symfony\Component\Routing\Route;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Controller for the jsonpage of the node.
 */
class JsonController extends ControllerBase {

  /**
   * The storage handler class for nodes.
   *
   * @var \Drupal\node\NodeStorage
   */
  private $nodeStorage;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity
   *   The Entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity) {
    $this->nodeStorage = $entity->getStorage('node');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
    $container->get('entity_type.manager')
    );
  }

  /**
   * Get the json format of the node.
   *
   * @return json
   *   Returns json response of the node.
   */
  public function jsonContent($key, NodeInterface $node) {

    $node_id = $node->id();

    $node_data = $this->nodeStorage->load($node_id);
    $serializer = \Drupal::service('serializer');
    // $node = Node::load($node_id);
    $data = $serializer->serialize($node_data, 'json', ['plugin_id' => 'entity']);

    return new JsonResponse($data);
  }

  /**
   * Checks access for the controller.
   */
  public function access($key, Route $route, NodeInterface $node) {
    // Site api key.
    $site_api_key = \Drupal::config('custom_site_helper.settings')->get('siteapikey');

    // Returns the page if the node content type is the same as
    // route requirement and the argument matches the key.
    return AccessResult::allowedIf(($node->getType() == $route->getRequirement('_content_type')) && ($site_api_key == $key));
  }

}
