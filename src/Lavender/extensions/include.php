<?php

class Lavender_Extension_Include extends Lavender_Node
{
  private $_subview;

  public function tokenize_content(Lavender_Content $content)
  {
    $content->consume_until(" "); // the 'include'

    $path = trim($content->consume_until("\n"));

    $this->_subview = new Lavender_View($path);
  }

  public function compile(array $scope)
  {
    return $this->_subview->compile($scope);
  }

  public function add_child($child)
  {
    throw new Exception('include cannot have children');
  }
}

Lavender::register_extension('include', 'Lavender_Extension_Include', array('include'));
