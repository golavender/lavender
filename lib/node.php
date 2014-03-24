<?php

abstract class Jade_Node
{
  private $_parent;
  private $_level;
  private $_children = array();

  public function set_level($level)
  {
    $this->_level = $level;
  }

  public function get_level()
  {
    return $this->_level;
  }

  public function set_parent($parent)
  {
    $this->_parent = $parent;
  }

  public function get_parent()
  {
    return $this->_parent;
  }

  public function add_child($child)
  {
    array_push($this->_children, $child);
    $child->set_parent($this);

    return $this;
  }

  public function set_children(array $children)
  {
    $this->_children = $children;
  }

  public function get_children()
  {
    return $this->_children;
  }

  public function compile(array $scope)
  {
    $result = '';

    foreach ($this->_children as $child) {
      $result .= $child->compile($scope);
    }

    return $result;
  }

  public abstract function tokenize_content(Jade_Content $content);
}
