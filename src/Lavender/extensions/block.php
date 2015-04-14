<?php

class Lavender_Extension_Block extends Lavender_Node
{
  private $_mode;
  private $_name;

  protected $_delimiter = '';

  public function tokenize_content(Lavender_Content $content)
  {
    $content->consume_until(" "); // the 'block'

    $this->_name = trim($content->consume_until("\n"));

    if (!$this->_name) {
      throw new Lavender_Exception($content, 'blocks need a name yo');
    }
  }

  public function _compile(array &$scope)
  {
    if (isset($scope[$this->get_block_id()])) {
      $this->set_children($scope[$this->get_block_id()]);
    }
    if ($this->_output) {
      return parent::_compile($scope);
    }
  }

  public function set_mode_definition()
  {
    $this->_output = FALSE;
  }

  public function get_block_id()
  {
    return 'jade_block_node_contents_'.sha1($this->_name);
  }
}

Lavender::register_extension('block', 'Lavender_Extension_Block', array('block '));
