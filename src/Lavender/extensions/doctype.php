<?php

class Lavender_Extension_Doctype extends Lavender_Node
{
  private $_type = 'html';
  private $_types = array(
    'html'         => '<!DOCTYPE html>',
    'transitional' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
    'strict'       => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',
    'frameset'     => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">',
    '1.1'          => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">',
    'basic'        => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.1//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic11.dtd">',
    'mobile'       => '<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.2//EN" "http://www.openmobilealliance.org/tech/DTD/xhtml-mobile12.dtd">',
  );

  public function tokenize_content(Lavender_Content $content)
  {
    $content->consume_until(" \n"); // the 'doctype'
    $content->consume_whitespace();

    if ($content->peek() !== "\n") {
      $this->_type = trim($content->consume_until("\n"));
    }
  }

  public function _compile(array &$scope)
  {
    if (isset($this->_types[$this->_type])) {
      return $this->_types[$this->_type];
    }
    else {
      return '';
    }
  }
}

Lavender::register_extension('doctype', 'Lavender_Extension_Doctype', array('doctype', '!!!'));
