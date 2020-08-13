<?php

namespace Fyi\Infinitum\Modules;

use Fyi\Infinitum\Infinitum;

class Notification extends Infinitum
{
	protected $rest;

	public function __construct($rest)
	{
		$this->rest = $rest;
	}

	public function send($input)
	{
		try {
			$data = [];

			if (isset($input["action"])) {
				$data["action"] = $input["action"];
			}

			if (isset($input["to"])) {
				$data["to"] = $input["to"];
			}

			if (isset($input["content"])) {
				$data["content"] = $input["content"];
			}

			if (isset($input["lang"])) {
				$data["lang"] = $input["lang"];
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

	public function showNotifications()
	{
		try {
			return $this->rest->get('notifications');
		} catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
			throw $exc;
		} catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
			throw $exc;
		} catch (\Exception $exc) {
			throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
		}
	}

	public function subscribe($input)
	{
		try {
			$data = [];

			if (isset($input["user_id"])) {
				$data["user_id"] = $input["user_id"];
			}

			if (isset($input["notification_id"])) {
				$data["notification_id"] = $input["notification_id"];
			} else if (isset($input["notifiation"])) {
				$data["notification"] = $input["notification"];
			}

			return $this->rest->post('notifications/subscribe', $data);
		} catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
			throw $exc;
		} catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
			throw $exc;
		} catch (\Exception $exc) {
			throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
		}
	}


	public function subscribeMultiple($input)
	{
		try {
			$data = [];

			if (isset($input["user_id"])) {
				$data["user_id"] = $input["user_id"];
			}

			if (isset($input["notification_ids"])) {
				$data["notification_ids"] = $input["notification_ids"];
			} else if (isset($input["notifications"])) {
				$data["notifications"] = $input["notifications"];
			}

			return $this->rest->post('notifications/subscribe/multiple', $data);
		} catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
			throw $exc;
		} catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
			throw $exc;
		} catch (\Exception $exc) {
			throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
		}
	}

	public function unsubscribe($input)
	{
		try {
			$data = [];

			if (isset($input["user_id"])) {
				$data["user_id"] = $input["user_id"];
			}

			if (isset($input["notification_id"])) {
				$data["notification_id"] = $input["notification_id"];
			} else if (isset($input["notification"])) {
				$data["notification"] = $input["notification"];
			}

			return $this->rest->post('notifications/unsubscribe', $data);
		} catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
			throw $exc;
		} catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
			throw $exc;
		} catch (\Exception $exc) {
			throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
		}
	}


	public function unsubscribeMultiple($input)
	{
		try {
			$data = [];

			if (isset($input["user_id"])) {
				$data["user_id"] = $input["user_id"];
			}

			if (isset($input["notification_ids"])) {
				$data["notification_ids"] = $input["notification_ids"];
			} else if (isset($input["notifications"])) {
				$data["notifications"] = $input["notifications"];
			}

			return $this->rest->post('notifications/unsubscribe/multiple', $data);
		} catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
			throw $exc;
		} catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
			throw $exc;
		} catch (\Exception $exc) {
			throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
		}
	}
}
