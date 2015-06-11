<?php

class Lavender_Filter_Date
{
  public function execute($timestamp, $format = 'n/j/Y')
  {
    if ($timestamp) {
      return date($format, $timestamp);
    }
    else {
      return $timestamp;
    }
  }
}

Lavender::register_filter('date', 'Lavender_Filter_Date');
