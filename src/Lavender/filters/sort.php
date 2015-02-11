<?php

class Lavender_Filter_Sort
{
  public function execute(array $array, $property = NULL)
  {
    if ($property) {
      usort($array, function ($thing1, $thing2) use ($property) {
        if (is_array($thing1) && is_array($thing2)) {
          $value1 = $thing1[$property];
          $value2 = $thing2[$property];
        }
        else {
          $value1 = $thing1->{$property};
          $value2 = $thing2->{$property};
        }

        return strcmp($value1, $value2);
      });
    }
    else {
      asort($array);
    }

    return $array;
  }
}

Lavender::register_filter('sort', 'Lavender_Filter_Sort');
