<?php

namespace Fyi\Infinitum\Modules;

use Fyi\Infinitum\Infinitum;

class User extends Infinitum
{
    protected $rest;

    public function __construct($rest)
    {
        $this->rest = $rest;
    }

    public function register($input)
    {
        try {
            if (isset($input["name"])) {
                $data["name"] = $input["name"];
            }

            if (isset($input["password"])) {
                $data["password"] = $input["password"];
            }

            if (isset($input["state"])) {
                $data["state"] = $input["state"];
            }

            if (isset($input["email"])) {
                $data["email"] = $input["email"];
            }

            if (isset($input["apis"])) {
                $data["apis"] = $input["apis"];
            }

            if (isset($input["photo"])) {
                $data["photo64"] =   "data:" . $input["photo"]->getMimeType() . ";base64," . base64_encode(file_get_contents($input["photo"]));
            }

            if (isset($input["birthdate"])) {
                $data["birthdate"] = $input["birthdate"];
            }

            if (isset($input["language"])) {
                $data["language"] = $input["language"];
            }

            if (isset($input["data"])) {
                $data["data"] = $input["data"];
            }

            if (isset($input["apps"])) {
                $data["apps"] = $input["apps"];
            }

            if (isset($input["links"])) {
                $data["links"] = $input["links"];
            }

            if (isset($input["roles"])) {
                $data["roles"] = $input["roles"];
            }

            if (isset($input["locations"])) {
                $data["locations"] = $input["locations"];
            }

            if (isset($input["groups"])) {
                $data["groups"] = $input["groups"];
            }

            if (isset($input["api_user_data"])) {
                $data["api_user_data"] = $input["api_user_data"];
            }

            if (isset($input["language"])) {
                $data["language"] = $input["language"];
            }

            return $this->rest->post('users', $data);
        } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
            throw $exc;
        } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
            throw $exc;
        } catch (\Exception $exc) {
            throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
        }
    }

    public function face($input)
    {
        try {
            $data = [];

            if (isset($input["photo"])) {
                $data["photo64"] =   "data:" . $input["photo"]->getMimeType() . ";base64," . base64_encode(file_get_contents($input["photo"]));
            } else if (isset($input["photo64"])) {
                $data["photo64"] = $input["photo64"];
            }
            return $this->rest->post('users/face', $data);
        } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
            throw $exc;
        } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
            throw $exc;
        } catch (\Exception $exc) {
            throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
        }
    }


    public function getUsers()
    {
        try {
            return $this->rest->get('users');
        } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
            throw $exc;
        } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
            throw $exc;
        } catch (\Exception $exc) {
            throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
        }
    }

    public function deleteUser($input)
    {
        try {
            if (isset($input["id"])) {
                return $this->rest->delete('users/' . $input["id"]);
            } else {
                throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException("Missing User info.");
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
