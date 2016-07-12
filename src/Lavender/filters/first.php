<?php

class Lavender_Filter_First
{
  public function execute(array $array)
  {
    if ($array && count($array) > 0) {
      return array_values($array)[0];
    }

    return false;
  }
}

Lavender::register_filter('first', 'Lavender_Filter_First');
