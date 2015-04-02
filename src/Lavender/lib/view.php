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
    if (!array_key_exists('global', $scope)) {
      $scope['global'] = array();
    }

    $this->set($scope);

    $that = $this;

    if (Lavender::get_config('handle_errors')) {
      $old_error_handler = set_error_handler(function($errno, $errstr, $errfile, $errline) use ($that) {
        $that->_handle_exception($errstr);
      });
    }

    try {
      $response = $this->_view_file->compile($this->_data);

      if (Lavender::get_config('handle_errors') && $old_error_handler) {
        set_error_handler($old_error_handler);
      }

      return $response;
    }
    catch (Exception $e) {
      $this->_handle_exception($e);
    }
  }

  public function _handle_exception($e = NULL)
  {
    if (Lavender::get_config('handle_errors')) {

      if ($e instanceof Lavender_Exception) {
        $file    = $e->get_file();
        $line    = $e->get_line();
      }
      else {
        $content = $this->_view_file->get_content();
        $file    = $content->get_full_content();
        $line    = $content->get_line();
      }

      if ($e instanceof Exception) {
        $message = $e->getMessage();
      }
      else if (is_string($e)) {
        $message = $e;
      }

      die(
        Lavender::view(__DIR__.'/error_template.lavender')
          ->compile(array(
            'exception'  => $e,
            'view_name'  => $this->_name,
            'error_line' => $line,
            'message'    => $message,
            'lines'      => explode("\n", $file),
          ))
      );
    }

    throw $e;
  }
}
