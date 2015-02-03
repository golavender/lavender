<?php

class Lavender_Filter_Length
{
  public function execute($array)
  {
    if (!$array instanceof Countable && !is_array($array)) {
      throw new exception(gettype($array) . ' cannot be counted');
    }

    return count($array);
  }
}

Lavender::register_filter('length', 'Lavender_Filter_Length');
