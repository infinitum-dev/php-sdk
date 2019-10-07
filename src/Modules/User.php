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

            if (isset($input["phone"])) {
                $data["phone"] = $input["phone"];
            }

            if (isset($input["apis"])) {
                $data["apis"] = $input["apis"];
            }

            if (isset($input["photo"])) {
                if (gettype($input["photo"]) === "resource") {
                    $data["photo64"] =   "data:image/png;base64," . base64_encode(stream_get_contents($input["photo"]));
                } else {
                    $data["photo64"] =   "data:image/png;base64," . base64_encode(file_get_contents($input["photo"]));
                }
            } else if (isset($input["photo64"])) {
                $data["photo64"] = $input["photo64"];
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

            if (isset($input["relations"])) {
                $data["relations"] = $input["relations"];
            }

            if (isset($input["notification"])) {
                $data["notification"] = $input["notification"];
            }

            return $this->rest->post('users', $data);
        } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
            throw $exc;
        } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
            throw $exc;
        } catch (\Exception $exc) {
            dd($exc);
            throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
        }
    }

    public function update($id, $input)
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

            if (isset($input["phone"])) {
                $data["phone"] = $input["phone"];
            }

            if (isset($input["apis"])) {
                $data["apis"] = $input["apis"];
            }

            if (isset($input["photo"])) {
                if (gettype($input["photo"]) === "resource") {
                    $data["photo64"] =   "data:image/png;base64," . base64_encode(stream_get_contents($input["photo"]));
                } else {
                    $data["photo64"] =   "data:image/png;base64," . base64_encode(file_get_contents($input["photo"]));
                }
            } else if (isset($input["photo64"])) {
                $data["photo64"] = $input["photo64"];
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

            if (isset($input["relations"])) {
                $data["relations"] = $input["relations"];
            }

            if (isset($input["notification"])) {
                $data["notification"] = $input["notification"];
            }

            return $this->rest->put('users/' . $id, $data);
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
                if (gettype($input["photo"]) === "resource") {
                    $data["photo64"] =   "data:image/png;base64," . base64_encode(stream_get_contents($input["photo"]));
                } else {
                    $data["photo64"] =   "data:image/png;base64," . base64_encode(file_get_contents($input["photo"]));
                }
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

    public function getUsers($ids = null)
    {
        try {
            if (isset($ids)) {
                return $this->rest->get('users?ids=' . $ids);
            } else {
                return $this->rest->get('users');
            }
        } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
            throw $exc;
        } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
            throw $exc;
        } catch (\Exception $exc) {
            throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
        }
    }

    public function getUser($id)
    {
        try {
            return $this->rest->get('users/' . $id);
        } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
            throw $exc;
        } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
            throw $exc;
        } catch (\Exception $exc) {
            throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
        }
    }

    public function getByEmail($input)
    {
        try {
            if (isset($input["email"])) {
                $email = $input["email"];
            } else {
                throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException("Missing email", 400);
            }

            return $this->rest->get('users/' . $email);
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

    public function notify($input)
    {
        try {
            $data = [];
            if (isset($input["from"])) {
                $data["from"] = $input["from"];
            } else {
                $data["from"] = null;
            }

            if (isset($input["subject"])) {
                $data["subject"] = $input["subject"];
            }

            if (isset($input["content"])) {
                $data["content"] = $input["content"];
            }

            if (isset($input["lang"])) {
                $data["lang"] = $input["lang"];
            }

            if (isset($input["action"])) {
                $data["action"] = $input["action"];
            }


            if (isset($input["to"])) {
                $data["to"] = $input["to"];
            }

            if (isset($input["data"])) {
                $data["data"] = $input["data"];
            } else {
                $data["data"] = [];
            }

            return $this->rest->post('notifications/send', $data);
        } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
            throw $exc;
        } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
            throw $exc;
        } catch (\Exception $exc) {
            throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
        }
    }

    public function registerGroup($input)
    {
        try {
            $data = [];

            if (isset($input["name"])) {
                $data["name"] = $input["name"];
            }

            if (isset($input["alias"])) {
                $data["alias"] = $input["alias"];
            } else {
                $data["alias"] = str_slug($data["name"]);
            }

            if (isset($input["action"])) {
                $data["action"] = $input["action"];
            }

            if (isset($input["automatic"])) {
                $data["automatic"] = $input["automatic"];
            }

            return $this->rest->post('users/groups', $data);
        } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
            throw $exc;
        } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
            throw $exc;
        } catch (\Exception $exc) {
            throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
        }
    }

    public function getGroups()
    {
        try {
            return $this->rest->get('users/groups');
        } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
            throw $exc;
        } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
            throw $exc;
        } catch (\Exception $exc) {
            throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
        }
    }

    public function passwordemail($email, $callback_url)
    {
        try {
            $data = [];
            $data['email'] = $email;
            $data['callback_url'] = $callback_url;

            return $this->rest->post('users/password/email', $data);
        } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
            throw $exc;
        } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
            throw $exc;
        } catch (\Exception $exc) {
            throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
        }
    }

    public function passwordreset($password, $token)
    {
        try {
            $data = [];
            $data['password'] = $password;
            $data['token'] = $token;

            return $this->rest->post('users/password/reset', $data);
        } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
            throw $exc;
        } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
            throw $exc;
        } catch (\Exception $exc) {
            throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
        }
    }

    public function getByToken($token = "")
    {
        try {
            if (empty($token)) {
                throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException("Missing token", 400);
            }

            return $this->rest->get('users/token/' . $token);
        } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
            throw $exc;
        } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
            throw $exc;
        } catch (\Exception $exc) {
            throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
        }
    }

    public function destroyRelation($user_id, $related_id)
    {
        try {
            return $this->rest->delete('users/relations/' . $user_id . '/' . $related_id);
        } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
            throw $exc;
        } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
            throw $exc;
        } catch (\Exception $exc) {
            dd($exc);
            throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
        }
    }
}
