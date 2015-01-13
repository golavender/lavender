<?php

class Lavender_Helper_Date
{
  public function execute($format, $timestamp = NULL)
  {
    if ($timestamp) {
      return date($format, $timestamp);
    }
    else {
      return date($format);
    }
  }
}

Lavender::register_helper('date', 'Lavender_Helper_Date');
