<?php

require 'lavender.php';

if (!$argv || count($argv) < 4) {
  die('usage lavender-cli.php <root-view-directory> <target-view-name> <target-filename>');
}

Lavender::config(array(
  'view_dir' => $argv[1]
));

$output = Lavender::view($argv[2])->compile();

$path = explode('/', $argv[3]);
$filename = array_pop($path);

$directories = getcwd();
foreach ($path as $directory) {
  $directories .= '/'.$directory;
  if (!file_exists($directories)) {
    mkdir($directories);
  }
}

file_put_contents($argv[3], $output);
