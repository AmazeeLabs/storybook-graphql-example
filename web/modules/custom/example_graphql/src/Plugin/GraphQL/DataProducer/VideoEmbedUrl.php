<?php

namespace Drupal\example_graphql\Plugin\GraphQL\DataProducer;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\media\IFrameUrlHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DataProducer(
 *   id = "video_oembed_url",
 *   name = @Translation("Video oEmbed URL"),
 *   description = @Translation("The oEmbed video URL."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("The oEmbed video URL."),
 *   ),
 *   consumes = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity")
 *     )
 *   }
 * )
 */
class VideoEmbedUrl extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  use DependencySerializationTrait;

  /**
   * @var \Drupal\media\IFrameUrlHelper
   */
  protected $iframeUrlHelper;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('media.oembed.iframe_url_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, IFrameUrlHelper $iframeUrlHelper) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->iframeUrlHelper = $iframeUrlHelper;
  }

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return string
   */
  public function resolve(EntityInterface $entity) {
    $source = $entity->get('field_media_oembed_video')->getValue()[0]['value'];
    // We need these for the hash generator. If we just send NULL, then the
    // OEmbedIframeController::render() will actually convert them to 0 when
    // generating the hash, which will return an access denied on the page.
    $max_width = 0;
    $max_height = 0;
    $url = Url::fromRoute('media.oembed_iframe', [], [
      'query' => [
        'url' => $source,
        'max_width' => $max_width,
        'max_height' => $max_height,
        'hash' => $this->iframeUrlHelper->getHash($source, $max_width, $max_height),
        'rel' => 0,
      ],
    ]);
    return $url;
  }
}
