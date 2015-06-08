<?php

class Lavender_Extension_Expression extends Lavender_Node
{
  protected $_delimiter = '';

  private $_expression_tree = array();
  private static $_constants       = array(
    'true'  => 'Lavender_Expression_Node_True',
    'false' => 'Lavender_Expression_Node_False',
    'TRUE'  => 'Lavender_Expression_Node_True',
    'FALSE' => 'Lavender_Expression_Node_False',
  );
  private static $_operators       = array(
    '>=' => 'Lavender_Expression_Node_Greater_Than_Equal_To',
    '<=' => 'Lavender_Expression_Node_Less_Than_Equal_To',
    '||' => 'Lavender_Expression_Node_Or',
    '&&' => 'Lavender_Expression_Node_And',
    '==' => 'Lavender_Expression_Node_Equal_To',
    '!=' => 'Lavender_Expression_Node_Not_Equal_To',
    '%'  => 'Lavender_Expression_Node_Modulus',
    '>'  => 'Lavender_Expression_Node_Greater_Than',
    '<'  => 'Lavender_Expression_Node_Less_Than',
    '='  => 'Lavender_Expression_Node_Assignment',
    '/'  => 'Lavender_Expression_Node_Divide',
    '*'  => 'Lavender_Expression_Node_Multiply',
    '+'  => 'Lavender_Expression_Node_Add',
    '-'  => 'Lavender_Expression_Node_Subtract',
  );
  private static $_operator_order  = array(
    '!'  => 0,
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
    '!=' => 3,
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

    $this->tokenize_internal($content);
  }

  public function tokenize_internal(Lavender_Content $content, $until = NULL)
  {
    while (TRUE) {

      $this->_expression_tree = array_merge($this->_expression_tree, $this->_parse_left_to_right($content));
      $content->consume_whitespace();

      if ($until && $content->peek() != $until && $content->peek() == "\n") {
        $content->consume_next();
      }
      else {
        break;
      }
    }
  }

  private function _parse_left_to_right(Lavender_Content $content, $parent = NULL)
  {
    $content->consume_whitespace();
    if ($content->peek() == "\n") {
      $content->consume_next();
    }
    $content->consume_whitespace();

    $expression = array();

    while (($next = $content->peek()) !== '') {
      $bit = $this->_parse_next($content, $parent, $expression);

      if ($bit) {
        $expression[] = $bit;
        $content->consume_whitespace();
      }
      else {
        break;
      }
    }

    return $expression;
  }

  private function _parse_next(Lavender_Content $content, $parent, &$expression)
  {
    $content->consume_whitespace();
    $next = $content->peek();

    foreach (static::$_operators as $operator => $class) {
      $length = strlen($operator);

      if ($content->peek($length) == $operator) {

        if ($parent && static::$_operator_order[$operator] >= static::$_operator_order[$parent]) {
          return NULL;
        }

        $content->consume_next($length);
        $content->consume_whitespace();

        $left = array_pop($expression);

        $right = Lavender::get_extension_by_name('expression');
        $right->set_tree($this->_parse_left_to_right($content, $operator));

        return new $class($left, $right);
      }
    }

    foreach (static::$_constants as $constant => $class) {
      $length = strlen($constant);

      if ($content->peek($length) == $constant) {

        $content->consume_next($length);

        return new $class();
      }
    }

    if ($next == '!') {
      $content->consume_next(); // the '!'
      $content->consume_whitespace();

      $sub_expression = Lavender::get_extension_by_name('expression');
      $sub_expression->set_tree($this->_parse_left_to_right($content, $next));

      return new Lavender_Expression_Node_Not($sub_expression);
    }
    else if ($next == '"' || $next == "'") {
      $content->consume_next(); // the '"'

      $text = Lavender::get_extension_by_name('text');
      $text->set_delimiter('');
      $text->add_stop($next);
      $text->tokenize_content($content);

      if ($content->peek() == $next) {
        $content->consume_next(); // the '"'
      }
      else {
        throw new Lavender_Exception($content, 'unclosed string');
      }

      return new Lavender_Expression_Node_String($text);
    }
    else if ($next == '[' && $expression && $expression[count($expression)-1] instanceof Lavender_Expression_Interface_Data_Container) {
      $content->consume_next(); // the '['
      $context = array_pop($expression);

      $sub_expression = Lavender::get_extension_by_name('expression');
      $sub_expression->tokenize_internal($content, ']');

      if ($content->peek() == ']') {
        $content->consume_next(); // the ']'
      }
      else {
        throw new Lavender_Exception($content, 'expected "]"');
      }

      return new Lavender_Expression_Node_Array_Bracket($context, $sub_expression);
    }
    else if ($next == '[') {
      $content->consume_next(); // the '['
      $content->consume_whitespace();

      $bits = array();
      while (($next = $content->peek()) !== NULL) {
        switch ($next) {
          case ']':
            $content->consume_next(); // the ']'
            break 2;
          case ',':
          case "\n":
            $content->consume_next();
            break;
          default:
            $sub_expression = Lavender::get_extension_by_name('expression');
            $sub_expression->tokenize_internal($content);
            $bits[] = $sub_expression;
            break;
        }
        $content->consume_whitespace();
      }

      return new Lavender_Expression_Node_Array($bits);
    }
    else if ($next == '{') {
      $content->consume_next(); // the '{'
      $content->consume_whitespace();

      $bits = array();
      while ($next = $content->peek()) {
        switch ($next) {
          case '}':
            $content->consume_next(); // the '}'
            break 2;
          case ',':
          case "\n":
            $content->consume_next();
            break;
          default:
            $key = $content->consume_regex('/[a-z0-9_]/i');
            $content->consume_whitespace();

            if ($content->peek() !== ':') {
              throw new Lavender_Exception($content, "expected \":\" but got \"{$content->peek()}\"");
            }
            $content->consume_next(); // the ':'
            $content->consume_whitespace();

            $sub_expression = Lavender::get_extension_by_name('expression');
            $sub_expression->tokenize_internal($content);
            $bits[$key] = $sub_expression;
            break;
        }
        $content->consume_whitespace();
      }

      return new Lavender_Expression_Node_Array($bits);
    }
    else if (preg_match('/[0-9]/', $next)) {
      $number = $content->consume_regex('/[0-9\.]/i');
      return new Lavender_Expression_Node_Number($number);
    }
    else if ($next == '|') {
      $content->consume_next(); // the "|"
      $content->consume_whitespace();

      $name = $content->consume_regex('/[a-z0-9_]/i');

      $arguments = array();
      if ($content->peek() == '(') {
        $content->consume_next(); // the '('

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
              $sub_expression->tokenize_internal($content);
              $arguments[] = $sub_expression;
          }
        }
      }

      $context = array_pop($expression);
      $filter = new Lavender_Expression_Node_Filter($name, $arguments);

      $filter->set_context($context);
      return $filter;
    }
    else if ($next == '(' && $expression && $expression[count($expression)-1] instanceof Lavender_Expression_Interface_Data_Container) {
      $content->consume_next(); // the '('
      $context = array_pop($expression);

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
            $sub_expression->tokenize_internal($content);
            $arguments[] = $sub_expression;
        }
      }
      $method = new Lavender_Expression_Node_Method();
      $method->set_context($context);
      $method->set_arguments($arguments);
      return $method;
    }
    else if ($next == '(') {
      $content->consume_next(); // the '('

      $sub_expression = Lavender::get_extension_by_name('expression');
      $sub_expression->tokenize_internal($content, ')');

      if ($content->peek() == ')') {
        $content->consume_next(); // the ')'
      }
      else {
        throw new Lavender_Exception($content, 'unclosed ")"');
      }
      return $sub_expression;
    }
    else if ($next == '.') {
      $content->consume_next(); // the '.'
      $context = array_pop($expression);
      $chain = $this->_parse_next($content, $parent, $expression);
      $chain->set_context($context);
      return $chain;
    }
    else if (preg_match('/[a-z]/i', $next)) {

      $name = $content->consume_regex('/[a-z0-9_]/i');

      if (!$name) {
        throw new Lavender_Exception($content);
      }
      else {
        return new Lavender_Expression_Node_Variable($name);
      }
    }
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

  public function _compile(array &$scope)
  {
    $value = NULL;

    foreach($this->_expression_tree as $node) {
      $value = $node->compile($scope);
    }

    if ($this->_output) {
      return $value;
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


interface Lavender_Expression_Interface_Data_Container
{
  public function set_context($context);
}
interface Lavender_Expression_Interface_Assignable
{
  public function assign(&$scope, $value);
  public function assignable();
}

class Lavender_Expression_Node_True
{
  public function compile($scope)
  {
    return TRUE;
  }
}

class Lavender_Expression_Node_False
{
  public function compile($scope)
  {
    return FALSE;
  }
}

class Lavender_Expression_Node_Not
{
  private $_sub_expression;

  public function __construct($expression)
  {
    $this->_sub_expression = $expression;
  }

  public function compile(&$scope)
  {
    return !$this->_sub_expression->compile($scope);
  }
}

class Lavender_Expression_Node_String
{
  private $_string;

  public function __construct($string)
  {
    $this->_string = $string;
  }

  public function compile($scope)
  {
    return $this->_string->compile($scope);
  }
}

class Lavender_Expression_Node_Number
{
  private $_number;

  public function __construct($number)
  {
    $this->_number = $number;
  }

  public function compile($scope)
  {
    return (float) $this->_number;
  }
}

class Lavender_Expression_Node_Array
{
  private $_array;

  public function __construct(array $array)
  {
    $this->_array = $array;
  }

  public function compile($scope)
  {
    $result = array();

    foreach ($this->_array as $key => $bit) {
      $result[$key] = $bit->compile($scope);
    }

    return $result;
  }
}

class Lavender_Expression_Node_Filter
{
  private $_filter;
  private $_arguments;
  private $_context;

  public function __construct($name, $arguments)
  {
    $this->_filter = Lavender::get_filter_by_name($name);
    $this->_arguments = $arguments;

    if (!$this->_filter) {
      throw new Exception("$name filter could not be found");
    }
  }

  public function set_context($context)
  {
    $this->_context = $context;
    return $this;
  }

  public function compile($scope)
  {
    $arguments = array();
    foreach ($this->_arguments as $argument) {
      $arguments[] = $argument->compile($scope);
    }

    array_unshift($arguments, $this->_context->compile($scope));

    return call_user_func_array(array($this->_filter, 'execute'), $arguments);
  }
}

class Lavender_Expression_Node_Variable implements Lavender_Expression_Interface_Data_Container, Lavender_Expression_Interface_Assignable
{
  private $_name;
  private $_context;

  public function __construct($name)
  {
    $this->_name = $name;
  }

  public function assignable()
  {
    return
      !$this->_context ||
      (
        $this->_context instanceof Lavender_Expression_Interface_Assignable &&
        $this->_context->assignable()
      );
  }

  public function assign(&$scope, $value)
  {
    if ($this->_context) {
      $compiled = $this->_context->compile($scope);
      $compiled = $this->_assign($compiled, $value);
      $scope = $this->_context->assign($scope, $compiled);
    }
    else {
      $scope = $this->_assign($scope, $value);
    }

    return $scope;
  }

  private function _assign($scope, $value)
  {
    if (is_array($scope)) {
      $scope[$this->_name] = $value;
    }
    else if (is_object($scope)) {
      $scope->{$this->_name} = $value;
    }
    else {
      throw new Exception('cannot assign value to this non-object/non-array');
    }

    return $scope;
  }

  public function set_context($context)
  {
    $this->_context = $context;
    return $this;
  }

  public function get_name()
  {
    return $this->_name;
  }

  public function compile($scope)
  {
    if ($this->_context) {
      $context = $this->_context->compile($scope);
    }
    else {
      $context = $scope;
    }

    if ($context instanceof Closure) {
      return NULL;
    }

    if (is_array($context) && isset($context[$this->_name])) {
      return $context[$this->_name];
    }
    else if (is_object($context) && isset($context->{$this->_name})) {
      return $context->{$this->_name};
    }
    else if (is_object($context) && method_exists($context, $this->_name)) {

      $name = $this->_name;

      // since we can't return a reference to this function, stub a wrapper and return that
      return function() use ($context, $name) {
        return call_user_func_array(array($context, $name), func_get_args());
      };
    }
    else {
      return NULL;
    }
  }
}

class Lavender_Expression_Node_Method implements Lavender_Expression_Interface_Data_Container
{
  private $_context;
  private $_arguments;

  public function set_context($context)
  {
    $this->_context = $context;
    return $this;
  }

  public function set_arguments(array $arguments = array())
  {
    $this->_arguments = $arguments;
  }

  private function _compile_arguments($scope)
  {
    $arguments = array();

    foreach ($this->_arguments as $argument) {
      $arguments[] = $argument->compile($scope);
    }

    return $arguments;
  }

  public function compile($scope)
  {
    $method = $this->_context->compile($scope);

    if ($method) {
      return call_user_func_array($method, $this->_compile_arguments($scope));

    } else if (
      $this->_context instanceof Lavender_Expression_Node_Variable &&
      $helper = Lavender::get_helper_by_name($this->_context->get_name())
    ) {
      return call_user_func_array(array($helper, 'execute'), $this->_compile_arguments($scope));
    } else {
      return NULL;
    }
  }
}

class Lavender_Expression_Node_Array_Bracket implements Lavender_Expression_Interface_Data_Container, Lavender_Expression_Interface_Assignable
{
  private $_context;
  private $_sub_expression;

  public function __construct($context, $sub_expression)
  {
    $this->_context        = $context;
    $this->_sub_expression = $sub_expression;
  }

  public function assignable()
  {
    return
      !$this->_context ||
      (
        $this->_context instanceof Lavender_Expression_Interface_Assignable &&
        $this->_context->assignable()
      );
  }

  public function assign(&$scope, $value)
  {
    $compiled = $this->_context->compile($scope);

    $key = $this->_key($scope);

    $value = $this->_assign($compiled, $key, $value);

    $scope = $this->_context->assign($scope, $value);

    return $scope;
  }

  private function _assign($scope, $key, $value)
  {
    if (is_array($scope)) {
      $scope[$key] = $value;
    }
    else if (is_object($scope)) {
      $scope->{$key} = $value;
    }
    else {
      throw new Exception('cannot assign value to this non-object/non-array');
    }

    return $scope;
  }

  public function set_context($context)
  {
    $this->_context = $context;
    return $this;
  }

  private function _key($scope)
  {
    $key = $this->_sub_expression->compile($scope);

    if (is_numeric($key)) {
      $intkey = (int) $key;

      // a float was passed in, do nothing
      if ($intkey != $key) {
        return NULL;
      }

      $key = $intkey;
    }

    return $key;
  }

  public function compile(&$scope)
  {
    $thing = $this->_context->compile($scope);
    $key   = $this->_key($scope);

    if (is_array($thing) && array_key_exists($key, $thing)) {
      return $thing[$key];
    }
    else if (is_object($thing) && property_exists($thing, $key)) {
      return $thing->$key;
    }
    else if (is_object($thing) && $thing instanceof ArrayAccess && $thing->offsetExists($key)) {
      return $thing[$key];
    }
    else {
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

  abstract public function compile(&$scope);
}

class Lavender_Expression_Node_Greater_Than extends Lavender_Expression_Node_Comparison
{
  public function compile(&$scope)
  {
    return $this->_left->compile($scope) > $this->_right->compile($scope);
  }
}
class Lavender_Expression_Node_Less_Than extends Lavender_Expression_Node_Comparison
{
  public function compile(&$scope)
  {
    return $this->_left->compile($scope) < $this->_right->compile($scope);
  }
}
class Lavender_Expression_Node_Greater_Than_Equal_To extends Lavender_Expression_Node_Comparison
{
  public function compile(&$scope)
  {
    return $this->_left->compile($scope) >= $this->_right->compile($scope);
  }
}
class Lavender_Expression_Node_Less_Than_Equal_To extends Lavender_Expression_Node_Comparison
{
  public function compile(&$scope)
  {
    return $this->_left->compile($scope) <= $this->_right->compile($scope);
  }
}

class Lavender_Expression_Node_Or extends Lavender_Expression_Node_Comparison
{
  public function compile(&$scope)
  {
    return $this->_left->compile($scope) || $this->_right->compile($scope);
  }
}
class Lavender_Expression_Node_And extends Lavender_Expression_Node_Comparison
{
  public function compile(&$scope)
  {
    return $this->_left->compile($scope) && $this->_right->compile($scope);
  }
}
class Lavender_Expression_Node_Equal_To extends Lavender_Expression_Node_Comparison
{
  public function compile(&$scope)
  {
    return $this->_left->compile($scope) == $this->_right->compile($scope);
  }
}
class Lavender_Expression_Node_Not_Equal_To extends Lavender_Expression_Node_Comparison
{
  public function compile(&$scope)
  {
    return $this->_left->compile($scope) != $this->_right->compile($scope);
  }
}
class Lavender_Expression_Node_Modulus extends Lavender_Expression_Node_Comparison
{
  public function compile(&$scope)
  {
    return $this->_left->compile($scope) % $this->_right->compile($scope);
  }
}
class Lavender_Expression_Node_Assignment
{
  protected $_left;
  protected $_right;

  public function __construct(Lavender_Expression_Interface_Assignable $left, $right)
  {
    if (!$left->assignable()) {
      throw new Exception('trying to assign to a thing which is not assignable');
    }

    $this->_left = $left;
    $this->_right = $right;
  }

  public function compile(&$scope)
  {
    return $this->_left->assign($scope, $this->_right->compile($scope));
  }
}
class Lavender_Expression_Node_Divide extends Lavender_Expression_Node_Comparison
{
  public function compile(&$scope)
  {
    return $this->_left->compile($scope) / $this->_right->compile($scope);
  }
}
class Lavender_Expression_Node_Multiply extends Lavender_Expression_Node_Comparison
{
  public function compile(&$scope)
  {
    return $this->_left->compile($scope) * $this->_right->compile($scope);
  }
}
class Lavender_Expression_Node_Add extends Lavender_Expression_Node_Comparison
{
  public function compile(&$scope)
  {
    $left = $this->_left->compile($scope);
    $right = $this->_right->compile($scope);


    if (is_numeric($left) && is_numeric($right)) {
      return $left + $right;
    }
    else {
      return $left . $right;
    }

  }
}
class Lavender_Expression_Node_Subtract extends Lavender_Expression_Node_Comparison
{
  public function compile(&$scope)
  {
    return $this->_left->compile($scope) - $this->_right->compile($scope);
  }
}
