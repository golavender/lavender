<?php

class Jade_Extension_Expression extends Jade_Node
{
  private $_expression_tree = array();
  private $_operators       = array(
    '>=' => 'Jade_Expression_Node_Greater_Than_Equal_To',
    '<=' => 'Jade_Expression_Node_Less_Than_Equal_To',
    '||' => 'Jade_Expression_Node_Or',
    '&&' => 'Jade_Expression_Node_And',
    '==' => 'Jade_Expression_Node_Equal_To',
    '>'  => 'Jade_Expression_Node_Greater_Than',
    '<'  => 'Jade_Expression_Node_Less_Than',
  );
  private $_operator_order  = array(
    '>=' => 1,
    '<=' => 1,
    '>'  => 1,
    '<'  => 1,
    '==' => 1,
    '||' => 2,
    '&&' => 2,
  );

  public function tokenize_content(Jade_Content $content)
  {
    $content->consume_whitespace();

    if ($content->peek() == '=') {
      $content->consume_next(); // the '='
    }

    $this->_expression_tree = $this->_parse_left_to_right($content);

    $content->consume_whitespace();
  }

  private function _parse_left_to_right(Jade_Content $content, $parent = NULL)
  {
    $content->consume_whitespace();
    $expression = array();

    while ($next = $content->peek()) {

      foreach ($this->_operators as $operator => $class) {
        $length = strlen($operator);

        if ($content->peek($length) == $operator) {

          if ($parent && $this->_operator_order[$operator] >= $this->_operator_order[$parent]) {
            break 2; // the while
          }

          $content->consume_next($length);
          $content->consume_whitespace();

          $left = Jade::get_extension_by_name('expression');
          $left->set_tree($expression);

          $right = Jade::get_extension_by_name('expression');
          $right->set_tree($this->_parse_left_to_right($content, $operator));

          $operator_object = new $class($left, $right);

          $expression = array($operator_object);

          continue 2; // the while
        }
      }

      if ($next == '.' || $next == ' ') {
        // todo - '.' should probably have some more robust logic
        // just a separator, no need to actually do anything with it
        $content->consume_next();

      } else if ($next == '"' || $next == "'") {
        $content->consume_next(); // the '"'
        $string = $content->consume_until($next);
        $content->consume_next(); // the '"'

        $expression[] = new Jade_Expression_Node_String($string);

      } else if (preg_match('/[1-9]/', $next)) {
        // todo - integers
        $number = $content->consume_regex('/[0-9\.]/i');
        $expression[] = new Jade_Expression_Node_Number($number);

      } else if (preg_match('/[a-z]/i', $next)) {

        $name = $content->consume_regex('/[a-z0-9_]/i');

        if (!$name) {
          throw new Exception("unexpected character: \"$next\"");
        }

        if ($content->peek() != '(') {
          // just a variable
          $expression[] = new Jade_Expression_Node_Variable($name);

        } else {
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
                $sub_expression = Jade::get_extension_by_name('expression');
                $sub_expression->tokenize_content($content);
                $arguments[] = $sub_expression;
            }
          }
          $method = new Jade_Expression_Node_Method($name);
          $method->set_arguments($arguments);
          $expression[] = $method;
        }

      } else {
        break;
      }
    }

    return $expression;
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

  public function set_tree(array $tree)
  {
    $this->_expression_tree = $tree;
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

class Jade_Expression_Node_Number
{
  private $_number;

  public function __construct($number)
  {
    $this->_number = $number;
  }

  public function compile($context, $scope)
  {
    return $this->_number;
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
    $this->_right = $right;
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

class Jade_Expression_Node_Or extends Jade_Expression_Node_Comparison
{
  public function compile($content, $scope)
  {
    return $this->_left->compile($scope) || $this->_right->compile($scope);
  }
}
class Jade_Expression_Node_And extends Jade_Expression_Node_Comparison
{
  public function compile($content, $scope)
  {
    return $this->_left->compile($scope) && $this->_right->compile($scope);
  }
}
class Jade_Expression_Node_Equal_To extends Jade_Expression_Node_Comparison
{
  public function compile($content, $scope)
  {
    return $this->_left->compile($scope) == $this->_right->compile($scope);
  }
}
