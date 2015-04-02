<?php

foreach (glob(__DIR__."/lib/*.php") as $filename) {
  require $filename;
}
foreach (glob(__DIR__."/extensions/*.php") as $filename) {
  require $filename;
}
foreach (glob(__DIR__."/filters/*.php") as $filename) {
  require $filename;
}
foreach (glob(__DIR__."/helpers/*.php") as $filename) {
  require $filename;
}

class Lavender
{
  private static $_extensions       = array();
  private static $_extension_tokens = array();

  private static $_config = array(
    'caching'        => FALSE,
    'cache_dir'      => NULL,
    'file_extension' => 'lavender',
    'handle_errors'  => TRUE,
  );
  private static $_filter_config = array(
  );

  public static function config(array $config)
  {
    static::$_config = array_merge(static::$_config, $config);
  }

  public static function get_config($key)
  {
    if ($key == 'handle_errors' && isset($_GET['disable_lavender_errors'])) {
      return FALSE;
    }

    return static::$_config[$key];
  }

  public static function filter_config($filter, $config)
  {
    static::$_filter_config[$filter] = $config;
  }

  public static function get_filter_config($filter, $key)
  {
    return static::$_filter_config[$filter][$key];
  }

  public static function view($name = NULL)
  {
    return new Lavender_View($name);
  }

  public static function register_helper($name, $class)
  {
    static::register_extension('helper|'.$name, $class);
  }

  public static function get_helper_by_name($name)
  {
    return static::get_extension_by_name('helper|'.$name);
  }

  public static function register_filter($name, $class, $config = array())
  {
    static::register_extension('filter|'.$name, $class);
    static::filter_config($name, $config);
  }

  public static function get_filter_by_name($name)
  {
    return static::get_extension_by_name('filter|'.$name);
  }

  public static function register_extension($name, $class, array $tokens = array())
  {
    static::$_extensions[$name] = $class;

    foreach ($tokens as $token) {
      static::$_extension_tokens[$token] = $name;
    }
  }

  public static function get_extension_by_name($name)
  {
    if (isset(static::$_extensions[$name])) {
      return new static::$_extensions[$name];
    }

    return FALSE;
  }

  public static function get_extension_by_token($token)
  {
    if (isset(static::$_extension_tokens[$token])) {
      return static::get_extension_by_name(static::$_extension_tokens[$token]);
    }

    return FALSE;
  }
}
