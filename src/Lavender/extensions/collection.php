<?php

class Lavender_Extension_Collection extends Lavender_Node
{
  protected $_output           = FALSE;
  private static $_collections = array();

  public static function get($key)
  {
    if (isset(static::$_collections[$key])) {
      return static::$_collections[$key];
    }

    return array();
  }

  public function tokenize_content(Lavender_Content $content)
  {
    $content->consume_until(" \n"); // the 'collect'
    $content->consume_whitespace();

    $key = $content->consume_until(" \n");
    $content->consume_whitespace();

    $value = $content->consume_until("\n");
    $content->consume_whitespace();

    if ($key && $value) {
      static::$_collections[$key][] = $value;
    }
  }
}

Lavender::register_extension('collection', 'Lavender_Extension_Collection', array('collect'));
