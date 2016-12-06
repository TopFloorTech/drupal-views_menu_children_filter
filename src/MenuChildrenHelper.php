<?php

namespace Drupal\views_menu_children_filter;

use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Url;

class MenuChildrenHelper {
  /**
   * Returns an array of child entity IDs of the provided parent entity
   *
   * @param $parentArgument
   * @param null $menus
   * @return array
   */
  public static function getChildEntityIds($parentArgument, $menus = NULL) {
    $ids = [];
    $parentPath = is_numeric($parentArgument) ? "node/$parentArgument" : $parentArgument;
    $url = Url::fromUserInput('/' . trim($parentPath, '/'));

    if ($url->isRouted()) {
      $preferredMenuLinkHelper = new PreferredMenuLinkHelper();
      $parent = $preferredMenuLinkHelper->getPreferredMenuLink($url->getRouteName(), $url->getRouteParameters(), $menus);

      if ($parent) {
        /** @var MenuLinkInterface $parent */

        $parameters = new MenuTreeParameters();
        $parameters
          ->setRoot($parent->getPluginId())
          ->setMaxDepth(1)
          ->excludeRoot();

        $children = \Drupal::menuTree()->load($parent->getMenuName(), $parameters);

        if (!empty($children)) {
          /** @var MenuLinkTreeElement $menuLinkTreeElement */
          foreach ($children as $menuLinkTreeElement) {
            $menuLink = $menuLinkTreeElement->link;
            $childParameters = $menuLink->getRouteParameters();

            if (isset($childParameters['node'])) {
              $ids[] = $childParameters['node'];
            }
          }
        }
      }
    }

    return $ids;
  }
}
