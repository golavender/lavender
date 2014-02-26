<?php

class Jade_Extension_If extends Jade_Node
{
  private $_expression;

  public function tokenize_content(Jade_Content $content)
  {
    $content->consume_until(" "); // the 'if'

    $this->_expression = Jade::get_extension_by_name('expression');
    $this->_expression->tokenize_content($content);
  }

  public function compile()
  {
    if ($this->_expression->is_truthy()) {
      return parent::compile();
    } else {
      return '';
    }
  }
}

Jade::register_extension('if', 'Jade_Extension_If', array('if'));
