<?php

namespace Drupal\example_graphql\Plugin\GraphQL\DataProducer\LanguageSwitcher;

use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * @DataProducer(
 *   id = "language_switch_link_id",
 *   name = @Translation("Language switch link id"),
 *   description = @Translation("The id of a language switch link"),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Language switch link id"),
 *   ),
 *   consumes = {
 *     "link" = @ContextDefinition("any",
 *       label = @Translation("The link")
 *     )
 *   }
 * )
 */
class LanguageSwitchLinkId extends DataProducerPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolve($link) {
    return $link['language']->id();
  }
}
