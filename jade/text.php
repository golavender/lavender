<?php

class Jade_Text
{
  private $_parent;
  private $_level;
  private $_text;

  public function __construct($text)
  {
    $this->_text = $text;
  }

  public function set_level($level)
  {
    $this->_level = $level;
  }

  public function get_level()
  {
    return $level;
  }

  public function set_parent($parent)
  {
    $this->_parent = $parent;
  }

  public function get_parent()
  {
    return $this->_parent;
  }

  public function compile()
  {
    return $this->_text;
  }
}
