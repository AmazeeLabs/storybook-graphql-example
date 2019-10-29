<?php

namespace Drupal\example_graphql\Plugin\GraphQL\DataProducer\LanguageSwitcher;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DataProducer(
 *   id = "language_switch_links",
 *   name = @Translation("Language switch links"),
 *   description = @Translation("Returns the list of available interface language switch links."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Language switch link"),
 *     multiple = TRUE
 *   ),
 *   consumes = {
 *     "path" = @ContextDefinition("string",
 *       label = @Translation("The path"),
 *       required = FALSE
 *     ),
 *   }
 * )
 */
class LanguageSwitchLinks extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  use DependencySerializationTrait;

  /**
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected $pathMatcher;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('language_manager'),
      $container->get('path.matcher')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, LanguageManagerInterface $languageManager, PathMatcherInterface $pathMatcher) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->languageManager = $languageManager;
    $this->pathMatcher = $pathMatcher;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($path = NULL) {
    // If there's no specific path for which to return the links, then use the
    // current one.
    if (empty($path)) {
      $route_name = $this->pathMatcher->isFrontPage() ? '<front>' : '<current>';
      $url = Url::fromRoute($route_name);
    }
    else {
      $url = Url::fromUserInput($path);
    }
    $links = $this->languageManager->getLanguageSwitchLinks(LanguageInterface::TYPE_INTERFACE, $url);
    return $links->links;
  }
}
