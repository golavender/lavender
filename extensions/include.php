<?php

class Jade_Extension_Include extends Jade_Node
{
  private $_subview;

  public function tokenize_content(Jade_Content $content)
  {
    $content->consume_until(" "); // the 'include'

    $path = trim($content->consume_until("\n"));

    $this->_subview = new Jade_View($path);
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

Jade::register_extension('include', 'Jade_Extension_Include', array('include'));
