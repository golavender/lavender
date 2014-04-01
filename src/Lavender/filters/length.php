<?php

class Lavender_Filter_Length
{
  public function execute(array $array)
  {
    return count($array);
  }
}

Lavender::register_filter('length', 'Lavender_Filter_Length');
