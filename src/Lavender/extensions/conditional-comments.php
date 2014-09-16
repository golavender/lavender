<?php

class Lavender_Extension_Conditional_Comment extends Lavender_Node
{
  private $_version;
  private $_comparison;

  public function tokenize_content(Lavender_Content $content)
  {
    $content->consume_next(2); // the 'IE'

    $bits = explode(' ', trim($content->consume_until("\n")));

    $bit = array_shift($bits);

    if (is_numeric($bit)) {
      $this->_version = $bit;
    }
    elseif($bit) {
      $this->_comparison = $bit;

     $bit = array_shift($bits);

      if (is_numeric($bit)) {
        $this->_version = $bit;
      }
      else {
        throw new Lavender_Exception($content, 'malformed conditional comment');
      }

    }
  }

  public function _compile(array &$scope)
  {
    $output = "<!--[if {$this->_comparison} IE {$this->_version}]>";
    $output .= parent::_compile($scope);
    $output .= "<![endif]-->";

    return $output;
  }
}

Lavender::register_extension('conditional-comment', 'Lavender_Extension_Conditional_Comment', array('IE'));
