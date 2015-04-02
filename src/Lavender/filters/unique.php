<?php

class Lavender_Filter_Unique
{
  public function execute(array $array)
  {
    return array_unique($array);
  }
}

Lavender::register_filter('unique', 'Lavender_Filter_Unique');
