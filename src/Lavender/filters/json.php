<?php

class Lavender_Filter_Json
{
  public function execute($thing, $pretty = false)
  {
    if ($pretty) {
      return json_encode($thing, JSON_PRETTY_PRINT);
    }

    return json_encode($thing);
  }
}

Lavender::register_filter('json', 'Lavender_Filter_Json');
