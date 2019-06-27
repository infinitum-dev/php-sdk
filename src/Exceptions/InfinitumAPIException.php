<?php

namespace Fyi\Infinitum\Exceptions;

use Exception;

class InfinitumAPIException extends Exception
{
  protected $type = "InfinitumAPIException";
  protected $body;
  protected $status = 500;
  protected $message = "API Error";

  public function __construct($body = [])
  {
    if (isset($body->message)) {
      $this->message = $body->message;
    }

    if (isset($body->type)) {
      $this->type = $body->type;
    }

    if (isset($body->status)) {
      $this->status = $body->status;
    }

    parent::__construct($this->message, $this->code, null);
    $this->body = $body;
  }

  public function getType()
  {
    return "InfinitumAPIException";
  }

  public function getBody()
  {
    return $this->body;
  }
}
