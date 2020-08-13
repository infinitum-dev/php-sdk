<?php

namespace Fyi\Infinitum\Modules;

use Fyi\Infinitum\Infinitum;

class Inbox extends Infinitum
{
	protected $rest;

	public function __construct($rest)
	{
		$this->rest = $rest;
	}

	public function storeThread($input)
	{
		try {
			return $this->rest->post('inbox/threads', $input);
		} catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
			throw $exc;
		} catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
			throw $exc;
		} catch (\Exception $exc) {
			throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
		}
    }


	public function updateThread($id, $input)
	{
		try {
			return $this->rest->put('inbox/threads/'.$id, $input);
		} catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
			throw $exc;
		} catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
			throw $exc;
		} catch (\Exception $exc) {
			throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
		}
	}

	public function storeMessage($input)
	{
		try {
            $data = [];
			if (isset($input["thread_id"])) {
				//check content
				$data["thread_id"] = $input["thread_id"];
			} else {
                $data["subject"] = "Sem assunto.";
				if (isset($input["subject"]))
                    $data["subject"] = $input["subject"];

                $data["creator_id"] = $input["creator_id"];

				if (isset($input["groups"]))
                    $data["groups"] = $input["groups"];
                else
                    $data["groups"] = [];
			}

			if(isset($input["require_answer"]))
				$data["require_answer"] = $input["require_answer"];

			$data["body"] = $input["body"];
			$data["sender_id"] = $input["sender_id"];
			$data["to"] = $input["to"];

            if (isset($input["message_type"]))
				$data["message_type"] = $input["message_type"];
			if (isset($input["message_type_id"]))
				$data["message_type_id"] = $input["message_type_id"];

			if (isset($input["attachments"])) {
				$data["attachments"] = $input["attachments"];
			}

			return  $this->rest->post('inbox/messages', $data);
		} catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
			throw $exc;
		} catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
			throw $exc;
		} catch (\Exception $exc) {
			throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
		}
	}


	public function getMessage($id)
	{
		try {
			return  $this->rest->get('inbox/messages/'. $id);
		} catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
			throw $exc;
		} catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
			throw $exc;
		} catch (\Exception $exc) {
			throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
		}
	}

	public function updateMessage($id, $input)
	{
		try {
            $data = [];
			if (isset($input["thread_id"]))
				$data["thread_id"] = $input["thread_id"];

			if (isset($input["sender_id"]))
				$data["sender_id"] = $input["sender_id"];

			if (isset($input["body"]))
				$data["body"] = $input["body"];

			if (isset($input["message_type"]))
				$data["message_type"] = $input["message_type"];

			if (isset($input["message_type_id"]))
				$data["message_type_id"] = $input["message_type_id"];

			if (isset($input["attachments"]))
				$data["attachments"] = $input["attachments"];

			return  $this->rest->put('inbox/messages/'. $id, $data);
		} catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
			throw $exc;
		} catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
			throw $exc;
		} catch (\Exception $exc) {
			throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
		}
	}

	public function markRead($input)
	{
		try {
			return $this->rest->post('inbox/messages/read', $input);
		} catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
			throw $exc;
		} catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
			throw $exc;
		} catch (\Exception $exc) {
			throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
		}
	}

	public function getThreadPage($thread_id)
	{
		try {
			$thread = $this->rest->get('inbox/threads');
		} catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
			throw $exc;
		} catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
			throw $exc;
		} catch (\Exception $exc) {
			throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
		}
	}

	public function getThread($id)
	{
		try {
			return $this->rest->get('inbox/threads/'. $id);
		} catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
			throw $exc;
		} catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
			throw $exc;
		} catch (\Exception $exc) {
			throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
		}
	}

	public function getThreads()
	{
		try {
			return $this->rest->get('inbox/threads');
		} catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
			throw $exc;
		} catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
			throw $exc;
		} catch (\Exception $exc) {
			throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
		}
	}

	public function copyToThread($input)
	{
		try {
            return $this->rest->post('inbox/messages/copy', $input);
		} catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
			throw $exc;
		} catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
			throw $exc;
		} catch (\Exception $exc) {
			throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
		}
	}

	public function getMessageBlock($message)
	{
		# code...
	}

	public function getUserMessages($user_id)
	{
		try {
			return $this->rest->get('inbox/messages/user/' . $user_id);
		} catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
			throw $exc;
		} catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
			throw $exc;
		} catch (\Exception $exc) {
			throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
		}
    }

    public function getUserThreads($user_id, $params)
	{
		try {
            if(isset($params["page"]))
                return $this->rest->get('inbox/threads/user/' . $user_id, $params)["data"];

			return $this->rest->get('inbox/threads/user/' . $user_id, $params);
		} catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
			throw $exc;
		} catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
			throw $exc;
		} catch (\Exception $exc) {
			throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
		}
    }

	public function getThreadMessages($thread_id)
	{
		try {
			return $this->rest->get('inbox/messages/thread/' . $thread_id);
		} catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
			throw $exc;
		} catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
			throw $exc;
		} catch (\Exception $exc) {
			throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
		}
	}

	public function isUser($message, $user)
	{
		if($message["sender_id"] === $user["id"]) return true;
		if(in_array($user["id"], array_column($message["users"], "id"))) return true;
		foreach ($user["relations"] as $r) {
			if(in_array($r["id"], array_column($message["users"], "id"))) return true;
		}
		return false;
    }

    public function archiveThread($thread_id, $user_id)
    {
		try {
			return $this->rest->put('inbox/threads/' . $thread_id. '/archive/user/'. $user_id);
		} catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
			throw $exc;
		} catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
			throw $exc;
		} catch (\Exception $exc) {
			throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
		}
    }
}
