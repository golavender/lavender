<?php

class Lavender_Filter_Reverse
{
  public function execute(array $array)
  {
    return array_reverse($array);
  }
}

Lavender::register_filter('reverse', 'Lavender_Filter_Reverse');
