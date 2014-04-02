<?php

class Lavender_Filter_Slice
{
  public function execute(array $array, $offset, $length = NULL)
  {
    return array_slice($array, $offset, $length, TRUE);
  }
}

Lavender::register_filter('slice', 'Lavender_Filter_Slice');
