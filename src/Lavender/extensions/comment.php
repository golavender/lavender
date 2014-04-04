<?php

class Lavender_Extension_Comment extends Lavender_Node
{
  public $text_children_only = TRUE;

  public function tokenize_content(Lavender_Content $content)
  {
    $content->consume_until("\n");
  }

  public function compile(array &$scope)
  {
    return '';
  }
}

Lavender::register_extension('comment', 'Lavender_Extension_Comment', array('//'));
