<?php

class Lavender_Extension_Include extends Lavender_Node
{
  private $_path;
  private $_expression;

  protected $_delimiter = '';

  public function tokenize_content(Lavender_Content $content)
  {
    $content->consume_until(" "); // the 'include'
    $content->consume_whitespace();

    $path = $content->consume_until(" \n");

    $content->consume_whitespace();

    if ($content->peek(4) == "with") {
      $content->consume_until("{\n");

      if ($content->peek() == "\n") {
        throw new Lavender_Exception($content, 'expected "{" in include');
      }
      $this->_expression = Lavender::get_extension_by_name('expression');
      $this->_expression->tokenize_content($content);
    }

    $this->_path = $path;
  }

  public function _compile(array &$scope)
  {
    if ($this->_expression) {
      $stuff = $this->_expression->compile($scope);
    }
    else {
      $stuff = array();
    }

    if (!is_array($stuff)) {
      throw new Exception('Invalid argument to include');
    }

    $subview = new Lavender_View($this->_path);

    $result = $subview->compile(array_merge($scope, $stuff));

    $scope['global'] = $subview->get('global');

    return $result;
  }

  public function add_child($child)
  {
    throw new Exception('include cannot have children');
  }
}

Lavender::register_extension('include', 'Lavender_Extension_Include', array('include '));
