<?php

class Jade_Extension_Text extends Jade_Node
{
  private $_text;

  public function tokenize_content(Jade_Content $content)
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

  public function compile()
  {
    return $this->_text;
  }
}

Jade::register_extension('text', 'Jade_Extension_Text', array('|'));
