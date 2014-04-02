<?php

class Lavender_Filter_Merge
{
  public function execute(array $array1, array $array2)
  {
    return array_merge($array1, $array2);
  }
}

Lavender::register_filter('merge', 'Lavender_Filter_Merge');
