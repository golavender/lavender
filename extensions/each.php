<?php

class Jade_Extension_Each extends Jade_Node
{
  private $_expression;
  private $_iterator;
  private $_key_iterator;

  public function tokenize_content(Jade_Content $content)
  {
    $content->consume_until(" "); // the 'each'
    $content->consume_whitespace();

    $this->_iterator = $content->consume_regex("/[a-z0-9]/i");

    $content->consume_whitespace();

    if ($content->peek() == ',') {
      $content->consume_next(); // the ','
      $content->consume_whitespace();

      $this->_key_iterator = $content->consume_regex("/[a-z0-9]/i");

      $content->consume_whitespace();
    }

    if ($content->peek(2) != 'in') {
      throw new Jade_Exception($content, 'expected "in"');
    }

    $content->consume_next(2);

    $this->_expression = Jade::get_extension_by_name('expression');
    $this->_expression->tokenize_content($content);
  }

  public function compile(array $scope)
  {
    $array = $this->_expression->compile($scope);
    $result = '';

    foreach ($array as $key => $iterator) {
      $scope[$this->_key_iterator] = $key;
      $scope[$this->_iterator]     = $iterator;

      $result .= parent::compile($scope);
    }

    return $result;
  }
}

Jade::register_extension('each', 'Jade_Extension_Each', array('each', 'for'));
