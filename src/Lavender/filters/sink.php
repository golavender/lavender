<?php

class Lavender_Filter_Sink
{
  public function execute(array $array, $items)
  {
    if (is_string($items)) {
      $items = array($items);
    }

    usort($array, function($thing1, $thing2) use ($items) {
      $thing1value = array_search($thing1, $items);
      $thing2value = array_search($thing2, $items);
      $thing1present = $thing1value !== FALSE;
      $thing2present = $thing2value !== FALSE;

      if ($thing1present && (!$thing2present || $thing1value < $thing2value)) {
        return 1;
      }
      if ($thing2present && (!$thing1present || $thing2value < $thing1value)) {
        return -1;
      }

      return 0;
    });

    return $array;
  }
}

Lavender::register_filter('sink', 'Lavender_Filter_Sink');
