<?php

class Lavender_Filter_Contains
{
  public function execute($haystack, $needle)
  {
    return in_array($needle, $haystack);
  }
}

Lavender::register_filter('contains', 'Lavender_Filter_Contains');
