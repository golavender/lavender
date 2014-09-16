<?php

class Lavender_Filter_Is
{
  public function execute($thing, $type)
  {
    $is_object = gettype($thing) == 'object';

    if ($type == 'number') {
      return is_numeric($thing);
    }

    switch ($type) {
      case 'list':
        return is_array($thing) && array_values($thing) == $thing;
      case 'object':
        return is_array($thing) && array_values($thing) != $thing;
      default:
        if (gettype($thing) == 'object') {
          return get_class($thing) == $type;
        }
        else {
          return gettype($thing) == $type;
        }
    }
  }
}

Lavender::register_filter('is', 'Lavender_Filter_Is');
