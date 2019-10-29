<?php

namespace Drupal\example_graphql\Plugin\GraphQL\DataProducer;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DataProducer(
 *   id = "current_language",
 *   name = @Translation("The current language"),
 *   description = @Translation("Returns the current language code."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("The current language code"),
 *   )
 * )
 */
class CurrentLanguage extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  use DependencySerializationTrait;

  /**
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, LanguageManagerInterface $languageManager) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->languageManager = $languageManager;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve() {
    $currentLanguage = $this->languageManager->getCurrentLanguage();
    return $currentLanguage->getId();
  }
}
