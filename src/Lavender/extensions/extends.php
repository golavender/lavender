<?php

class Lavender_Extension_Extends extends Lavender_Node
{
  private $_parent_view_path;
  private $_blocks = array();

  protected $_delimiter = '';

  public function tokenize_content(Lavender_Content $content)
  {
    $content->consume_until(" "); // the 'extends'

    $path = trim($content->consume_until("\n"));

    $this->_parent_view_path = $path;

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
    $extends      = Lavender::get_extension_by_name('extends');
    $block        = Lavender::get_extension_by_name('block');
    $extends_node = NULL;
    $extend_count = 0;

    foreach ($file->get_children() as $key => $child) {

      if (get_class($child) == get_class($extends)) {
        $extend_count++;

        if ($extend_count > 1) {
          throw new Lavender_Exception($file->get_content(), 'can only extend one parent');
        }

        // stash and remove so we can put it back at the end
        $extends_node = $child;
        $file->remove_child_at($key);
      }
      else if (get_class($child) == get_class($block)) {
        $this->_blocks[$child->get_block_id()] = $child->get_children();
        $child->set_mode_definition();
      }
      else if ($child->has_output()) {
        throw new Lavender_Exception($file->get_content(), 'templates that extend another cannot have output outside blocks');
      }
    }

    $file->add_child($extends_node);
  }

  public function _compile(array &$scope)
  {
    $parent_view = new Lavender_View($this->_parent_view_path);
    $scope       = array_merge($scope, $this->_blocks);
    $result      = $parent_view->compile($scope);
    $scope       = array_merge($parent_view->get(), $scope);

    return $result;
  }

  public function add_child($child)
  {
    throw new Exception('extends cannot have children');
  }
}

Lavender::register_extension('extends', 'Lavender_Extension_Extends', array('extends '));
