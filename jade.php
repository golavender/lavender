<?php

require __DIR__ . '/lib/content.php';
require __DIR__ . '/lib/view.php';
require __DIR__ . '/lib/file.php';
require __DIR__ . '/lib/node.php';

require __DIR__ . '/extensions/text.php';
require __DIR__ . '/extensions/html.php';

class Jade
{
  private static $_extensions = array();
  private static $_config = array();

  public static function config(array $config)
  {
    static::$_config = array_merge(static::$_config, $config);
  }

  public static function get_config($key)
  {
    return static::$_config[$key];
  }

  public static function view($name)
  {
    return new Jade_View($name);
  }

  public static function register_extension($token, $extension)
  {
    static::$_extensions[$token] = $extension;
  }

  public static function get_extension($token)
  {
    if (isset(static::$_extensions[$token])) {
      $class = static::$_extensions[$token];
      return new $class();
    } else {
      return FALSE;
    }
  }
}
