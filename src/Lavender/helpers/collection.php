<?php

class Lavender_Helper_Collection
{
  public function execute($name)
  {
    return Lavender_Extension_Collection::get($name);
  }
}

Lavender::register_helper('collection', 'Lavender_Helper_Collection');
