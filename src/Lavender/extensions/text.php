<?php

class Lavender_Extension_Text extends Lavender_Node
{
  private $_bits = array();

  public function tokenize_content(Lavender_Content $content)
  {
    if ($content->peek() == '|') {
      $content->consume_next(); // the '|'
    }

    # the rest of the line should just be text
    while ($content->peek() !== "\n" && $content->peek()) {
      $this->_bits[] = $content->consume_until("#\n");

      if ($content->peek(2) == '#{') {
        $content->consume_next(2); // the '#{'

        $expression = Lavender::get_extension_by_name('expression');
        $expression->tokenize_content($content);
        $this->_bits[] = $expression;

        if ($content->peek() == '}') {
          $content->consume_next(); // the '}'
        } else {
          throw new Lavender_Exception($content, 'found "#{" in string with no matching "}"');
        }
      }
    }
  }

  public function compile(array &$scope)
  {
    $text = '';

    foreach($this->_bits as $bit) {
      if (gettype($bit) == 'string') {
        $text .= $bit;
      } else {
        $text .= $bit->compile($scope);
      }
    }

    return $text;
  }
}

Lavender::register_extension('text', 'Lavender_Extension_Text', array('|'));
