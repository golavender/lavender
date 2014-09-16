<?php

class Lavender_Extension_Comment extends Lavender_Node
{
  public $text_children_only = TRUE;

  private $_comment;

  public function tokenize_content(Lavender_Content $content)
  {
    $content->consume_next(2); // the '//'

    if ($content->peek() == '-') {
      $this->_output = FALSE;
      $content->consume_next(); // the '-'
    }

    $this->_comment = $content->consume_until("\n");
  }

  public function _compile(array &$scope)
  {
    return "<!--{$this->_comment}-->";
  }
}

Lavender::register_extension('comment', 'Lavender_Extension_Comment', array('//'));
