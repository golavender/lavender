<?php

class Lavender_Filter_Number_Format
{
  public function execute($number, $decimals = 0, $point = '.', $thousands = ',')
  {
    return number_format($number, $decimals, $point, $thousands);
  }
}

Lavender::register_filter('number_format', 'Lavender_Filter_Number_Format');
