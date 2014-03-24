<?php

class Jade_Extension_Block extends Jade_Node
{
  private $_mode;
  private $_name;

  public function tokenize_content(Jade_Content $content)
  {
    $content->consume_until(" "); // the 'block'

    $this->_name = trim($content->consume_until("\n"));

    if (!$this->_name) {
      throw new Jade_Exception($content, 'blocks need a name yo');
    }
  }

  public function compile(array $scope)
  {
    if ($this->_mode !== 'definition') {

      if (isset($scope[$this->get_block_id()])) {
        $this->set_children($scope[$this->get_block_id()]);
      }

      return parent::compile($scope);
    }
  }

  public function set_mode_definition()
  {
    $this->_mode = 'definition';
  }

  public function get_block_id()
  {
    return 'jade_block_node_contents_'.sha1($this->_name);
  }
}

Jade::register_extension('block', 'Jade_Extension_Block', array('block'));
