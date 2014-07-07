<?php

class Lavender_View
{
  private $_name;
  private $_view_file;
  private $_data = array();

  public function __construct($name = NULL)
  {
    if ($name) {
      $this->set_view($name);
    }
  }

  public function set_view($name)
  {
    $this->_name      = $name;
    $this->_view_file = new Lavender_File($name);

    return $this;
  }

  public function set($key, $value = NULL)
  {
    if (is_array($key)) {
      $this->_data = array_merge($this->_data, $key);
    }
    elseif($key) {
      $this->_data[$key] = $value;
    }

    return $this;
  }

  public function get($key = NULL)
  {
    if ($key && isset($this->_data[$key])) {
      return $this->_data[$key];
    }
    else if (!$key) {
      return $this->_data;
    }
    else {
      return NULL;
    }
  }

  public function compile(array $scope = array())
  {
    $this->set($scope);

    try {
      return $this->_view_file->compile($this->_data);
    }
    catch (Exception $e) {
      $this->_handle_exception($e);
    }
  }

  private function _handle_exception($e)
  {
    if (Lavender::get_config('handle_errors') && $e instanceof Lavender_Exception) {
      $view_file = $e->get_file();

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
