<?php

namespace Drupal\example_graphql\Plugin\GraphQL\DataProducer\LanguageSwitcher;

use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * @DataProducer(
 *   id = "language_switch_link_url",
 *   name = @Translation("Language switch link url"),
 *   description = @Translation("The url of a language switch link"),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Language switch link url"),
 *   ),
 *   consumes = {
 *     "link" = @ContextDefinition("any",
 *       label = @Translation("The link")
 *     )
 *   }
 * )
 */
class LanguageSwitchLinkUrl extends DataProducerPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolve($link) {
    /* @var \Drupal\Core\Url $url */
    $url = $link['url'];
    $url->setOption('language', $link['language']);
    return $url;
  }
}
