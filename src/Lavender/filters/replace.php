<?php

class Lavender_Filter_Replace
{
  public function execute($string, $search, $replace)
  {
    return str_replace($search, $replace, $string);
  }
}

Lavender::register_filter('replace', 'Lavender_Filter_Replace');
