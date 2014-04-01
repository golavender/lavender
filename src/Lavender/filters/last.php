<?php

class Lavender_Filter_Last
{
  public function execute(array $array)
  {
    if (isset($array[count($array)-1])) {
      return $array[count($array)-1];
    }
    else {
      return NULL;
    }
  }
}

Lavender::register_filter('last', 'Lavender_Filter_Last');
