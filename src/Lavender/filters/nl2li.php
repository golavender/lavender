<?php

class Lavender_Filter_Newline_List
{
  public function execute($string)
  {
    return '<li>' . implode('</li><li>', explode("\n", $string)) . '</li>';
  }
}

Lavender::register_filter('nl2li', 'Lavender_Filter_Newline_List');
