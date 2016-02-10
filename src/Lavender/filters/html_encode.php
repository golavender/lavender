<?php

class Lavender_Filter_Html_Encode
{
  public function execute($thing)
  {
    if (is_array($thing)) {
      return $this->_html_entity_array($thing);
    }

    return htmlentities($thing);
  }

  public function _html_entity_array($thing)
  {
    $return = [];

    foreach ($thing as $key => $value) {
      if (is_array($value)) {
        $return[$key] = $this->_html_entity_array($value);
      } else {
        $return[$key] = htmlentities($value);
      }
    }

    return $return;
  }
}

Lavender::register_filter('html_encode', 'Lavender_Filter_Html_Encode');
