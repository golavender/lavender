<?php

require '../src/Lavender/lavender.php';

const HELLOTHERE = 'asdfasdfasdf';

class test
{
  public $key = 'object value';
}

Lavender::config(array(
  'view_dir' => __DIR__.'/views',
));

$view = Lavender::view('hello');

$_GET['test_object'] = new test();

$_GET['seven'] = 7;
$_GET['eight'] = 8;

$_GET['test'] = function($arg1, $arg2) {
  return 'this is a test function: ' . $arg1 . ' ' . $arg2;
};

$_GET['array_function'] = function() {
  return array('foo', 'bar');
};

$_GET['arr'] = (object) array('foo' => array());

echo $view->compile($_GET);
