<?php

require 'jade/view.php';

$view = new Jade_View('hello.jade');

echo $view->compile();

