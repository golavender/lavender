<?php

class Lavender_Filter_Escape_Newlines
{
  public function execute($string)
  {
    return preg_replace([
      '/(?<!\\\\)\\n/',
      '/(?<!\\\\)\\r/',
      '/(?<!\\\\)\\\\n/',
      '/(?<!\\\\)\\\\r/',
    ], [
      '\\\\\n',
      '\\\\\r',
      '\\\\\n',
      '\\\\\r',
    ], $string);
  }
}

Lavender::register_filter('escape_newlines', 'Lavender_Filter_Escape_Newlines');
