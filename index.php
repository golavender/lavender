<?php

require 'jade/jade.php';

$view = Jade::view('hello.jade');

echo $view->compile();

