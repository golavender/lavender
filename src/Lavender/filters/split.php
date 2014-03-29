<?php

class Lavender_Filter_Split
{
  public function execute($string, $delimiter)
  {
    return explode($delimiter, $string);
  }
}

Lavender::register_filter('split', 'Lavender_Filter_Split');
