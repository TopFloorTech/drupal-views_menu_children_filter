<?php

namespace Drupal\views_menu_children_filter;

use Drupal\Core\Menu\MenuLinkInterface;

/**
 * Provides helper methods for finding the preferred menu link for a route.
 *
 * @package Drupal\views_menu_children_filter
 */
class PreferredMenuLinkHelper {

  /**
   * Tries to determine the top menu link for a given route
   *
   * @param $route
   * @param array $routeParameters
   * @param null $menus
   * @return bool|mixed
   */
  public static function getPreferredMenuLink($route, $routeParameters = [], $menus = NULL) {
    $preferredLinks = &drupal_static(__FUNCTION__);

    if (!isset($route)) {
      $route = \Drupal::service('current_route_match')->getRouteName();
    }

    if (!isset($preferredLinks[$route])) {
      // Retrieve a list of menu names, ordered by preference.
      $menuLinks = \Drupal::service('plugin.manager.menu.link')->loadLinksByRoute($route, $routeParameters);

      $candidates = self::getCandidatesFromMenuLinks($menuLinks);

      if (!empty($candidates)) {
        foreach ($menus as $menuName) {
          if (empty($preferredLinks[$route][$menuName]) && isset($candidates[$route][$menuName])) {
            /** @var MenuLinkInterface $item */
            $item = $candidates[$route][$menuName];

            if (\Drupal::service('access_manager')->checkNamedRoute($item->getRouteName(), $item->getRouteParameters(), \Drupal::currentUser())) {
              $preferredLinks[$route][$menuName] = $item;
            }
          }
        }
      }
    }

    return self::getPreferredMenuLinkForMenu($preferredLinks, $route, $menus);
  }

  /**
   * @param MenuLinkInterface[] $menuLinks
   * @return array
   */
  protected static function getCandidatesFromMenuLinks($menuLinks) {
    $candidates = [];

    foreach ($menuLinks as $menuLink) {
      $candidates[$menuLink->getRouteName()][$menuLink->getMenuName()] = $menuLink;
    }

    return $candidates;
  }

  /**
   * Returns the first menu link for a route, or the menu link from the given menu if specified.
   *
   * @param $preferredLinks
   * @param $route
   * @param null $menus
   * @return bool|mixed
   * @internal param null $menu
   */
  public static function getPreferredMenuLinkForMenu($preferredLinks, $route, $menus = NULL) {
    if (is_null($menus)) {
      if (!empty($preferredLinks[$route])) {
        return array_shift($preferredLinks[$route]);
      }
    } else {
      foreach ((array) $menus as $menu) {
        if (isset($preferredLinks[$route][$menu])) {
          return $preferredLinks[$route][$menu];
        }
      }
    }

    return FALSE;
  }
}
