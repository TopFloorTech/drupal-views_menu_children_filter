<?php

namespace Drupal\views_menu_children_filter\Plugin\views\sort;

use Drupal\views\Plugin\views\sort\SortPluginBase;
use Drupal\views_menu_children_filter\Plugin\views\join\MenuChildrenNodeJoin;

/**
 * Default implementation of the base sort plugin.
 *
 * @ingroup views_sort_handlers
 *
 * @ViewsSort("menu_children")
 */
class MenuChildren extends SortPluginBase {

  public function query() {

    MenuChildrenNodeJoin::joinMenuLinksTableToNode($this->query);
    $tables = $this->query->tables['node_field_data'];

    $this->query->addOrderBy($tables['menu_link_content_data']['alias'], 'weight', $this->options['order']);
    $this->query->addOrderBy($tables['node_field_data']['alias'], 'title', $this->options['order']);
    $this->query->addOrderBy($tables['menu_link_content_data']['alias'], 'id', $this->options['order']);
  }
}
