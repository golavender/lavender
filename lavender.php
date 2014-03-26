<?php

require __DIR__ . '/lib/content.php';
require __DIR__ . '/lib/view.php';
require __DIR__ . '/lib/file.php';
require __DIR__ . '/lib/node.php';
require __DIR__ . '/lib/exception.php';

require __DIR__ . '/extensions/text.php';
require __DIR__ . '/extensions/html.php';
require __DIR__ . '/extensions/expression.php';
require __DIR__ . '/extensions/if.php';
require __DIR__ . '/extensions/each.php';
require __DIR__ . '/extensions/include.php';
require __DIR__ . '/extensions/extends.php';
require __DIR__ . '/extensions/block.php';
require __DIR__ . '/extensions/else.php';

class Lavender
{
  private static $_extensions = array();
  private static $_config = array(
    'file_extension' => 'lavender'
  );

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
    return new Lavender_View($name);
  }

  public static function register_extension($name, $class, array $tokens = array())
  {
    static::$_extensions[] = array(
      'name'   => $name,
      'class'  => $class,
      'tokens' => $tokens,
    );
  }

  public static function get_extension_by_name($name)
  {
    foreach (static::$_extensions as $extension) {
      if ($extension['name'] == $name) {
        return new $extension['class'];
      }
    }

    return FALSE;
  }

  public static function get_extension_by_token($token)
  {
    foreach (static::$_extensions as $extension) {

      if (in_array($token, $extension['tokens'])) {
        return new $extension['class'];
      }
    }

    return FALSE;
  }
}
