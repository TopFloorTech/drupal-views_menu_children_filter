<?php

namespace Drupal\views_menu_children_filter\Plugin\views\sort;

use Drupal\views\Plugin\views\sort\SortPluginBase;
use Drupal\views_menu_children_filter\MenuChildrenHelper;

/**
 * Default implementation of the base sort plugin.
 *
 * @ingroup views_sort_handlers
 *
 * @ViewsSort("menu_children")
 */
class MenuChildren extends SortPluginBase {

  public function query() {
    // Do nothing if this view doesn't use the menu_children_filter argument.
    if (!empty($this->view->argument['menu_children_filter'])) {
      $argument = $this->view->argument['menu_children_filter'];

      // Sanity check, although it would be even nicer if we could check against
      // an interface instead of a specific class.
      if (!$argument instanceof \Drupal\views_menu_children_filter\Plugin\views\argument\MenuChildren) {
        return;
      }

      $targetId = reset($argument->value);
      $children = MenuChildrenHelper::getChildEntityWeights($targetId, $argument->options['target_menus']);

      // Build an arbitrary sort order statement that is compatible with MySQL,
      // PostgreSQL and SQLite.
      $idField = empty($argument->tableAlias) ? 'nid' : "$argument->tableAlias.nid";

      $orderBy = [];

      foreach ($children as $childId => $childWeight) {
        // Sanitize the id and weight values by casting to int, just to be sure.
        // We cannot use query placeholders here, because the integers would get
        // quoted, turn into strings, and break the order by clause when there
        // are negative weights.
        $orderBy[] = 'WHEN ' . (int)$childId . ' THEN ' . (int)$childWeight . ' ';
      }

      if (!empty($orderBy)) {
        $orderByCase = "CASE $idField " . implode(' ', $orderBy) . ' END';

        $this->query->addField(NULL, $orderByCase, 'menu_children_order');
        $this->query->addOrderBy(NULL, NULL, $this->options['order'], 'menu_children_order');
      }

    }
  }

}
