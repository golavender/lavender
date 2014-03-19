<?php

class Jade_Exception extends Exception
{
  private $_line;

  public function __construct($content)
  {
    $this->_line = $content->get_line();
  }

  public function get_line()
  {
    return $this->_line;
  }
}
