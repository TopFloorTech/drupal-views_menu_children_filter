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
    $placeholder = $this->placeholder();
    $nidField = empty($this->tableAlias) ? 'nid' : "$this->tableAlias.nid";
    $realField = empty($this->tableAlias) ? $this->realField : "$this->tableAlias.$this->realField";
    $nullCheck = empty($this->options['not']) ? '' : "OR $realField IS NULL";

    if (!empty($targetId)) {
      if (count($this->value) >= 1) {
        $operator = empty($this->options['not']) ? 'IN' : 'NOT IN';

        $children = MenuChildrenHelper::getChildEntityIds($targetId, $this->options['target_menus']);

        if (!empty($children)) {
          $placeholder .= '[]';
        }

        $this->query->addWhereExpression(
          0,
          "$nidField $operator($placeholder) $nullCheck",
          array($placeholder => !empty($children) ? $children : '')
        );
      }
    }
  }
}
