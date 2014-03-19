<?php

class Jade_Extension_Html extends Jade_Node
{
  private $_name;
  private $_classes = array();
  private $_attributes = array();

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

  public function tokenize_content(Jade_Content $content)
  {
    $special_characters = " .(#\n";

    $this->_name = $content->consume_until($special_characters) ?: 'div';

    while ($next = $content->peek()) {

      switch ($next) {
        case '.':
          $content->consume_next(); // the '.'
          $class = $content->consume_until($special_characters);
          $this->set_class($class);
          break;
        case '#':
          $content->consume_next(); // the '#'
          $id = $content->consume_until($special_characters);
          $this->set_attribute('id', $id);
          break;
        case '(':
          $content->consume_next(); // the '('
          $content->consume_whitespace();

          while ($content->peek() != '' && $content->peek() != ')') {
            $name = $content->consume_regex("/[a-z\-]/i");
            $content->consume_whitespace();
            if ($content->peek() != '=') {
              throw new Jade_Exception($content, 'expected "=" in attribute expression');
            }
            $content->consume_next(); // the '='
            $content->consume_whitespace();

            $expression = Jade::get_extension_by_name('expression');
            $expression->tokenize_content($content);

            $this->set_attribute($name, $expression);

            $content->consume_whitespace();

            if ($content->peek() == ',') {
              $content->consume_next(); // the ','
              $content->consume_whitespace();
            }
          }

          $content->consume_next(); // the ')'

          break;
        case '=':
          # the rest of the line is an expression
          $expression = Jade::get_extension_by_name('expression');
          $expression->tokenize_content($content);
          $this->add_child($expression);
          break;
        case " ":
        case "\t":
          # the rest of the line should just be text
          $text = $content->consume_until("\n");
          $text_node = Jade::get_extension_by_name('text');
          $text_node->set_text($text);
          $this->add_child($text_node);
          break;
        case "\n":
          return;
        default:
          throw new Jade_Exception($content);
      }
    }
  }

  public function compile(array $scope)
  {
    if ($this->_classes) {
      $this->set_attribute('class', implode(' ', $this->_classes));
    }

    $attributes = '';

    foreach ($this->_attributes as $name => $value) {
      if (gettype($value) == gettype(Jade::get_extension_by_name('expression'))) {
        $value = $value->compile($scope);
      }

      $attributes .= " $name=\"$value\"";
    }

    $result = "<{$this->_name}{$attributes}>";
    $result .= parent::compile($scope);
    $result .= "</{$this->_name}>";

    return $result;
  }
}

Jade::register_extension('html', 'Jade_Extension_Html');
