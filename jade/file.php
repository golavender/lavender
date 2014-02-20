<?php

class Jade_File
{
  private $_contents;
  private $_lines = array();


  private function _deal_with_line($previous, $line)
  {
    if ($previous->get_level() < $line->get_level()) {
      return $previous->add_child($line);
    }

    $parent = $previous;

    while ($parent = $parent->get_parent()) {

      if (get_class($parent) == 'Jade_File') {
        return $parent->add_line($line);
      } else if ($parent->get_level() < $line->get_level()) {
        return $parent->add_child($line);
      }
    }

    throw new Exception('foobar');
  }

  private function _parse_line_tree($lines)
  {
    $previous = array_shift($lines);
    $this->add_line($previous);

    while ($line = array_shift($lines)) {
      $this->_deal_with_line($previous, $line);
      $previous = $line;
    }
  }

  public function __construct($path)
  {
    $this->_contents = file_get_contents($path);

    $raw_lines = explode("\n", $this->_contents);

    $lines = array();
    foreach ($raw_lines as $raw_line) {
      array_push($lines, new Jade_Line($raw_line));
    }

    $this->_parse_line_tree($lines);
  }

  public function compile($data)
  {
    $result = '';

    foreach($this->_lines as $line) {
      $result .= $line->compile();
    }

    return $result;
  }

  public function add_line($line)
  {
    array_push($this->_lines, $line);
    $line->set_parent($this);

    return $this;
  }
}
