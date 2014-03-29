<?php

class Lavender_Content
{
  public $_full_content;
  public $_content;
  private $_line = 1;

  public function __construct($raw_content)
  {
    $this->_content = $this->_full_content = $raw_content;
  }

  public function get_full_content()
  {
    return $this->_full_content;
  }

  public function get_line()
  {
    return $this->_line;
  }

  public function consume_until($until)
  {
    $result = '';
    $until = str_split($until);

    while (($next = $this->peek()) !== '') {
      if (in_array($next, $until)) {
        break;
      } else {
        $result .= $this->consume_next();
      }
    }
    return $result;
  }

  public function consume_next($length = 1)
  {
    $result = substr($this->_content, 0, $length);

    $this->_line += substr_count($result, "\n");

    $this->_content = substr($this->_content, $length);
    return $result;
  }

  public function consume_regex($regex)
  {
    $result = '';

    while (($next = $this->peek()) !== '') {
      if (preg_match($regex, $next)) {
        $result .= $this->consume_next();
      } else {
        break;
      }
    }

    return $result;
  }

  public function consume_whitespace()
  {
    $start_length = strlen($this->_content);
    $this->_content = ltrim($this->_content, " \t");
    return $start_length - strlen($this->_content);
  }

  public function peek($length = 1, $index = 0)
  {
    if (!$this->_content) {
      return '';
    }
    return substr($this->_content, $index, $length);
  }

  public function peek_until($until)
  {
    $result = '';
    $until = str_split($until);

    $i = 0;
    while (($next = $this->peek(1, $i)) !== '') {
      if (in_array($next, $until)) {
        break;
      } else {
        $result .= $next;
      }
      $i++;
    }
    return $result;
  }
}
