<?php

class Lavender_Filter_Lower
{
  public function execute($string)
  {
    return strtolower($string);
  }
}

Lavender::register_filter('lower', 'Lavender_Filter_Lower');
