<?php

class Lavender_Filter_Range
{
  public function execute($start, $stop, $step = 1)
  {
    return range($start, $stop, $step);
  }
}

Lavender::register_filter('range', 'Lavender_Filter_Range');
