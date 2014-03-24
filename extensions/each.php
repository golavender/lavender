<?php

class Lavender_Extension_Each extends Lavender_Node
{
  private $_expression;
  private $_iterator;
  private $_key_iterator;

  public function tokenize_content(Lavender_Content $content)
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
      throw new Lavender_Exception($content, 'expected "in"');
    }

    $content->consume_next(2);

    $this->_expression = Lavender::get_extension_by_name('expression');
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

Lavender::register_extension('each', 'Lavender_Extension_Each', array('each', 'for'));
