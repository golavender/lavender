<?php

class Lavender_Filter_Title
{
  public function execute($string)
  {
    return ucwords($string);
  }
}

Lavender::register_filter('title', 'Lavender_Filter_Title');
