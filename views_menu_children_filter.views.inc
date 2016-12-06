<?php

/**
 * Implements hook_views_data_alter().
 */
function views_menu_children_filter_views_data_alter(array &$data) {
  $data['node']['menu_children_filter'] = [
    'title' => t('Menu children'),
    'help' => t('Gets one level of child nodes for argument Nid within the target menu.'),
    'argument' => [
      'id' => 'menu_children',
    ]
  ];
}
