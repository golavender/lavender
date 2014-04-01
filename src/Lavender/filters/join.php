<?php

class Lavender_Filter_Join
{
  public function execute(array $array, $glue = ' ')
  {
    return implode($glue, $array);
  }
}

Lavender::register_filter('join', 'Lavender_Filter_Join');
