<?php

class Lavender_Filter_Upper
{
  public function execute($string)
  {
    return strtoupper($string);
  }
}

Lavender::register_filter('upper', 'Lavender_Filter_Upper');
