<?php

namespace Drupal\example_graphql\Plugin\GraphQL\DataProducer\Breadcrumb;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\GeneratedUrl;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\menu_link_content\MenuLinkContentInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DataProducer(
 *   id = "menu_breadcrumbs_from_path",
 *   name = @Translation("Breadcrumbs"),
 *   description = @Translation("Returns the list of breadcrumbs based on a given path."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Breadcrumbs"),
 *     multiple = TRUE
 *   ),
 *   consumes = {
 *     "menu" = @ContextDefinition("string",
 *       label = @Translation("The menu from which we load the breadcrumbs."),
 *       required = TRUE
 *     ),
 *     "path" = @ContextDefinition("string",
 *       label = @Translation("The current path."),
 *       required = TRUE
 *     ),
 *   }
 * )
 */
class MenuBreadcrumbFromPath extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  use DependencySerializationTrait;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected $menuLinkTree;

  /**
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected $pathMatcher;

  /**
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $pathAliasManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('entity_type.manager'),
      $container->get('menu.link_tree'),
      $container->get('entity.repository'),
      $container->get('path.matcher'),
      $container->get('language_manager'),
      $container->get('path.alias_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, EntityTypeManagerInterface $entity_type_manager, MenuLinkTreeInterface $menu_link_tree, EntityRepositoryInterface $entity_repository, PathMatcherInterface $path_matcher, LanguageManagerInterface $language_manager, AliasManagerInterface $path_alias_manager) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->entityTypeManager = $entity_type_manager;
    $this->menuLinkTree = $menu_link_tree;
    $this->entityRepository = $entity_repository;
    $this->pathMatcher = $path_matcher;
    $this->languageManager = $language_manager;
    $this->pathAliasManager = $path_alias_manager;
  }

  /**
   * Returns the breadcrumb data.
   *
   * @param string $menu
   * @param string|null $path
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function resolve($menu, $path = NULL) {
    // If there's no specific path for which to return the links, then use the
    // current one.
    if (empty($path)) {
      $route_name = $this->pathMatcher->isFrontPage() ? '<front>' : '<current>';
      $path = Url::fromRoute($route_name);
    }
    else {
      $path = Url::fromUserInput($path);
    }
    if ($path instanceof Url) {
      $path = $path->toString(TRUE);
    }
    if ($path instanceof GeneratedUrl) {
      $path = $path->getGeneratedUrl();
    }
    $parts = explode('/', trim($path, '/'));
    $prefix = array_shift($parts);
    // Search prefix within added languages and remove from path.s
    foreach ($this->languageManager->getLanguages() as $language) {
      // Rebuild $path with the language removed.
      if ($prefix == $language->getId()) {
        $path = '/' . implode('/', $parts);
        break;
      }
    }
    // Get the menu tree.
    $tree = $this->getBreadcrumbMenuTree($path, $prefix);
    $links = [];
    foreach ($tree as $key => $branch) {
      $links[$key] = [
        'url' => $branch->getUrlObject()->toString(TRUE)->getGeneratedUrl(),
        'title' => $branch->getTitle(),
      ];
      // Add siblings to the last item.
      if (end($tree) === $tree[$key]) {
        $parent = $tree[$key - 1] ?? $tree[$key];
        $siblings = $this->getMenuLinkSiblings($tree[$key], $parent, $menu);
        $links[$key]['siblings'] = [];
        foreach ($siblings as $sibling) {
          $links[$key]['siblings'][] = [
            'url' => $sibling->link->getUrlObject()
              ->toString(TRUE)
              ->getGeneratedUrl(),
            'title' => $sibling->link->getTitle(),
          ];
        }
      }
    }
    return $links;
  }

  /**
   * Gets the basic breadcrumb tree, in order, based on the current path.
   *
   * @param string $path
   * @param string $language
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function getBreadcrumbMenuTree($path, $language = 'en') {
    $real_path = $this->pathAliasManager->getPathByAlias($path);
    $query = $this->entityTypeManager->getStorage('menu_link_content')
      ->getQuery();
    // Search for the alias or the actual path.
    $group = $query->orConditionGroup()
      ->condition('link__uri', 'internal:' . $path)
      ->condition('link__uri', 'internal:' . $real_path)
      ->condition('link__uri', 'entity:' . ltrim($real_path, '/'));
    $query->condition($group);
    $result = $query->execute();
    if (!$result) {
      return [];
    }
    $menu_item = $this->entityTypeManager->getStorage('menu_link_content')
      ->load(reset($result));
    $tree = $this->recursivelyGetMenuLinkParentTree($menu_item);
    // Reverse the order.
    $tree = array_reverse($tree);
    // If necessary, get the correct translations for the menu items.
    foreach ($tree as $key => $branch) {
      $item_lang = $branch->language()->getId();
      if ($language == $item_lang) {
        continue;
      }
      if (!$branch->hasTranslation($language)) {
        continue;
      }
      $tree[$key] = $branch->getTranslation($language);
    }
    return $tree;
  }

  /**
   * Given a menu link entity, returns an array of all the parent items.
   *
   * @param \Drupal\menu_link_content\MenuLinkContentInterface $menu_link_content
   * @param array $tree
   *
   * @return array
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function recursivelyGetMenuLinkParentTree(MenuLinkContentInterface $menu_link_content, array $tree = []) {
    $tree[] = $menu_link_content;
    if (!$id = $menu_link_content->getParentId()) {
      return $tree;
    }
    $parts = explode(':', $id);
    $parent = $this->entityRepository->loadEntityByUuid($parts[0], $parts[1]);
    return $this->recursivelyGetMenuLinkParentTree($parent, $tree);
  }

  /**
   * Returns menu siblings when given the current link and the parent link.
   *
   * @param \Drupal\menu_link_content\MenuLinkContentInterface $current_link
   * @param \Drupal\menu_link_content\MenuLinkContentInterface $parent_link
   * @param $menu
   *
   * @return MenuLinkContentInterface[]
   */
  public function getMenuLinkSiblings(MenuLinkContentInterface $current_link, MenuLinkContentInterface $parent_link, $menu) {
    $params = new MenuTreeParameters();
    $params->setRoot($parent_link->getPluginId());
    $tree = $this->menuLinkTree->load($menu, $params);
    if (empty($tree)) {
      return [];
    }
    $root = reset($tree);
    if (empty($root->subtree)) {
      return [];
    }
    // Exclude the current link from the tree.
    $subtree = array_filter($root->subtree, function ($branch) use ($current_link) {
      return $branch->link->pluginDefinition['metadata']['entity_id'] !== $current_link->get('id')
          ->getValue()[0]['value'];
    });
    return $subtree;
  }

}
