<?php

class Lavender_Helper_Constant
{
  public function execute($string)
  {
    return constant($string);
  }
}

Lavender::register_helper('constant', 'Lavender_Helper_Constant');
