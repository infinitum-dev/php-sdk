<?php

namespace Fyi\Infinitum\Tests;

class Config
{
  private $workspace;
  private $app_secret;
  private $app_key;
  private $app_token;
  private $infinitum;

  public function __construct($workspace = "localhost:9001", $app_key = "ee83eb4f-21ba-4dea-8bce-d7236b5857d2", $app_secret = "QsQG7hZSbobef9mF0wxSM0W05d1wWehhn8l2BlQavn0=", $app_token = "asd")
  {
    $this->workspace = $workspace;
    $this->app_secret = $app_secret;
    $this->app_key = $app_key;
    $this->app_token = $app_token;

    $this->infinitum = new \Fyi\Infinitum\Infinitum;
    $response = $this->infinitum->init($this->workspace, $this->app_token, $this->app_key, $this->app_secret);
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
