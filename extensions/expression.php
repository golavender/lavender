<?php

class Jade_Extension_Expression extends Jade_Node
{
  private $_expression_tree = array();

  public function tokenize_content(Jade_Content $content)
  {
    if ($content->peek() == '=') {
      $content->consume_next(); // the '='
    }

    $content->consume_whitespace();

    while ($next = $content->peek()) {

      switch ($next) {
        case '"':
          $content->consume_next(); // the '"'
          $string = $content->consume_until('"');
          $content->consume_next(); // the '"'

          $this->_expression_tree[] = new Jade_Expression_Node_String($string);
          break;
        case "\n":
          return;
        default:
          throw new Exception("unexpected character: \"$next\"");
      }
    }
  }

  public function add_child($child)
  {
    throw new Exception('expressions cannot have children');
  }

  public function compile()
  {
    $context = NULL;

    foreach($this->_expression_tree as $node) {
      $context = $node->compile($context);
    }

    return $context;
  }

  public function is_truthy()
  {
    return (bool) $this->compile();
  }
}

Jade::register_extension('expression', 'Jade_Extension_Expression', array('='));

class Jade_Expression_Node_String
{
  private $_string;

  public function __construct($string)
  {
    $this->_string = $string;
  }

  public function compile($context)
  {
    return $this->_string;
  }
}
