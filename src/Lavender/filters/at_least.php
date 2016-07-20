<?php

class Lavender_Filter_At_Least
{
  public function execute($number, $number_2)
  {
    return max($number, $number_2);
  }
}

Lavender::register_filter('at_least', 'Lavender_Filter_At_Least');
