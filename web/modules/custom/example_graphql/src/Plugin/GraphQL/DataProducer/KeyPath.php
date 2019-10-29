<?php

namespace Drupal\example_graphql\Plugin\GraphQL\DataProducer;

use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Data producer to retrieve the value from a nested array path.
 *
 * @DataProducer(
 *   id = "key_path",
 *   name = @Translation("Key path"),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Value")
 *   ),
 *   consumes = {
 *     "input" = @ContextDefinition("any",
 *       label = @Translation("Array")
 *     ),
 *     "path" = @ContextDefinition("string",
 *       label = @Translation("The path")
 *     ),
 *    "default" = @ContextDefinition("string",
 *       label = @Translation("The default value"),
 *       required = FALSE
 *     ),
 *   }
 * )
 */
class KeyPath extends DataProducerPluginBase {

  /**
   * @param array $input
   *  The array to search into
   * @param string $path
   *  The key path to search. They keys should be separated by dot.
   * @param mixed $default
   *  The default value to return, in case there is no value for that key.
   * @return mixed
   *  The value stored at that path.
   */
  public function resolve(array $input, $path, $default = NULL) {
    $keys = explode('.', $path);
    $value = $input;
    if (!empty($keys)) {
      foreach ($keys as $key) {
        if (is_array($value) && isset($value[$key])) {
          $value = $value[$key];
        }
        else {
          return $default;
        }
      }
      return $value;
    }
    return $default;
  }
}
