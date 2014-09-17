<?php

class Lavender_Exception extends Exception
{
  private $_line;
  private $_content;
  private $_message;

  public function __construct($content, $message = "", $line = NULL)
  {
    $this->_line    = $line;
    $this->_content = $content;
    parent::__construct($message);
  }

  public function get_file()
  {
    return $this->_content->get_full_content();
  }

  public function get_line()
  {
    return $this->_line ?: $this->_content->get_line();
  }
}
