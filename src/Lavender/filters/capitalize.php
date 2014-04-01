<?php

class Lavender_Filter_Capitalize
{
  public function execute($string)
  {
    return ucfirst($string);
  }
}

Lavender::register_filter('capitalize', 'Lavender_Filter_Capitalize');
