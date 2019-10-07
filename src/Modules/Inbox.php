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

	public function storeMessageBlock($input)
	{ }

	public function storeMessage($input)
	{
		try {
			$data = [];

			if (isset($input["thread_id"])) {
				//check content
				$data["thread_id"] = $input["thread_id"];
			} else {
				$subject = "Sem assunto.";
				if (isset($input["subject"])) {
					$subject = $input["subject"];
				}

				$thread = $this->rest->post('inbox/threads', [
					"subject" => $subject
				]);

				$type = $this->rest->get('cms/v4/types/inbox');
				$content = $this->createContent([
					"type_id" => $type["id"],
					"languages" =>
					[
						[
							"language_id" => 1,
							"name" => $subject,
							"alias" => strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $subject))),
							"description" => $subject,
							"body" => $input["body"]
						]
					]
				]);
				dd($content);
				$data["thread_id"] = $thread->id;
			}

			$data["body"] = $input["body"];
			$data["sender_id"] = $input["sender_id"];
			$data["users"] = $input["users"];



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
			dd($exc);
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

	public function getThreads()
	{
		try {
			$thread = $this->rest->get('inbox/threads');
			dd($thread);
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
			$thread = $this->rest->get('inbox/messages/user/' . $user_id);
			dd($thread);
		} catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
			throw $exc;
		} catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
			throw $exc;
		} catch (\Exception $exc) {
			throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
		}
	}

	public function getUserThreads($user_id)
	{
		try {
			return $this->rest->get('inbox/threads/user/' . $user_id);
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
}
