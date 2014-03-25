<?php

class Lavender_Extension_Expression extends Lavender_Node
{
  private $_output          = TRUE;
  private $_expression_tree = array();
  private $_operators       = array(
    '>=' => 'Lavender_Expression_Node_Greater_Than_Equal_To',
    '<=' => 'Lavender_Expression_Node_Less_Than_Equal_To',
    '||' => 'Lavender_Expression_Node_Or',
    '&&' => 'Lavender_Expression_Node_And',
    '==' => 'Lavender_Expression_Node_Equal_To',
    '%'  => 'Lavender_Expression_Node_Modulus',
    '>'  => 'Lavender_Expression_Node_Greater_Than',
    '<'  => 'Lavender_Expression_Node_Less_Than',
    '='  => 'Lavender_Expression_Node_Assignment',
    '/'  => 'Lavender_Expression_Node_Divide',
    '*'  => 'Lavender_Expression_Node_Multiply',
    '+'  => 'Lavender_Expression_Node_Add',
    '-'  => 'Lavender_Expression_Node_Subtract',
  );
  private $_operator_order  = array(
    '/'  => 1,
    '*'  => 1,
    '%'  => 1,
    '+'  => 2,
    '-'  => 2,
    '>=' => 3,
    '<=' => 3,
    '>'  => 3,
    '<'  => 3,
    '==' => 3,
    '||' => 4,
    '&&' => 4,
    '='  => 5,
  );

  public function tokenize_content(Lavender_Content $content)
  {
    $content->consume_whitespace();

    if ($content->peek() == '=') {
      $content->consume_next(); // the '='
    }
    else if ($content->peek() == '-') {
      $this->_output = FALSE;
      $content->consume_next(); // the '-'
    }

    $this->_expression_tree = $this->_parse_left_to_right($content);

    $content->consume_whitespace();
  }

  private function _parse_left_to_right(Lavender_Content $content, $parent = NULL)
  {
    $content->consume_whitespace();
    $expression = array();

    while (($next = $content->peek()) !== '') {
      foreach ($this->_operators as $operator => $class) {
        $length = strlen($operator);

        if ($content->peek($length) == $operator) {

          if ($parent && $this->_operator_order[$operator] >= $this->_operator_order[$parent]) {
            break 2; // the while
          }

          $content->consume_next($length);
          $content->consume_whitespace();

          $left = Lavender::get_extension_by_name('expression');
          $left->set_tree($expression);

          $right = Lavender::get_extension_by_name('expression');
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

        $expression[] = new Lavender_Expression_Node_String($string);

      } else if (preg_match('/[0-9]/', $next)) {
        $number = $content->consume_regex('/[0-9\.]/i');
        $expression[] = new Lavender_Expression_Node_Number($number);

      } else if (preg_match('/[a-z]/i', $next)) {

        $name = $content->consume_regex('/[a-z0-9_]/i');

        if (!$name) {
          throw new Lavender_Exception($content);
        }

        if ($content->peek() != '(') {
          // just a variable
          $expression[] = new Lavender_Expression_Node_Variable($name);

        } else {
          // we got a method here bud
          $content->consume_next(); // the '('
          $arguments = array();

          while (($next = $content->peek()) !== '') {
            switch ($next) {
              case ')':
                $content->consume_next(); // the ')'
                break 2;
              case ',':
                $content->consume_next(); // the ','
                break;
              default:
                $sub_expression = Lavender::get_extension_by_name('expression');
                $sub_expression->tokenize_content($content);
                $arguments[] = $sub_expression;
            }
          }
          $method = new Lavender_Expression_Node_Method($name);
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

  public function assign(array &$scope, $value)
  {
    return $scope = $this->_assign($this->_expression_tree, $value, $scope);
  }

  private function _assign($tree, $value, $scope)
  {
    $current = array_shift($tree);

    if ($tree) {
      $value = $this->_assign($tree, $value, $current->compile($scope, $scope));
    }

    return $current->assign($scope, $value);
  }

  public function compile(array &$scope)
  {
    $context = NULL;

    foreach($this->_expression_tree as $node) {
      $context = $node->compile($context, $scope);
    }

    if ($this->_output) {
      return $context;
    }
    else {
      return '';
    }
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

Lavender::register_extension('expression', 'Lavender_Extension_Expression', array('=', '-'));

class Lavender_Expression_Node_String
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

class Lavender_Expression_Node_Number
{
  private $_number;

  public function __construct($number)
  {
    $this->_number = $number;
  }

  public function compile($context, $scope)
  {
    return (float) $this->_number;
  }
}

class Lavender_Expression_Node_Variable
{
  private $_name;

  public function __construct($name)
  {
    $this->_name = $name;
  }

  public function assign($scope, $value)
  {
    if (is_array($scope)) {
      $scope[$this->_name] = $value;
    } else if (is_object($scope)) {
      $scope->{$this->_name} = $value;
    } else {
      throw new Exception('cannot assign value to this non-object/non-array');
    }

    return $scope;
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

class Lavender_Expression_Node_Method extends Lavender_Expression_Node_Variable
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

abstract class Lavender_Expression_Node_Comparison
{
  protected $_left;
  protected $_right;

  public function __construct($left, $right)
  {
    $this->_left = $left;
    $this->_right = $right;
  }

  abstract public function compile($context, &$scope);
}

class Lavender_Expression_Node_Greater_Than extends Lavender_Expression_Node_Comparison
{
  public function compile($context, &$scope)
  {
    return $this->_left->compile($scope) > $this->_right->compile($scope);
  }
}
class Lavender_Expression_Node_Less_Than extends Lavender_Expression_Node_Comparison
{
  public function compile($context, &$scope)
  {
    return $this->_left->compile($scope) < $this->_right->compile($scope);
  }
}
class Lavender_Expression_Node_Greater_Than_Equal_To extends Lavender_Expression_Node_Comparison
{
  public function compile($context, &$scope)
  {
    return $this->_left->compile($scope) >= $this->_right->compile($scope);
  }
}
class Lavender_Expression_Node_Less_Than_Equal_To extends Lavender_Expression_Node_Comparison
{
  public function compile($context, &$scope)
  {
    return $this->_left->compile($scope) <= $this->_right->compile($scope);
  }
}

class Lavender_Expression_Node_Or extends Lavender_Expression_Node_Comparison
{
  public function compile($context, &$scope)
  {
    return $this->_left->compile($scope) || $this->_right->compile($scope);
  }
}
class Lavender_Expression_Node_And extends Lavender_Expression_Node_Comparison
{
  public function compile($context, &$scope)
  {
    return $this->_left->compile($scope) && $this->_right->compile($scope);
  }
}
class Lavender_Expression_Node_Equal_To extends Lavender_Expression_Node_Comparison
{
  public function compile($context, &$scope)
  {
    return $this->_left->compile($scope) == $this->_right->compile($scope);
  }
}
class Lavender_Expression_Node_Modulus extends Lavender_Expression_Node_Comparison
{
  public function compile($context, &$scope)
  {
    return $this->_left->compile($scope) % $this->_right->compile($scope);
  }
}
class Lavender_Expression_Node_Assignment extends Lavender_Expression_Node_Comparison
{
  public function compile($context, &$scope)
  {
    return $this->_left->assign($scope, $this->_right->compile($scope));
  }
}
class Lavender_Expression_Node_Divide extends Lavender_Expression_Node_Comparison
{
  public function compile($context, &$scope)
  {
    return $this->_left->compile($scope) / $this->_right->compile($scope);
  }
}
class Lavender_Expression_Node_Multiply extends Lavender_Expression_Node_Comparison
{
  public function compile($context, &$scope)
  {
    return $this->_left->compile($scope) * $this->_right->compile($scope);
  }
}
class Lavender_Expression_Node_Add extends Lavender_Expression_Node_Comparison
{
  public function compile($context, &$scope)
  {
    return $this->_left->compile($scope) + $this->_right->compile($scope);
  }
}
class Lavender_Expression_Node_Subtract extends Lavender_Expression_Node_Comparison
{
  public function compile($context, &$scope)
  {
    return $this->_left->compile($scope) - $this->_right->compile($scope);
  }
}
