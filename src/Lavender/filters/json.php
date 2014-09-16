<?php

class Lavender_Filter_Json
{
  public function execute($thing)
  {
    return json_encode($thing);
  }
}

Lavender::register_filter('json', 'Lavender_Filter_Json');
