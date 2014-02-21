<?php

require __DIR__ . '/content.php';
require __DIR__ . '/view.php';
require __DIR__ . '/file.php';
require __DIR__ . '/node.php';

require __DIR__ . '/extensions/text.php';
require __DIR__ . '/extensions/html.php';

class Jade
{
  private static $_extensions = array();

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
