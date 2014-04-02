<?php

class Lavender_Filter_Floor
{
  public function execute($number)
  {
    return floor($number);
  }
}

Lavender::register_filter('floor', 'Lavender_Filter_Floor');
