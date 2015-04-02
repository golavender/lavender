<?php

class Lavender_Filter_Push
{
  public function execute($array, $item)
  {
    $array[] = $item;
    return $array;
  }
}

Lavender::register_filter('push', 'Lavender_Filter_Push');
