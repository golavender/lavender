<?php

class Jade_Node
{
  private $_name;
  private $_level;
  private $_classes = array();
  private $_attributes = array();

  private $_parent;
  private $_children = array();

  public function __construct($name)
  {
    $this->_name = $name;
  }

  public function set_class($class)
  {
    array_push($this->_classes, $class);
  }

  public function set_attribute($attribute)
  {
    array_push($this->_attributes, $attribute);
  }

  public function set_classes(array $classes)
  {
    array_map($this->_set_class, $classes);
  }

  public function set_attributes(array $attributes)
  {
    foreach ($attributes as $attribute) {
      $this->set_attribute(trim($attribute));
    }
  }

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

  public function compile()
  {
    $attributes = $this->_attributes;

    if ($this->_classes) {
      $attributes[] = 'class="' . implode(' ', $this->_classes) . '"';
    }

    if ($attributes) {
      $attributes = ' ' . implode(' ', $attributes);
    } else {
      $attributes = '';
    }

    $result = "<{$this->_name}{$attributes}>";

    foreach ($this->_children as $child) {
      $result .= $child->compile();
    }

    $result .= "</{$this->_name}>";

    return $result;
  }
}
