<?php

class Lavender_Extension_Text extends Lavender_Node
{
  private $_text;

  public function tokenize_content(Lavender_Content $content)
  {
    $content->consume_next(); // the '|'
    # the rest of the line should just be text
    $text = $content->consume_until("\n");
    $this->_text = ltrim($text);
  }

  public function set_text($text)
  {
    $this->_text = $text;
  }

  public function compile(array $scope)
  {
    return $this->_text;
  }
}

Lavender::register_extension('text', 'Lavender_Extension_Text', array('|'));
