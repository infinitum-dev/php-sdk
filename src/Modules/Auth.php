<?php

namespace Fyi\Infinitum\Modules;

use Fyi\Infinitum\Infinitum;
use function GuzzleHttp\json_encode;

class Auth extends Infinitum
{
   protected $rest;

   public function __construct($rest)
   {
      $this->rest = $rest;
   }

   public function biometric($input)
   {
      try {
         $data = [];
         if (isset($input["photo"])) {
            $data["photo64"] = "data:" . $input["photo"]->getMimeType() . ";base64," . base64_encode(file_get_contents($input["photo"]));
         } else if (isset($input["photo64"])) {
            $data["photo64"] = $input["photo64"];
         }

         if (isset($input["device"])) {
            $data["device"] = $input["device"];
         }

         if (isset($input["device_ip"])) {
            $data["device_ip"] = $input["device_ip"];
         }

         if (isset($input["device_mac_address"])) {
            $data["device_mac_address"] = $input["device_mac_address"];
         }

         if (isset($input["device_mac_address_value"])) {
            $data["device_mac_address_value"] = $input["device_mac_address_value"];
         }

         if (isset($input["action"])) {
            $data["action"] = $input["action"];
         }

         if (isset($input["proximity"])) {
            $data["proximity"] = $input["proximity"];
         }

         if (isset($input["data"])) {
            $data["data"] = $input["data"];
         }

         return $this->rest->post('auth/biometric', $data);
      } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
         throw $exc;
      } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
         throw $exc;
      } catch (\Exception $exc) {
         throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
      }
   }

   public function code($input)
   {
      try {
         $data = [];
         if (isset($input["used_codes"])) {
            $data["used_codes"] = $input["used_codes"];
         }
         if (isset($input["device_mac_address"])) {
            $data["device_mac_address"] = $input["device_mac_address"];
         }

         return $this->rest->post('auth/code', $data);
      } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
         throw $exc;
      } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
         throw $exc;
      } catch (\Exception $exc) {
         throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
      }
   }

   public function login($input)
   {
      try {
         $data = [];
         if (isset($input["email"])) {
            $data["email"] = $input["email"];
         }

         if (isset($input["password"])) {
            $data["password"] = $input["password"];
         }

         return $this->rest->post('auth', $data);
      } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
         throw $exc;
      } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
         throw $exc;
      } catch (\Exception $exc) {
         throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
      }
   }
}
