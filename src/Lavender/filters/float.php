<?php

class Lavender_Filter_Float
{
  public function execute(array $array, $items = array(), $property = NULL)
  {
    if (is_string($items)) {
      $items = array($items);
    }

    usort($array, function($thing1, $thing2) use ($items, $array, $property) {
      $thing1_value = $thing1;
      $thing2_value = $thing2;

      if ($property && is_array($thing1) && is_array($thing2)) {
        $thing1_value = $thing1[$property];
        $thing2_value = $thing2[$property];
      }
      else if ($property && is_object($thing1) && is_object($thing2)) {
        $thing1_value = $thing1->{$property};
        $thing2_value = $thing2->{$property};
      }

      $thing1_float_value = array_search($thing1_value, $items);
      $thing2_float_value = array_search($thing2_value, $items);

      $thing1_natural_value = array_search($thing1, $array);
      $thing2_natural_value = array_search($thing2, $array);

      $thing1present = $thing1_float_value !== FALSE;
      $thing2present = $thing2_float_value !== FALSE;

      if ($thing1present && (!$thing2present || $thing1_float_value < $thing2_float_value)) {
        return -1;
      }
      if ($thing2present && (!$thing1present || $thing2_float_value < $thing1_float_value)) {
        return 1;
      }

      if ($thing1_natural_value > $thing2_natural_value) {
        return 1;
      }
      if ($thing2_natural_value > $thing1_natural_value) {
        return -1;
      }

      return 0;
    });

    return $array;
  }
}

Lavender::register_filter('float', 'Lavender_Filter_Float');
