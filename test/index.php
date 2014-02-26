<?php

require '../jade.php';

Jade::config(array(
  'view_dir' => __DIR__.'/views'
));

$view = Jade::view('hello.jade');

$_GET['test'] = function($arg1, $arg2) {
  return 'this is a test function: ' . $arg1 . ' ' . $arg2;
};

echo $view->compile($_GET);

