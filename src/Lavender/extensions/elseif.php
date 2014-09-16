<?php

class Lavender_Extension_Elseif extends Lavender_Node
{
  private $_previous;
  private $_expression;

  protected $_delimiter = '';

  public function tokenize_content(Lavender_Content $content)
  {
    $content->consume_until(" "); // the 'elseif'

    $this->_expression = Lavender::get_extension_by_name('expression');
    $this->_expression->tokenize_content($content);

    $siblings = $this->get_parent()->get_children();

    if (count($siblings) < 2) {
      throw new Lavender_Exception($content, 'elseif node used with no previous conditional');
    }

    $before_me = $siblings[count($siblings)-2];

    if (!method_exists($before_me, 'is_truthy')) {
      throw new Lavender_Exception($content, 'elseif node used after something that has no boolean state');
    }

    $this->_previous = $before_me;
  }

  public function _compile(array &$scope)
  {
    if (!$this->_previous->is_truthy($scope) && $this->is_truthy($scope)) {
      return parent::_compile($scope);
    } else {
      return '';
    }
  }

  public function is_truthy(array $scope)
  {
    // if the parent is truthy, then mark shit as true all the way down
    if ($this->_previous->is_truthy($scope)) {
      return TRUE;
    }
    return $this->_expression->is_truthy($scope);
  }
}

Lavender::register_extension('elseif', 'Lavender_Extension_Elseif', array('elseif '));
