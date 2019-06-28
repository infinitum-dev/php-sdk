<?php

namespace Fyi\Infinitum\Tests;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Config.php';

class DeviceTest extends TestCase
{
  protected $device;

  protected function setUp(): void
  {
    $config = new Config();
    $this->device = $config->getInfinitum()->device();
  }

  public function testCreate()
  {
    $input = [
      "mac_address" => "FF:DD:CC:BB:AA:EE",
      "identity" => "test_identity"
    ];
    try {
      # Test to create a device 
      $device = $this->device->registerDevice($input);
      $this->assertTrue($device !== null);
      # -----------------------------------------------------------------------

      # Test to create the same device again
      try {
        $device = $this->device->registerDevice($input);
      } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
        # Should have an error as response
        $error = $exc->getBody();
        $this->assertTrue(isset($error->status));
      }
      # -----------------------------------------------------------------------
      $device_user_input = [
        "device_mac_address" => "FF:DD:CC:BB:AA:EE",
        "user_email" => "system.user@fyi.pt"
      ];
      # Test to create a device user 
      $device_user = $this->device->registerDeviceUser($device_user_input);
      $this->assertTrue($device_user !== null);
      # -----------------------------------------------------------------------

      # Test to delete the previously created device
      $delete = $this->device->deleteDevice(["id" => $device["id"]]);
      $this->assertTrue($delete !== null);
      # -----------------------------------------------------------------------
    } catch (\Throwable $th) {
      var_dump($th->getMessage());
    }
  }
}
