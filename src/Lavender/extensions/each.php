<?php

class Lavender_Extension_Each extends Lavender_Node
{
  private $_expression;
  private $_iterator;
  private $_key_iterator;
  private $_array;

  protected $_delimiter = '';

  public function tokenize_content(Lavender_Content $content)
  {
    parent::tokenize_content($content);

    $content->consume_until(" "); // the 'each'
    $content->consume_whitespace();

    $this->_content = $content;
    $this->_line    = $content->get_line();

    $this->_iterator = $content->consume_regex("/[a-z0-9_]/i");

    $content->consume_whitespace();

    if ($content->peek() == ',') {
      $content->consume_next(); // the ','
      $content->consume_whitespace();

      $this->_key_iterator = $content->consume_regex("/[a-z0-9_]/i");

      $content->consume_whitespace();
    }

    if ($content->peek(2) != 'in') {
      throw new Lavender_Exception($content, 'expected "in"');
    }

    $content->consume_next(2);

    $this->_expression = Lavender::get_extension_by_name('expression');
    $this->_expression->tokenize_content($content);
  }

  public function _compile(array &$scope)
  {
    $array = $this->_array($scope) ?: array();

    if (is_array($array) || $array instanceof Traversable) {
      $result = '';

      foreach ($array as $key => $iterator) {
        $scope[$this->_key_iterator] = $key;
        $scope[$this->_iterator]     = $iterator;

        $result .= parent::_compile($scope);
      }
    }
    else {
      $this->_throw_exception('invalid argument for each');
    }

    return $result;
  }

  public function is_truthy(array $scope)
  {
    return (bool) $this->_array($scope);
  }

  private function _array(array $scope)
  {
    return $this->_expression->compile($scope);
  }
}

Lavender::register_extension('each', 'Lavender_Extension_Each', array('each ', 'for '));
