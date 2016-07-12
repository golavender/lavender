<?php

class Lavender_Filter_First
{
  public function execute(array $array)
  {
    return reset($array);
  }
}

Lavender::register_filter('first', 'Lavender_Filter_First');
