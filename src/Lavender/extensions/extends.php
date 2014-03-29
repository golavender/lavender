<?php

class Lavender_Extension_Extends extends Lavender_Node
{
  private $_parent_view;
  private $_content;
  private $_blocks = array();

  public function tokenize_content(Lavender_Content $content)
  {
    $this->_content = $content;

    $content->consume_until(" "); // the 'extends'

    $path = trim($content->consume_until("\n"));

    $this->_parent_view = new Lavender_View($path);

    $parent_node = $this->get_parent();

    if ($parent_node instanceof Lavender_File) {
      $parent_node->post_tokenize_hook(array($this, 'validate'));
    }
    else {
      throw new Lavender_Exception($content);
    }
  }

  public function validate(Lavender_File $file)
  {
    $extends = Lavender::get_extension_by_name('extends');
    $block = Lavender::get_extension_by_name('block');

    $extend_cound = 0;
    foreach ($file->get_children() as $child) {

      if (get_class($child) == get_class($extends)) {
        $extend_count++;

        if ($extend_count > 1) {
          throw new Lavender_Exception($this->content, 'can only extend one parent');
        }
      }
      else if (get_class($child) == get_class($block)) {
        $this->_blocks[$child->get_block_id()] = $child->get_children();
        $child->set_mode_definition();
      }
      else {
        throw new Lavender_Exception($this->_content, 'templates that extend another can only have blocks in them');
      }
    }
  }

  public function compile(array $scope)
  {
    return $this->_parent_view->compile(array_merge($scope, $this->_blocks));
  }

  public function add_child($child)
  {
    throw new Exception('extends cannot have children');
  }
}

Lavender::register_extension('extends', 'Lavender_Extension_Extends', array('extends'));
