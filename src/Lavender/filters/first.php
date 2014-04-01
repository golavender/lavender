<?php

class Lavender_Filter_First
{
  public function execute(array $array)
  {
    if (isset($array[0])) {
      return $array[0];
    }
    else {
      return NULL;
    }
  }
}

Lavender::register_filter('first', 'Lavender_Filter_First');
