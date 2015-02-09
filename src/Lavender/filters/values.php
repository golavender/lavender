<?php

class Lavender_Filter_Values
{
  public function execute(array $array)
  {
    return array_values($array);
  }
}

Lavender::register_filter('values', 'Lavender_Filter_Values');
