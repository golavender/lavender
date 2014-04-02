<?php

class Lavender_Filter_Round
{
  public function execute($number, $precision = 0)
  {
    return round($number, $precision);
  }
}

Lavender::register_filter('round', 'Lavender_Filter_Round');
