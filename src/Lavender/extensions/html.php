<?php

class Lavender_Extension_Html extends Lavender_Node
{
  private $_name;
  private $_classes = array();
  private $_attributes = array();
  private static $_self_closing_tags = array(
    'area',
    'base',
    'br',
    'col',
    'embed',
    'hr',
    'img',
    'input',
    'keygen',
    'link',
    'menuitem',
    'meta',
    'param',
    'source',
    'track',
    'wbr'
  );

  public function set_class($class)
  {
    array_push($this->_classes, $class);
  }

  public function set_attribute($name, $value)
  {
    $this->_attributes[$name] = $value;
  }

  public function set_classes(array $classes)
  {
    array_map($this->_set_class, $classes);
  }

  public function tokenize_content(Lavender_Content $content)
  {
    $special_characters = " .(#=\n";

    $this->_name = $content->consume_until($special_characters.'-') ?: 'div';

    while ($next = $content->peek()) {

      switch ($next) {
        case '.':
          $content->consume_next(); // the '.'

          if (trim($content->peek())) {
            $class = $content->consume_until($special_characters);
            $this->set_class($class);
          } else {
            $this->text_children_only = TRUE;
            return;
          }

          break;
        case '#':
          $content->consume_next(); // the '#'
          $id = $content->consume_until($special_characters);
          $this->set_attribute('id', $id);
          break;
        case '-':
          $content->consume_next(); // the '-'
          $this->_delimiter = '';
          break;
        case '(':
          $content->consume_next(); // the '('

          while ($content->peek() != '' && $content->peek() != ')') {
            $content->consume_regex("/[ \t\n]/i");

            $name = $content->consume_regex("/[a-z0-9\-]/i");
            $content->consume_whitespace();
            if ($content->peek() != '=') {
              throw new Lavender_Exception($content, 'expected "=" in attribute expression');
            }
            $content->consume_next(); // the '='
            $content->consume_whitespace();

            $expression = Lavender::get_extension_by_name('expression');
            $expression->tokenize_content($content);

            $this->set_attribute($name, $expression);

            $content->consume_whitespace();

            if ($content->peek() == ',') {
              $content->consume_next(); // the ','
            }
            $content->consume_regex("/[ \t\n]/i");
          }

          $content->consume_next(); // the ')'

          break;
        case '=':
          # the rest of the line is an expression
          $expression = Lavender::get_extension_by_name('expression');
          $expression->tokenize_content($content);
          $this->add_child($expression);
          break;
        case " ":
        case "\t":
          $content->consume_whitespace();
          # the rest of the line should just be text
          $text_node = Lavender::get_extension_by_name('text');
          $text_node->tokenize_content($content);
          $this->add_child($text_node);
          break;
        case "\n":
          return;
        default:
          throw new Lavender_Exception($content);
      }
    }
  }

  public function _compile(array &$scope)
  {
    if ($this->_classes) {
      $this->set_attribute('class', implode(' ', $this->_classes));
    }

    $attributes = '';

    foreach ($this->_attributes as $name => $value) {
      if (gettype($value) == gettype(Lavender::get_extension_by_name('expression'))) {
        $value = $value->compile($scope);
      }

      if ($value !== FALSE && $value !== NULL) {
        $attributes .= " $name";

        if ($value !== TRUE) {
          $value = htmlentities($value);
          $attributes .= "=\"$value\"";
        }
      }
    }

    $result = "<{$this->_name}{$attributes}>";

    if (!in_array($this->_name, static::$_self_closing_tags)) {
      $result .= rtrim($this->_compile_children($scope), ' ');
      $result .= "</{$this->_name}>";
    }

    return $result;
  }

  private function _compile_children(&$scope)
  {
    $result = '';

    $base_level = NULL;
    foreach ($this->get_children() as $child) {
      if ($child->get_level() < $base_level || $base_level === NULL) {
        $base_level = $child->get_level();
      }
    }

    foreach ($this->get_children() as $child) {
      $prefix = '';

      if ($this->text_children_only && $child->get_level()) {
        $prefix = str_repeat(' ', $child->get_level() - $base_level);
      }

      $result .= $prefix . $child->compile($scope);
    }

    return $result;
  }
}

Lavender::register_extension('html', 'Lavender_Extension_Html');
