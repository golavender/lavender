<?php

require '../jade.php';

class test
{
  public $key = 'object value';
}

Jade::config(array(
  'view_dir' => __DIR__.'/views'
));

$view = Jade::view('hello.jade');

$_GET['test_array'] = array(
  'key' => 'array value'
);

$_GET['test_object'] = new test();

$_GET['seven'] = 7;
$_GET['eight'] = 8;

$_GET['test'] = function($arg1, $arg2) {
  return 'this is a test function: ' . $arg1 . ' ' . $arg2;
};

echo $view->compile($_GET);

