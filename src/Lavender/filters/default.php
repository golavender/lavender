<?php

class Lavender_Filter_Default
{
  public function execute($first, $second)
  {
    return $first ?: $second;
  }
}

Lavender::register_filter('default', 'Lavender_Filter_Default');
