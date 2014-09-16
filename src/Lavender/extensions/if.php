<?php

class Lavender_Extension_If extends Lavender_Node
{
  private $_expression;
  private $_is_truthy = NULL;

  protected $_delimiter = '';

  public function tokenize_content(Lavender_Content $content)
  {
    $content->consume_until(" "); // the 'if'

    $this->_expression = Lavender::get_extension_by_name('expression');
    $this->_expression->tokenize_content($content);
  }

  public function _compile(array &$scope)
  {
    if ($this->is_truthy($scope)) {
      return parent::_compile($scope);
    }
    else {
      return '';
    }
  }

  public function is_truthy(array $scope)
  {
    return $this->_expression->is_truthy($scope);
  }
}

Lavender::register_extension('if', 'Lavender_Extension_If', array('if '));
