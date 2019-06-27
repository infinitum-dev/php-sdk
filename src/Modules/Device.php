<?php

namespace Fyi\Infinitum\Modules;

use Fyi\Infinitum\Infinitum;

class Device  extends Infinitum
{
    protected $rest;

    public function __construct($rest)
    {
        $this->rest = $rest;
    }

    public function registerDevice($input)
    {
        try {
            $data = [];

            if (isset($input["mac_address"])) {
                $data["mac_address"] = $input["mac_address"];
            }

            if (isset($input["ip"])) {
                $data["ip"] = $input["ip"];
            }

            if (isset($input["identity"])) {
                $data["identity"] = $input["identity"];
            }

            if (isset($input["app_id"])) {
                $data["app_id"] = $input["app_id"];
            }

            if (isset($input["state_id"])) {
                $data["state_id"] = $input["state_id"];
            }

            if (isset($input["device_type"])) {
                $data["device_type"] = $input["device_type"];
            } else if (isset($input["device_type_id"])) {
                $data["device_type_id"] = $input["device_type_id"];
            }

            if (isset($input["locations"])) {
                $data["locations"] = $input["locations"];
            }

            if (isset($input["users"])) {
                $data["users"] = $input["users"];
            }

            if (isset($input["value"])) {
                $data["value"] = $input["value"];
            }

            if (isset($input["licensed"])) {
                $data["licensed"] = $input["licensed"];
            }

            if (isset($input["app_version"])) {
                $data["app_version"] = $input["app_version"];
            }

            return $this->rest->post('devices', $data);
        } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
            throw $exc;
        } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
            throw $exc;
        } catch (\Exception $exc) {
            throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
        }
    }

    public function registerDeviceUser($input)
    {
        try {
            $data = [];

            if (isset($input["device_mac_address"])) {
                $data["device_mac_address"] = $input["device_mac_address"];
            }

            if (isset($input["user_email"])) {
                $data["user_email"] = $input["user_email"];
            }

            if (isset($input["device_id"])) {
                $data["device_id"] = $input["device_id"];
            }

            if (isset($input["user_id"])) {
                $data["user_id"] = $input["user_id"];
            }
            return $this->rest->post('devices/user', $data);
        } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
            throw $exc;
        } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
            throw $exc;
        } catch (\Exception $exc) {
            throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
        }
    }

    public function deleteDevice($input)
    {
        try {
            if (isset($input["id"])) {
                return $this->rest->delete('devices/' . $input["id"]);
            } else {
                throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException("Missing Device info.");
            }
        } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
            throw $exc;
        } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
            throw $exc;
        } catch (\Exception $exc) {
            throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
        }
    }
}
