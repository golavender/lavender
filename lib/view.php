<?php

class Jade_View
{
  private $_name;
  private $_view_file;

  public function __construct($name)
  {
    $this->_name      = $name;
    $this->_view_file = new Jade_File(Jade::get_config('view_dir') . '/' . $name);
  }

  public function compile($scope = array())
  {
    try {
      return $this->_view_file->compile($scope);

    } catch (Jade_Exception $e) {

      die("error in {$this->_name} at line: {$e->get_line()}");

    } catch (Exception $e) {

      die("error in {$this->_name} at line: {$this->_view_file->get_content()->get_line()}");
    }
  }

}