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

    $this->_parse_left_to_right($content);

    $content->consume_whitespace();

    if ($content->peek(2) == '>=') {
      $content->consume_next(2); // the '<='
      $compare = new Jade_Expression_Node_Greater_Than_Equal_To(clone $this, $content);
      $this->_expression_tree = array($compare);
    } else if ($content->peek(2) == '<=') {
      $content->consume_next(2); // the '<='
      $compare = new Jade_Expression_Node_Less_Than_Equal_To(clone $this, $content);
      $this->_expression_tree = array($compare);
    } else if ($content->peek() == '>') {
      $content->consume_next(); // the '>'
      $compare = new Jade_Expression_Node_Greater_Than(clone $this, $content);
      $this->_expression_tree = array($compare);
    } else if ($content->peek() == '<') {
      $content->consume_next(); // the '<'
      $compare = new Jade_Expression_Node_Less_Than(clone $this, $content);
      $this->_expression_tree = array($compare);
    }
  }

  private function _parse_left_to_right(Jade_Content $content)
  {
    $content->consume_whitespace();

    while ($next = $content->peek()) {

      switch ($next) {
        // '.' is just a separator, no need to actually do anything with it
        case '.':
          $content->consume_next(); // the '.'
          break;
        case '"':
        case "'":
          $content->consume_next(); // the '"'
          $string = $content->consume_until($next);
          $content->consume_next(); // the '"'

          $this->_expression_tree[] = new Jade_Expression_Node_String($string);
          break;
        case '>':
        case " ":
        case ",":
        case ";":
        case ")":
        case "\t":
        case "\n":
          return;

        // really the return above should be the default and this should be [a-z]
        default:
          $name = $content->consume_until("\n\t \"'().");

          if (!$name) {
            throw new Exception("unexpected character: \"$next\"");
          }

          if ($content->peek() == '(') {
            // we got a method here bud

            $content->consume_next(); // the '('
            $arguments = array();

            while ($next = $content->peek()) {
              switch ($next) {
                case ')':
                  $content->consume_next(); // the ')'
                  break 2;
                case ',':
                  $content->consume_next(); // the ','
                  break;
                default:
                  $expression = Jade::get_extension_by_name('expression');
                  $expression->tokenize_content($content);
                  $arguments[] = $expression;
              }
            }
            $method = new Jade_Expression_Node_Method($name);
            $method->set_arguments($arguments);
            $this->_expression_tree[] = $method;
          } else {
            $this->_expression_tree[] = new Jade_Expression_Node_Variable($name);
          }
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
    $context = $context ?: $scope;

    if (is_array($context) && isset($context[$this->_name])) {
      return $context[$this->_name];
    } else if (is_object($context) && isset($context->{$this->_name})) {
      return $context->{$this->_name};
    } else {
      return NULL;
    }
  }
}

class Jade_Expression_Node_Method extends Jade_Expression_Node_Variable
{
  private $_arguments;

  public function set_arguments(array $arguments = array())
  {
    $this->_arguments = $arguments;
  }

  public function compile($context, $scope)
  {
    $method = parent::compile($context, $scope);
    $arguments = array();

    if ($method) {

      foreach ($this->_arguments as $argument) {
        $arguments[] = $argument->compile($scope);
      }

      return call_user_func_array($method, $arguments);
    } else {
      return NULL;
    }
  }
}

abstract class Jade_Expression_Node_Comparison
{
  protected $_left;
  protected $_right;

  public function __construct($left, $right)
  {
    $this->_left = $left;
    $this->_right = Jade::get_extension_by_name('expression');
    $this->_right->tokenize_content($right);
  }

  abstract public function compile($context, $scope);
}

class Jade_Expression_Node_Greater_Than extends Jade_Expression_Node_Comparison
{
  public function compile($content, $scope)
  {
    return $this->_left->compile($scope) > $this->_right->compile($scope);
  }
}
class Jade_Expression_Node_Less_Than extends Jade_Expression_Node_Comparison
{
  public function compile($content, $scope)
  {
    return $this->_left->compile($scope) < $this->_right->compile($scope);
  }
}
class Jade_Expression_Node_Greater_Than_Equal_To extends Jade_Expression_Node_Comparison
{
  public function compile($content, $scope)
  {
    return $this->_left->compile($scope) >= $this->_right->compile($scope);
  }
}
class Jade_Expression_Node_Less_Than_Equal_To extends Jade_Expression_Node_Comparison
{
  public function compile($content, $scope)
  {
    return $this->_left->compile($scope) <= $this->_right->compile($scope);
  }
}
