<?php

class Lavender_Filter_Newline_Break
{
  public function execute($string)
  {
    return nl2br($string);
  }
}

Lavender::register_filter('nl2br', 'Lavender_Filter_Newline_Break');
