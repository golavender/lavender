<?php

class Lavender_Filter_Ceil
{
  public function execute($number)
  {
    return ceil($number);
  }
}

Lavender::register_filter('ceil', 'Lavender_Filter_Ceil');
