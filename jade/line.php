<?php

class Jade_Line
{
  private $_raw;
  private $_level;
  private $_children = array();
  private $_parent;
  private $_node_regex = '/^([a-z0-9]+)(\.[^ \(]+)*(\(.+\))?( (.+))?$/i';
  private $_node_parts = array();

  public function __construct($raw_line)
  {
    $this->_raw = $raw_line;
    $this->_level = strlen($this->_raw) - strlen(ltrim($this->_raw));
    preg_match($this->_node_regex, trim($this->_raw), $this->_node_parts);
  }

  public function get_level()
  {
    return $this->_level;
  }

  public function get_parent()
  {
    return $this->_parent;
  }

  public function add_child($child)
  {
    array_push($this->_children, $child);
    $child->set_parent($this);

    return $this;
  }

  public function set_parent($parent)
  {
    $this->_parent = $parent;
  }

  public function compile($data)
  {
    switch ($this->get_type()) {
      case 'text':
        return ltrim($this->_raw, " \t|");
      case 'node':
        return $this->_compile_node();
    }
  }

  public function get_type()
  {
    $trimmed = trim($this->_raw);

    if (!$trimmed || $trimmed[0] == '|') {
      return 'text';
    } else {
      return 'node';
    }
  }

  private function _compile_node()
  {
    $name = $this->_node_parts[1];
    $classes = implode(' ', array_filter(explode('.', $this->_node_parts[2])));
    $attributes = explode(',', trim($this->_node_parts[3], '()'));

    $text = $this->_node_parts[5];

    if ($classes) {
      $attributes[] = 'class="'.$classes.'"';
    }

    $attributes = implode(' ', $attributes);

    $result = "<$name $attributes>$text";

    if (!$text) {
      foreach($this->_children as $line) {
        $result .= $line->compile();
      }
    }

    $result .= "</$name>";


    return $result;
  }
}
