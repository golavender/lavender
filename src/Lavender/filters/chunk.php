<?php

class Lavender_Filter_Chunk
{
  public function execute($array, $size)
  {
    return array_chunk($array ?: array(), $size);
  }
}

Lavender::register_filter('chunk', 'Lavender_Filter_Chunk');
