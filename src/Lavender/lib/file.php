<?php

class Lavender_File
{
  private $_content;
  private $_children = array();

  private $_tokens = array();
  private $_post_tokenize_hooks = array();

  public function __construct($name)
  {
    if ($name[0] == '/') {
      $path = $name;
    }
    else {
      $path = Lavender::get_config('view_dir') . '/' . $name . '.' . Lavender::get_config('file_extension');
    }
    $this->_content = new Lavender_Content(file_get_contents($path));
  }

  private function _tokenize()
  {
    $parent = $this;
    $level  = 0;

    while ($next = $this->_content->peek()) {

      if ($next == "\n") {

        while($this->_content->peek() == "\n") {
          $this->_content->consume_next(); // the '\n'
          $level = $this->_content->consume_whitespace();
        }

        while ($level <= $parent->get_level()) {
          $parent = $parent->get_parent();
        }
      } else {
        $token = trim($this->_content->peek_until(" \n"));

        // just ignore extra whitespace that is not newline
        if (!$token) {
          continue;
        }

        if ($parent->text_children_only) {
          $node = Lavender::get_extension_by_name('text');
        }
        else {
          $node = Lavender::get_extension_by_token($token) ?: Lavender::get_extension_by_name('html');
        }

        if (!$node) {
          throw new Lavender_Exception($this->_content);
        }

        $node->set_level($level);
        $parent->add_child($node);
        $node->tokenize_content($this->_content);

        $parent = $node;
      }
    }

    $this->_execute_post_tokenize_hooks();
  }

  private function _execute_post_tokenize_hooks()
  {
    foreach ($this->_post_tokenize_hooks as $hook) {
      call_user_func($hook, $this);
    }
  }

  public function post_tokenize_hook($hook)
  {
    $this->_post_tokenize_hooks[] = $hook;
  }

  public function compile(array &$scope)
  {
    $this->_tokenize();
    $result = '';

    foreach ($this->_children as $child) {
      $result .= $child->compile($scope);
    }

    return $result;
  }

  public function get_content()
  {
    return $this->_content;
  }

  public function get_level()
  {
    return -1;
  }

  public function add_child($child)
  {
    array_push($this->_children, $child);
    $child->set_parent($this);

    return $this;
  }

  public function get_children()
  {
    return $this->_children;
  }
}
