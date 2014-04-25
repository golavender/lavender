<?php

class Lavender_Filter_Last
{
  public function execute(array $array, $number = 1)
  {
    $last = array_slice($array, $number*-1);

    if ($number == 1 && count($last) == 1) {
      return $last[0];
    }
    else {
      return $last;
    }
  }
}

Lavender::register_filter('last', 'Lavender_Filter_Last');
