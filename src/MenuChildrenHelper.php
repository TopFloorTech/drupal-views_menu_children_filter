<?php

namespace Drupal\views_menu_children_filter;

use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Url;

/**
 * Assists in finding children of a parent menu item based on input criteria.
 *
 * @package Drupal\views_menu_children_filter
 */
class MenuChildrenHelper {

  /**
   * Static cache for menu children node id's.
   */
  protected static $childEntityIds = [];

  /**
   * Returns an array of child entity IDs of the provided parent entity.
   *
   * @param string $parentArgument The argument, usually a path or a NID
   * @param array|NULL $menus The menus to search for children within
   * @return array The entity IDs of children of the specified parent
   */
  public static function getChildEntityIds($parentArgument, $menus = NULL) {
    $key = $parentArgument;
    if (is_array($menus)) {
      $key .= ':' . implode(':', $menus);
    }

    if (!isset(self::$childEntityIds[$key])) {
      $children = [];

      /** @var MenuLinkInterface $parent */
      $parent = self::getParent($parentArgument, $menus);

      if ($parent) {
        $parameters = (new MenuTreeParameters())
          ->setRoot($parent->getPluginId())
          ->setMaxDepth(1)
          ->excludeRoot();

        $children = \Drupal::menuTree()->load($parent->getMenuName(), $parameters);
      }

      self::$childEntityIds[$key] = self::getIds($children);
    }

    return self::$childEntityIds[$key];
  }

  /**
   * Gets the entity IDs of the provided child MenuLinkTreeElement entities
   *
   * @param MenuLinkTreeElement[] $children
   * @return integer[]
   */
  protected static function getIds($children) {
    $ids = [];

    if (!empty($children)) {
      /** @var MenuLinkTreeElement $menuLinkTreeElement */
      foreach ($children as $menuLinkTreeElement) {
        $childParameters = $menuLinkTreeElement->link->getRouteParameters();
        $childWeight = $menuLinkTreeElement->link->getWeight();

        if (!empty($childParameters['node'])) {
          $ids[$childWeight] = $childParameters['node'];
        }
      }
    }

    return $ids;
  }

  /**
   * Gets the parent menu item from the provided user input.
   *
   * @param string $parentArgument The argument, usually a path or a NID
   * @param array|NULL $menus The menus to search for the parent within
   * @return bool|mixed
   */
  protected static function getParent($parentArgument, $menus) {
    $parent = FALSE;

    $url = self::getParentUrl($parentArgument);

    if ($url->isRouted()) {
      $preferredMenuLinkHelper = new PreferredMenuLinkHelper();

      $parent = $preferredMenuLinkHelper->getPreferredMenuLink(
        $url->getRouteName(),
        $url->getRouteParameters(),
        $menus
      );
    }

    return $parent;
  }

  /**
   * Gets the Url of the parent entity based on the provided user input.
   *
   * @param $input
   * @return Url The Url object representing the parent entity
   */
  protected static function getParentUrl($input) {
    $parentPath = is_numeric($input) ? "node/$input" : $input;

    return Url::fromUserInput('/' . trim($parentPath, '/'));
  }
}
