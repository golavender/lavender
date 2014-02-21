<?php

require '../jade.php';

Jade::config(array(
  'view_dir' => __DIR__.'/views'
));

$view = Jade::view('hello.jade');

echo $view->compile();

