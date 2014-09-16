<?php

class Lavender_Extension_Else extends Lavender_Node
{
  private $_previous;

  protected $_delimiter = '';

  public function tokenize_content(Lavender_Content $content)
  {
    $content->consume_until("\n"); // the 'else'

    $siblings = $this->get_parent()->get_children();

    if (count($siblings) < 2) {
      throw new Lavender_Exception($content, 'else node used with no previous conditional');
    }

    $before_me = $siblings[count($siblings)-2];

    if (!method_exists($before_me, 'is_truthy')) {
      throw new Lavender_Exception($content, 'else node used after something that has no boolean state');
    }

    $this->_previous = $before_me;
  }

  public function _compile(array &$scope)
  {
    if (!$this->_previous->is_truthy($scope)) {
      return parent::_compile($scope);
    } else {
      return '';
    }
  }
}

Lavender::register_extension('else', 'Lavender_Extension_Else', array('else'));
