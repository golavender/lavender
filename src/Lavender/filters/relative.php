<?php

class Lavender_Filter_Relative
{
  private $_intervals = array(
    'minute' => 60,
    'hour'   => 3600,
    'day'    => 86400,
    'month'  => 2592000,
    'year'   => 31536000,
    '_'      => NULL, // this exists so that times greater than one year will be caught
  );

  private function _past($now, $timestamp)
  {
    $diff = $now - $timestamp;

    if ($diff <= $this->_intervals['minute']) {
      return 'just now';
    }

    foreach ($this->_intervals as $name => $interval) {

      if (!$interval || $diff <= $interval) {
        return floor($diff / $last['interval']) . ' ' . $last['name'] . (floor($diff / $last['interval']) > 1 ? 's' : '') . ' ago';
      }

      $last = array('interval' => $interval, 'name' => $name);
    }
  }

  private function _future($now, $timestamp)
  {
    $diff = $timestamp - $now;

    if ($diff <= $this->_intervals['minute']) {
      return 'right now';
    }

    foreach ($this->_intervals as $name => $interval) {

      if (!$interval || $diff <= $interval) {
        return 'in ' . floor($diff / $last['interval']) . ' ' . $last['name'] . (floor($diff / $last['interval']) > 1 ? 's' : '');
      }

      $last = array('interval' => $interval, 'name' => $name);
    }
  }

  public function execute($timestamp)
  {
    $now = time();

    if ($timestamp <= $now) {
      return $this->_past($now, $timestamp);
    }
    else {
      return $this->_future($now, $timestamp);
    }
  }
}

Lavender::register_filter('relative', 'Lavender_Filter_Relative');
