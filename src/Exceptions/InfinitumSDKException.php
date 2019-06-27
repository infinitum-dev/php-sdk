<?php

namespace Fyi\Infinitum\Exceptions;
use Exception; 

class InfinitumSDKException extends Exception
{
  protected $type = "InfinitumSDKException";

  public function __construct($message = "SDK Error", $code = 500, $prev = null)
  {
    parent::__construct($message, $code, $prev);
  }
  
  public function getType()
  {
    return "InfinitumSDKException";
  }
}
