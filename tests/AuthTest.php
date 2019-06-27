<?php
namespace Fyi\Infinitum\Tests;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Config.php';

class AuthTest extends TestCase
{
  protected $auth;
  protected $user;

  protected function setUp(): void
  {
    $config = new Config();
    $this->auth = $config->getInfinitum()->auth();
    $this->user = $config->getInfinitum()->user();
  }

  public function testLogin()
  {
    $input = [
      "email" => "system.user@fyi.pt",
      "password" => "fyi2011"
    ];

    # Regular login test
    try {
      $user = $this->auth->login($input);
      $this->assertTrue($user != null);
    } catch (\Exception $exc) {
      var_dump($exc->getBody());
    }

    try {
      $user_input = [
        "name" => "Test User",
        "email" => "test.user@fyi.pt",
        "password" => "testuser123"
      ];
      # Test to create a user 
      $new_user = $this->user->register($user_input);
      $this->assertTrue($new_user != null);
      # -----------------------------------------------------------------------

      # Regular login with new User
      $user_loggedin = $this->auth->login($user_input);
      $this->assertTrue($user_loggedin != null);

      # -----------------------------------------------------------------------

      # Test to delete the previously created user
      $delete_user = $this->user->deleteUser(["id" => $new_user["body"]->id]);
      $this->assertTrue($delete_user != null);

      # -----------------------------------------------------------------------

      # Test unauthorize on login with deleted user
      try {
        $user = $this->auth->login($user_input);
      } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
        # Should have an error as response

        $error = $exc->getBody();
        $this->assertTrue(isset($error->status) && $error->status === 401);
      }
      # -----------------------------------------------------------------------

    } catch (\Exception $th) {
      var_dump($th->getMessage());
    }
  }
}
