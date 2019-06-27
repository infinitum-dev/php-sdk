<?php

namespace Fyi\Infinitum\Tests;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Config.php';

class UserTest extends TestCase
{

  protected $user;

  protected function setUp(): void
  {
    $config = new Config();
    $this->user = $config->getInfinitum()->user();
  }

  public function testCreate()
  {
    $input = [
      "name" => "Test User",
      "email" => "test.user@fyi.pt",
      "password" => "testuser123"
    ];
    try {
      # Test to create a user 
      $user = $this->user->register($input);
      $this->assertTrue($user != null);
      # -----------------------------------------------------------------------

      # Test to create the same user again
      try {
        $user = $this->user->register($input);
      } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
        # Should have an error as response
        $error = $exc->getBody();
        $this->assertTrue(isset($error->status));
      }
      # -----------------------------------------------------------------------

      # Test to delete the previously created user
      $delete = $this->user->deleteUser(["id" => $user["body"]->id]);
      $this->assertTrue($delete != null);
      # -----------------------------------------------------------------------

    } catch (\Throwable $th) {
      var_dump($th->getMessage());
    }
  }
}
