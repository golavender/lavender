<?php

class Lavender_Helper_Date
{
  public function execute($format, $timestamp = NULL)
  {
    return date($format, $timestamp);
  }
}

Lavender::register_helper('date', 'Lavender_Helper_Date');
