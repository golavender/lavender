<?php

class Jade_View
{
  private $_view_dir = '../../views/';
  private $_view_file;

  public function __construct($name)
  {
    $this->_view_file = new Jade_File(__DIR__ . '/' . $this->_view_dir . $name);
  }

  public function compile($data = array())
  {
    return $this->_view_file->compile();
  }

}
