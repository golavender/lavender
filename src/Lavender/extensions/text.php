<?php

class Lavender_Extension_Text extends Lavender_Node
{
  private $_bits = array();
  private $_stop_characters = array("\n");

  protected $_delimiter = ' ';

  public function tokenize_content(Lavender_Content $content)
  {
    if ($content->peek() == '|') {
      $content->consume_next(); // the '|'
    }

    while ($content->peek() !== '' && !in_array($content->peek(), $this->_stop_characters)) {
      $this->_bits[] = $content->consume_until(implode('', array_merge($this->_stop_characters, array('#'))));

      if ($content->peek(2) == '#{') {
        $content->consume_next(2); // the '#{'

        $expression = Lavender::get_extension_by_name('expression');
        $expression->tokenize_content($content);
        $this->_bits[] = $expression;

        if ($content->peek() == '}') {
          $content->consume_next(); // the '}'
        }
        else {
          throw new Lavender_Exception($content, 'found "#{" in string with no matching "}"');
        }
      }
      elseif($content->peek() == '#') {
        $this->_bits[] = $content->consume_next(); // the '#'
      }
    }
  }

  public function add_stop($character)
  {
    $this->_stop_characters[] = $character;

    return $this;
  }

  public function _compile(array &$scope)
  {
    $text = '';

    foreach($this->_bits as $bit) {
      if (gettype($bit) == 'string') {
        $text .= $bit;
      }
      else {
        $text .= $bit->compile($scope);
      }
    }

    $text = str_replace('\n', "\n", $text);
    $text = str_replace('\t', "\t", $text);

    return $text;
  }
}

Lavender::register_extension('text', 'Lavender_Extension_Text', array('|'));
