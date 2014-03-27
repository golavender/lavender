<?php

class Lavender_Filter_Trim
{
  public function execute($string, $characters = " \t\n\r\0\x0B")
  {
    return trim($string, $characters);
  }
}

Lavender::register_filter('trim', 'Lavender_Filter_Trim');
