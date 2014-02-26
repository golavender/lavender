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

  public function set_attribute($attribute)
  {
    array_push($this->_attributes, $attribute);
  }

  public function set_classes(array $classes)
  {
    array_map($this->_set_class, $classes);
  }

  public function set_attributes(array $attributes)
  {
    foreach ($attributes as $attribute) {
      $this->set_attribute(trim($attribute));
    }
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
          $this->set_attribute('id="'.$id.'"');
          break;
        case '(':
          $content->consume_next(); // the '('
          $raw = $content->consume_until(")");
          $content->consume_next(); // the ')'
          $attributes = explode(',', $raw);
          $this->set_attributes($attributes);
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
          throw new Exception("unexpected character: \"$next\"");
      }
    }
  }

  public function compile(array $scope)
  {
    $attributes = $this->_attributes;

    if ($this->_classes) {
      $attributes[] = 'class="' . implode(' ', $this->_classes) . '"';
    }

    if ($attributes) {
      $attributes = ' ' . implode(' ', $attributes);
    } else {
      $attributes = '';
    }

    $result = "<{$this->_name}{$attributes}>";
    $result .= parent::compile($scope);
    $result .= "</{$this->_name}>";

    return $result;
  }
}

Jade::register_extension('html', 'Jade_Extension_Html');
