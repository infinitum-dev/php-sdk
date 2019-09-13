<?php

namespace Fyi\Infinitum\Tests;

class Config
{
  private $workspace;
  private $identity;
  private $app_key;
  private $app_token;
  private $infinitum;

  public function __construct($workspace = "localhost:9001", $identity = "identity_test", $app_token = "asd")
  {
    $this->workspace = $workspace;
    $this->identity = $identity;
    $this->app_token = $app_token;

    $this->infinitum = new \Fyi\Infinitum\Infinitum($this->workspace, $this->app_token, $this->identity);
    $response = $this->infinitum->init();
    if ($response) {
      if (isset($response["access_token"])) {
        $this->access_token = $response["access_token"];
      } else {
        throw new \Exception("Invalid config.", 400);
      }
    } else {
      throw new \Exception("Connection Error", 500);
    }

    $response = $this->infinitum->setAccessToken($this->access_token, $this->app_token);
  }

  public function getInfinitum()
  {
    return $this->infinitum;
  }
}
