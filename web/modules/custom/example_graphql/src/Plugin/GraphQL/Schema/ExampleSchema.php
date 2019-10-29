<?php

namespace Drupal\example_graphql\Plugin\GraphQL\Schema;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistry;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerCallable;
use Drupal\graphql\Plugin\GraphQL\Schema\SdlSchemaPluginBase;
use Drupal\node\NodeInterface;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * @Schema(
 *   id = "example",
 *   name = "Example",
 * )
 */
class ExampleSchema extends SdlSchemaPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function getResolverRegistry() {
    $builder = new ResolverBuilder();
    $registry = new ResolverRegistry([
      'NodePage' => ContextDefinition::create('entity:node')
        ->addConstraint('Bundle', 'page'),
    ]);

    $this->registerRootQuery($registry, $builder);
    $this->registerUserContext($registry, $builder);
    $this->registerMenu($registry, $builder);
    $this->registerNodePage($registry, $builder);

    return $registry;
  }

  /**
   * {@inheritdoc}
   */
  protected function getSchemaDefinition() {
    return file_get_contents(drupal_get_path('module', 'example_graphql') . '/schema.graphqls');
  }

  /**
   * Registers the root query fields into the GraphQL schema.
   *
   * @param \Drupal\graphql\GraphQL\ResolverRegistry $registry
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   */
  protected function registerRootQuery(ResolverRegistry $registry, ResolverBuilder $builder) {
    // The root query fields.
    $registry->addFieldResolver('Query', 'menuByName',
      $builder->compose(
        new DataProducerCallable($this->languageContext()),
        $builder->produce('entity_load', [
          'mapping' => [
            'type' => $builder->fromValue('menu'),
            'id' => $builder->fromArgument('name'),
          ],
        ])
      )
    );

    $registry->addFieldResolver('Query', 'languageSwitchLinks',
      $builder->produce('language_switch_links', [
        'mapping' => [
          'path' => $builder->fromArgument('path'),
        ],
      ])
    );

    $registry->addFieldResolver('Query', 'currentUserContext',
      $builder->fromValue([])
    );

    $registry->addFieldResolver('Query', 'countrySwitcherData',
      $builder->produce('country_switcher')
    );

    $registry->addFieldResolver('Query', 'countrySwitcherMobileData',
      $builder->produce('country_switcher_mobile')
    );

    $registry->addFieldResolver('Query', 'pageNode',
      $builder->compose(
        new DataProducerCallable($this->languageContext()),
        $builder->produce('entity_load', [
          'mapping' => [
            'type' => $builder->fromValue('node'),
            'id' => $builder->fromArgument('id'),
          ],
        ]),
        new DataProducerCallable($this->localizeEntity())
      )
    );

    $registry->addFieldResolver('Query', 'menuBreadcrumbFromPath',
      $builder->produce('menu_breadcrumbs_from_path', [
        'mapping' => [
          'menu' => $builder->fromArgument('menu'),
          'path' => $builder->fromArgument('path'),
        ],
      ])
    );

    $entity_fields = [
      'pageNode' => 'node',
      'sliderItemNode' => 'node',
      'block' => 'block_content',
    ];
    foreach ($entity_fields as $field => $entity_type) {
      $registry->addFieldResolver('Query', $field,
        $builder->compose(
          new DataProducerCallable($this->languageContext()),
          $builder->produce('entity_load', [
            'mapping' => [
              'type' => $builder->fromValue($entity_type),
              'id' => $builder->fromArgument('id'),
            ],
          ]),
          new DataProducerCallable($this->localizeEntity())
        )
      );
    }
  }

  /**
   * Registers the user context fields into the GraphQL schema.
   *
   * @param \Drupal\graphql\GraphQL\ResolverRegistry $registry
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   */
  protected function registerUserContext(ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('UserContext', 'currentLanguage',
      $builder->compose(
        new DataProducerCallable($this->languageContext()),
        $builder->produce('current_language')
      )
    );
  }

  /**
   * Registers the page content type fields into the GraphQL schema.
   *
   * @param \Drupal\graphql\GraphQL\ResolverRegistry $registry
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   */
  protected function registerNodePage(ResolverRegistry $registry, ResolverBuilder $builder) {
    // The Page type.
    $registry->addFieldResolver('NodePage', 'title',
      $builder->produce('entity_label', [
        'mapping' => [
          'entity' => $builder->fromParent(),
        ],
      ])
    );
    $registry->addFieldResolver('NodePage', 'nodeType',
      new DataProducerCallable(function (NodeInterface $value) {
        return $value->bundle();
      })
    );
    $registry->addFieldResolver('NodePage', 'header',
      new DataProducerCallable(function (NodeInterface $value) {
        return $value;
      })
    );
    $registry->addFieldResolver('NodePage', 'teaser',
      new DataProducerCallable(function (NodeInterface $value) {
        return $value;
      })
    );
    $registry->addFieldResolver('NodePage', 'contact',
      new DataProducerCallable(function (NodeInterface $value) {
        return $value;
      })
    );
    $registry->addFieldResolver('NodePage', 'personTeaser',
      new DataProducerCallable(function (NodeInterface $value) {
        return $value;
      })
    );
    $registry->addFieldResolver('NodePage', 'eventDetails',
      new DataProducerCallable(function (NodeInterface $value) {
        return $value;
      })
    );
    $registry->addFieldResolver('NodePage', 'sidebarButtons',
      new DataProducerCallable(function (NodeInterface $value) {
        return $value;
      })
    );
    $registry->addFieldResolver('NodePage', 'created',
      new DataProducerCallable(function (NodeInterface $value) {
        return $value->getCreatedTime();
      })
    );
    $registry->addFieldResolver('NodePage', 'updated',
      new DataProducerCallable(function (NodeInterface $value) {
        return $value->getChangedTime();
      })
    );
    $registry->addFieldResolver('NodePage', 'category',
      new DataProducerCallable(function (NodeInterface $value) {
        switch ($value->bundle()) {
          case 'news':
            if ($value->hasField('field_topic') && !$value->get('field_topic')
                ->isEmpty()) {
              $category = $value->field_topic->entity->getName();
            }
            break;

          case 'media_release':
            if ($value->hasField('field_brand') && !$value->get('field_brand')
                ->isEmpty()) {
              $category = $value->field_brand->entity->getName();
            }
            break;

          case 'investor_news':
            if ($value->hasField('field_investor_news_category') && !$value->get('field_investor_news_category')
                ->isEmpty()) {
              $category = $value->field_investor_news_category->entity->getName();
            }
            break;
        }
        return $category ?? FALSE;
      })
    );
    $registry->addFieldResolver('NodePage', 'content',
      new DataProducerCallable(function (NodeInterface $value) {
        /* @var \Drupal\ckeditor5_sections\DocumentSectionInterface $sections */
        $sections = $value->get('body')->sections;
        return !empty($sections) ? $sections->get('sections') : [];
      })
    );
    $registry->addFieldResolver('NodePage', 'teaserRef',
      new DataProducerCallable(function (NodeInterface $value) {
        $items = [];
        if ($value->hasField('field_teaser_ref') && !$value->get('field_teaser_ref')
            ->isEmpty()) {
          $referred_items = $value->get('field_teaser_ref')
            ->referencedEntities();
          if (!empty($referred_items)) {
            $items[] = [
              'title' => ($value->hasField('field_teaser_ref_title') ? $value->field_teaser_ref_title->value : FALSE) ?? '',
              'teasers' => $referred_items,
            ];
          }
        }
        return $items;
      })
    );
    $registry->addFieldResolver('NodePage', 'mediaKit',
      new DataProducerCallable(function (NodeInterface $value) {
        return $value;
      })
    );
    $registry->addFieldResolver('NodePage', 'socialTeaser',
      new DataProducerCallable(function (NodeInterface $value) {
        return $value;
      })
    );
    $registry->addFieldResolver('NodePage', 'financialReport',
      new DataProducerCallable(function (NodeInterface $value) {
        return $value;
      })
    );

    $registry->addFieldResolver('NodePage', 'location',
      new DataProducerCallable(function (NodeInterface $value) {
        return $value;
      })
    );
  }

  /**
   * Registers the menu fields into the GraphQL schema.
   *
   * @param \Drupal\graphql\GraphQL\ResolverRegistry $registry
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   */
  protected function registerMenu(ResolverRegistry $registry, ResolverBuilder $builder) {
    // The Menu type.
    $registry->addFieldResolver('Menu', 'name',
      $builder->produce('entity_label', [
        'mapping' => [
          'entity' => $builder->fromParent(),
        ],
      ])
    );
    $registry->addFieldResolver('Menu', 'links',
      $builder->produce('menu_links', [
        'mapping' => [
          'menu' => $builder->fromParent(),
        ],
      ])
    );

    // The MenuLink type.
    $menu_tree_link = $builder->produce('menu_tree_link', [
      'mapping' => [
        'element' => $builder->fromParent(),
      ],
    ]);
    $registry->addFieldResolver('MenuLink', 'label',
      $builder->compose(
        $menu_tree_link,
        $builder->produce('menu_link_label', [
          'mapping' => [
            'link' => $builder->fromParent(),
          ],
        ])
      )
    );

    $registry->addFieldResolver('MenuLink', 'description',
      $builder->compose(
        $menu_tree_link,
        $builder->produce('menu_link_description', [
          'mapping' => [
            'link' => $builder->fromParent(),
          ],
        ])
      )
    );

    $registry->addFieldResolver('MenuLink', 'url',
      $builder->compose(
        $menu_tree_link,
        $builder->produce('menu_link_url', [
          'mapping' => [
            'link' => $builder->fromParent(),
          ],
        ]),
        $builder->produce('url_path', [
          'mapping' => [
            'url' => $builder->fromParent(),
          ],
        ])
      )
    );

    $registry->addFieldResolver('MenuLink', 'links',
      $builder->produce('menu_tree_subtree', [
        'mapping' => [
          'element' => $builder->fromParent(),
        ],
      ])
    );
  }

  /**
   * Helper method that returns a closure which adds some caching metadata on
   * GraphQL fields.
   *
   * @return \Closure
   */
  protected function languageContext() {
    // @todo: is there a better approach to put the current language into the
    // context?
    return function ($value, $args, ResolveContext $context, ResolveInfo $info) {
      $metadata = new CacheableMetadata();
      $metadata->addCacheContexts(['languages:language_interface']);
      $metadata->addCacheContexts(['languages:language_content']);
      $context->addCacheableDependency($metadata);
      return $value;
    };
  }

  /**
   * Helper method that returns a closure to localize a content entity in the
   * context of a GraphQL field.
   *
   * @return \Closure
   */
  protected function localizeEntity() {
    return function ($value, $args, ResolveContext $context, ResolveInfo $info) {
      if (is_array($value)) {
        $localized = [];
        foreach ($value as $item) {
          $localized[] = $this->_localizeEntity($item, $args, $context, $info);
        }
        return $localized;
      }
      return $this->_localizeEntity($value, $args, $context, $info);
    };
  }

  protected function _localizeEntity($value, $args, ResolveContext $context, ResolveInfo $info) {
    if ($value instanceof ContentEntityInterface) {
      /** @var \Drupal\revision_tree\Entity\RevisionTreeEntityRepositoryInterface $entityRepository */
      $entityRepository = \Drupal::service('entity.repository');
      $localized = $entityRepository->getActive($value->getEntityTypeId(), $value->id(), [
        'workspace' => $context->getContext('workspaceHierarchy', $info),
      ]);
      // @todo: get the language from a graphql context?
      $langCode = \Drupal::languageManager()
        ->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)
        ->getId();
      //$langCode = $context->getContext('languageCode', $info);
      if ($localized->hasTranslation($langCode)) {
        $localized = $localized->getTranslation($langCode);
      }
      return $localized;
    }
    return NULL;
  }

}
