<?php

class Lavender_Filter_Sort
{
  public function execute(array $array)
  {
    asort($array);
    return $array;
  }
}

Lavender::register_filter('sort', 'Lavender_Filter_Sort');
