<?php

require 'lavender.php';

if (!$argv || count($argv) < 4) {
  die('usage lavender-cli.php <root-view-directory> <target-view-name> <target-filename>');
}


Lavender::config(array(
  'view_dir' => $argv[1]
));

$output = Lavender::view($argv[2])->compile();

file_put_contents($argv[3], $output);
