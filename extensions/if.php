<?php

class Lavender_Extension_If extends Lavender_Node
{
  private $_expression;
  private $_is_truthy = NULL;

  public function tokenize_content(Lavender_Content $content)
  {
    $content->consume_until(" "); // the 'if'

    $this->_expression = Lavender::get_extension_by_name('expression');
    $this->_expression->tokenize_content($content);
  }

  public function compile(array $scope)
  {
    if ($this->is_truthy($scope)) {
      return parent::compile($scope);
    }
    else {
      return '';
    }
  }

  public function is_truthy(array $scope)
  {
    if ($this->_is_truthy == NULL) {
      return $this->_is_truthy = $this->_expression->is_truthy($scope);
    }
    else {
      return $this->_is_truthy;
    }
  }
}

Lavender::register_extension('if', 'Lavender_Extension_If', array('if'));
