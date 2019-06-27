<?php

namespace Fyi\Infinitum\Exceptions\SDK;
use Fyi\Infinitum\Exceptions\InfinitumSDKException;

class MissingTokenException extends InfinitumSDKException
{
  protected $message = "Missing Authorization token.";
  protected $code = 400;
  protected $type = "MissingTokenException";

  public function __construct()
  {
      parent::__construct("Missing Authorization token.", $this->code, null);
  }

  public function getType() {
      return $this->type;
  }
}
