<?php

namespace Fyi\Infinitum\Utils;

class Response 
{
  protected $body;
  protected $success;

  public function __construct($body = [], $success = true)
  {
    $this->body = $body;
    $this->success = $success;
  }

  public function json() {
    $data = [];

    if($this->success)
      $data["body"] = $this->body;      
      
    else
      $data["error"] = $this->body;

    return $data;
  }
}
