<?php

class Jade_View
{
  private $_view_file;

  public function __construct($name)
  {
    $this->_view_file = new Jade_File(Jade::get_config('view_dir') . '/' . $name);
  }

  public function compile($scope = array())
  {
    return $this->_view_file->compile($scope);
  }

}
