<?php

namespace Drupal\views_menu_children_filter\Plugin\views\argument;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Annotation\ViewsArgument;
use Drupal\views\Plugin\views\argument\NumericArgument;
use Drupal\views_menu_children_filter\MenuChildrenHelper;
use Drupal\views_menu_children_filter\MenuOptionsHelper;

/**
 * A filter to show menu children of a parent menu item
 *
 * @ingroup views_argument_handlers
 *
 * @ViewsArgument("menu_children")
 */
class MenuChildren extends NumericArgument {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['target_menus'] = ['default' => []];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    unset($form['not']);
    unset($form['break_phrase']);

    $form['target_menus'] = MenuOptionsHelper::getSelectField($this->options['target_menus']);
  }

  /**
   * {@inheritdoc}
   */
  public function query($group_by = FALSE) {
    $targetId = reset($this->value);

    if (!empty($targetId)) {
      if (count($this->value) >= 1) {
        $children = MenuChildrenHelper::getChildEntityIds($targetId, $this->options['target_menus']);

        $placeholder = $this->queryPlaceholder($children);
        $idField = empty($this->tableAlias) ? 'nid' : "$this->tableAlias.nid";

        $this->query->addWhereExpression(0,
          "$idField IN ($placeholder)",
          array($placeholder => !empty($children) ? $children : '')
        );
      }
    }
  }

  /**
   * Returns an array placeholder if there are children, and a standard
   * placeholder if there are no children.
   *
   * @param array $children
   * @return string
   */
  protected function queryPlaceholder(array $children) {
    $placeholder = $this->placeholder();

    if (!empty($children)) {
      $placeholder .= '[]';
    }

    return $placeholder;
  }
}
