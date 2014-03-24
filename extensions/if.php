<?php

class Lavender_Extension_If extends Lavender_Node
{
  private $_expression;

  public function tokenize_content(Lavender_Content $content)
  {
    $content->consume_until(" "); // the 'if'

    $this->_expression = Lavender::get_extension_by_name('expression');
    $this->_expression->tokenize_content($content);
  }

  public function compile(array $scope)
  {
    if ($this->_expression->is_truthy($scope)) {
      return parent::compile($scope);
    } else {
      return '';
    }
  }
}

Lavender::register_extension('if', 'Lavender_Extension_If', array('if'));
