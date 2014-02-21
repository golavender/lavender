<?php

class Jade_File
{
  private $_contents;
  private $_children = array();

  private $_tokens = array();

  private function _consume_until($until)
  {
    $result = '';
    $until = str_split($until);

    while ($next = $this->_peek()) {
      if (in_array($next, $until)) {
        break;
      } else {
        $result .= $this->_consume_next();
      }
    }
    return $result;
  }

  private function _consume_next($length = 1)
  {
    $result = substr($this->_contents, 0, $length);
    $this->_contents = substr($this->_contents, $length);
    return $result;
  }

  private function _consume_whitespace()
  {
    $start_length = strlen($this->_contents);
    $this->_contents = ltrim($this->_contents, " \t");
    return $start_length - strlen($this->_contents);
  }

  private function _peek($length = 1)
  {
    if (!$this->_contents) {
      return FALSE;
    }
    return substr($this->_contents, 0, $length);
  }

  public function __construct($path)
  {
    $this->_contents = file_get_contents($path);
  }

  private function _tokenize($data)
  {
    $parent = $this;
    $level  = 0;

    while ($next = $this->_peek()) {

      switch ($next) {
        case "\n":
          $this->_consume_next(); // the '\n'
          $level = $this->_consume_whitespace();

          while ($level <= $parent->get_level()) {
            $parent = $parent->get_parent();
          }

          break;
        case "|":
          $this->_consume_next(); // the '|'
          # the rest of the line should just be text
          $text = $this->_consume_until("\n");
          $text = new Jade_Text(ltrim($text));
          $parent->add_child($text);
          break;
        default:
          $node = $this->_build_node();
          $node->set_level($level);
          $parent->add_child($node);
          $parent = $node;
      }
    }
  }

  public function compile()
  {
    $this->_tokenize();
    $result = '';


    foreach ($this->_children as $child) {
      $result .= $child->compile();
    }

    return $result;
  }

  private function _build_node()
  {
    $special_characters = " .(#\n";

    $node_name = $this->_consume_until($special_characters);
    $node = new Jade_Node($node_name);

    while ($next = $this->_peek()) {

      switch ($next) {
        case '.':
          $this->_consume_next(); // the '.'
          $class = $this->_consume_until($special_characters);
          $node->set_class($class);
          break;
        case '#':
          $this->_consume_next(); // the '#'
          $id = $this->_consume_until($special_characters);
          $node->set_attribute('id = "'.$id.'"');
          break;
        case '(':
          $this->_consume_next(); // the '('
          $raw = $this->_consume_until(")");
          $this->_consume_next(); // the ')'
          $attributes = explode(',', $raw);
          $node->set_attributes($attributes);
          break;
        case " ":
        case "\t":
          # the rest of the line should just be text
          $text = $this->_consume_until("\n");
          $text = new Jade_Text(ltrim($text));
          $node->add_child($text);
          break;
        case "\n":
          return $node;
        default:
          throw new Exception("asdf");
      }
    }
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
}
