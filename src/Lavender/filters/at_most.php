<?php

class Lavender_Filter_At_Most
{
  public function execute($number, $number_2)
  {
    return min($number, $number_2);
  }
}

Lavender::register_filter('at_most', 'Lavender_Filter_At_Most');
