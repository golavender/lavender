<?php

class Lavender_Exception extends Exception
{
  private $_line;
  private $_message;

  public function __construct($content, $message = "")
  {
    $this->_line = $content->get_line();
    $this->_message = $message;
  }

  public function get_line()
  {
    return $this->_line;
  }

  public function get_message()
  {
    return $this->_message;
  }
}
