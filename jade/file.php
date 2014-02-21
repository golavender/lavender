<?php

class Jade_File
{
  private $_content;
  private $_children = array();

  private $_tokens = array();

  public function __construct($path)
  {
    $this->_content = new Jade_Content(file_get_contents($path));
  }

  private function _tokenize()
  {
    $parent = $this;
    $level  = 0;

    while ($next = $this->_content->peek()) {

      if ($next == "\n") {
        $this->_content->consume_next(); // the '\n'
        $level = $this->_content->consume_whitespace();

        while ($level <= $parent->get_level()) {
          $parent = $parent->get_parent();
        }
      } else {
        $token = $this->_content->peek_until(" \n");
        $node = Jade::get_extension($token) ?: Jade::get_extension('html');

        if (!$node) {
          throw new Exception("Unknown expression: \"$token\"");
        }

        $node->tokenize_content($this->_content);
        $node->set_level($level);

        $parent->add_child($node);
        $parent = $node;
      }
    }
  }

  public function compile()
  {
    $this->_tokenize();
    $result = '';

    foreach ($this->_children as $child) {
      $result .= $child->compile();
    }

    return $result;
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
}
