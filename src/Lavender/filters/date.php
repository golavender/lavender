<?php

class Lavender_Filter_Date
{
  public function execute($timestamp, $format = 'n/j/Y')
  {
    return date($format, $timestamp);
  }
}

Lavender::register_filter('date', 'Lavender_Filter_Date');
