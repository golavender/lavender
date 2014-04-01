<?php

class Lavender_Filter_Keys
{
  public function execute(array $array)
  {
    return array_keys($array);
  }
}

Lavender::register_filter('keys', 'Lavender_Filter_Keys');
