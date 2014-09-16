<?php

abstract class Lavender_Node
{
  private $_parent;
  private $_level;
  private $_children = array();

  // used for exception handling
  private $_content;
  private $_line;

  // set this to FALSE if your node doesn't output anything
  protected $_output = TRUE;
  protected $_delimiter = "\n";

  public $text_children_only = FALSE;

  public function set_delimiter($delimiter)
  {
    $this->_delimiter = $delimiter;
  }

  public function has_output()
  {
    return $this->_output;
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

  public function set_children(array $children)
  {
    $this->_children = $children;
  }

  public function get_children()
  {
    return $this->_children;
  }

  public function previous()
  {
    $siblings = $this->get_parent()->get_children();

    foreach ($siblings as $key => $sibling) {
      if ($sibling == $this && isset($siblings[$key-1])) {
        return $siblings[$key-1];
      }
    }

    return NULL;
  }

  public function next()
  {
    $siblings = $this->get_parent()->get_children();

    foreach ($siblings as $key => $sibling) {
      if ($sibling == $this && isset($siblings[$key+1])) {
        return $siblings[$key+1];
      }
    }

    return NULL;
  }

  public function compile(array &$scope)
  {
    $output = $this->_compile($scope);

    if ($this->_output) {

      if ($output && is_string($output) && $this->_delimiter) {
        $output .= $this->_delimiter;
      }

      return $output;
    }

    return NULL;
  }

  protected function _compile(array &$scope)
  {
    $result = '';

    foreach ($this->_children as $child) {
      $result .= $child->compile($scope);
    }

    return $result;
  }

  public function tokenize_content(Lavender_Content $content)
  {
    $this->_content = $content;
    $this->_line    = $content->get_line();
  }

  protected function _throw_exception($message)
  {
    throw new Lavender_Exception($this->_content, $message, $this->_line);
  }
}
