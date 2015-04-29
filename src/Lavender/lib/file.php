<?php

class Lavender_File
{
  private $_content;
  private $_name;
  private $_children = array();

  private $_tokens = array();
  private $_post_tokenize_hooks = array();

  public  $text_children_only = FALSE;

  public function __construct($name)
  {
    if ($name[0] == '/') {
      $path = $name;
    }
    else {
      $path = Lavender::get_config('view_dir') . '/' . $name . '.' . Lavender::get_config('file_extension');
    }
    $this->_name = $name;
    $this->_content = new Lavender_Content(file_get_contents($path));
  }

  private function _get_cache_file()
  {
    $path = [];

    if (!$cache_dir = Lavender::get_config('cache_dir')) {
      $cache_dir = sys_get_temp_dir();
      $path[] = 'lavender';
    }

    $path       = array_merge($path, explode('/', $this->_name));
    $file_name  = array_pop($path);

    foreach ($path as $part) {
      $cache_dir .= DIRECTORY_SEPARATOR . $part;

      if(!is_dir($cache_dir)) {
        mkdir($cache_dir);
      }
    }

    return $cache_dir . DIRECTORY_SEPARATOR . $file_name . '.lavenderc';
  }

  private function _save_to_cache()
  {
    if (!Lavender::get_config('caching')) {
      return;
    }

    file_put_contents(
      $this->_get_cache_file(),
      serialize($this->_children)
    );
  }

  private function _load_from_cache()
  {
    if (!Lavender::get_config('caching')) {
      return FALSE;
    }

    $cache_file = $this->_get_cache_file();

    if (is_file($cache_file)) {
      $this->_children = unserialize(file_get_contents($cache_file));
      return TRUE;
    }

    return FALSE;
  }

  private function _tokenize()
  {
    $parent = $this;
    $level  = 0;

    while ($next = $this->_content->peek()) {

      if ($next == "\n") {

        while($this->_content->peek() == "\n") {
          $this->_content->consume_next(); // the '\n'
          $level = $this->_content->consume_whitespace();
        }

        while ($level <= $parent->get_level()) {
          $parent = $parent->get_parent();
        }
      } else {

        // just ignore extra whitespace that is not newline
        $this->_content->consume_whitespace();
        if ($this->_content->peek() == "\n") {
          continue;
        }

        if ($parent->text_children_only) {
          $node = Lavender::get_extension_by_name('text');
        }
        else {

          $token = '';
          $node  = NULL;

          while(
            $this->_content->peek(strlen($token)+1, strlen($token)) &&
            $this->_content->peek(1, strlen($token)) !== "\n"
          ) {
            $token = $this->_content->peek(strlen($token)+1);
            $node = Lavender::get_extension_by_token($token) ?: $node;
          }

          $node = $node ?: Lavender::get_extension_by_name('html');
        }

        // something seriously wrong
        if (!$node) {
          throw new Lavender_Exception($this->_content, "something is seriously wrong. maybe the html extensions isn't loaded?");
        }

        $node->set_level($level);
        $parent->add_child($node);
        $node->tokenize_content($this->_content);

        if ($parent->text_children_only) {
          $node->set_delimiter("\n");
        }
        else {
          $parent = $node;
        }
      }
    }

    $this->_execute_post_tokenize_hooks();
  }

  private function _execute_post_tokenize_hooks()
  {
    foreach ($this->_post_tokenize_hooks as $hook) {
      call_user_func($hook, $this);
    }
  }

  public function post_tokenize_hook($hook)
  {
    $this->_post_tokenize_hooks[] = $hook;
  }

  public function compile(array &$scope)
  {
    if (!$this->_load_from_cache()) {
      $this->_tokenize();
      $this->_save_to_cache();
    }

    $result = '';

    foreach ($this->_children as $child) {
      $result .= $child->compile($scope);
    }

    return $result;
  }

  public function get_content()
  {
    return $this->_content;
  }

  public function get_level()
  {
    return -1;
  }

  public function add_child($child)
  {
    array_push($this->_children, $child);
    $child->set_parent($this);

    return $this;
  }

  public function remove_child_at($index)
  {
    unset($this->_children[$index]);
  }

  public function get_children()
  {
    return $this->_children;
  }
}
