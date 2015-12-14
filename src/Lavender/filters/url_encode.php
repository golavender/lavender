<?php

class Lavender_Filter_Url_Encode
{
  public function execute($string)
  {
    return urlencode($string);
  }
}

Lavender::register_filter('url_encode', 'Lavender_Filter_Url_Encode');
