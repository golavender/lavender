<?php

class Lavender_View
{
  private $_name;
  private $_view_file;

  public function __construct($name)
  {
    $this->_name      = $name;
    $this->_view_file = new Lavender_File(Lavender::get_config('view_dir') . '/' . $name);
  }

  public function compile($scope = array())
  {
    try {
      return $this->_view_file->compile($scope);
    }
    catch (Lavender_Exception $e) {
      die("{$e->get_message()}. {$this->_name} at line: {$e->get_line()}");
    }
    catch (Exception $e) {
      die("error in {$this->_name} at line: {$this->_view_file->get_content()->get_line()}");
    }
  }

}
