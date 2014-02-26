<?php

class Jade_Extension_Expression extends Jade_Node
{
  private $_expression_tree = array();

  public function tokenize_content(Jade_Content $content)
  {
    $content->consume_whitespace();

    if ($content->peek() == '=') {
      $content->consume_next(); // the '='
    }

    $content->consume_whitespace();

    while ($next = $content->peek()) {

      switch ($next) {
        case '"':
        case "'":
          $content->consume_next(); // the '"'
          $string = $content->consume_until($next);
          $content->consume_next(); // the '"'

          $this->_expression_tree[] = new Jade_Expression_Node_String($string);
          break;
        case " ":
        case "\t":
        case "\n":
          return;
        default:
          $name = $content->consume_until("\n\t \"'(");

          if (!$name) {
            throw new Exception("unexpected character: \"$next\"");
          }

          $this->_expression_tree[] = new Jade_Expression_Node_Variable($name);
      }
    }
  }

  public function add_child($child)
  {
    throw new Exception('expressions cannot have children');
  }

  public function compile(array $scope)
  {
    $context = NULL;

    foreach($this->_expression_tree as $node) {
      $context = $node->compile($context, $scope);
    }

    return $context;
  }

  public function is_truthy(array $scope)
  {
    return (bool) $this->compile($scope);
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

  public function compile($context, $scope)
  {
    return $this->_string;
  }
}

class Jade_Expression_Node_Variable
{
  private $_name;

  public function __construct($name)
  {
    $this->_name = $name;
  }

  public function compile($context, $scope)
  {
    if (isset($scope[$this->_name])) {
      return $scope[$this->_name];
    } else {
      return '';
    }
  }
}
