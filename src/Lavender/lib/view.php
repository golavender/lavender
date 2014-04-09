<?php

class Lavender_View
{
  private $_name;
  private $_view_file;

  public function __construct($name)
  {
    $this->_name      = $name;
    $this->_view_file = new Lavender_File($name);
  }

  public function compile(array $scope = array())
  {
    try {
      return $this->_view_file->compile($scope);
    }
    catch (Exception $e) {
      $this->_handle_exception($e);
    }
  }

  private function _handle_exception($e)
  {
    if (Lavender::get_config('handle_errors') && $e instanceof Lavender_Exception) {
      $view_file = $this->_view_file->get_content()->get_full_content();

      die(
        Lavender::view(__DIR__.'/error_template.lavender')
          ->compile(array(
            'exception' => $e,
            'view_name' => $this->_name,
            'lines'     => explode("\n", $view_file),
          ))
      );
    }

    throw $e;
  }
}
