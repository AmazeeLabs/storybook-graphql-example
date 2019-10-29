<?php

namespace Drupal\example_graphql\Plugin\GraphQL\DataProducer\LanguageSwitcher;

use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * @DataProducer(
 *   id = "language_switch_link_name",
 *   name = @Translation("Language switch link name"),
 *   description = @Translation("The name of a language switch link"),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Language switch link name"),
 *   ),
 *   consumes = {
 *     "link" = @ContextDefinition("any",
 *       label = @Translation("The link")
 *     )
 *   }
 * )
 */
class LanguageSwitchLinkName extends DataProducerPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolve($link) {
    return $link['language']->label();
  }
}
