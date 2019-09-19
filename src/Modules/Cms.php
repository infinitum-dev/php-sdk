<?php

namespace Fyi\Infinitum\Modules;

use Fyi\Infinitum\Infinitum;

class Cms extends Infinitum
{
	protected $rest;

	public function __construct($rest)
	{
		$this->rest = $rest;
	}

	public function getPage($session_user, $current_url, $device_type = "web", $params = [])
	{
		try {
			if (empty($session_user) || $session_user == null) {
				$session_user = "anon";
			}
			$user_module = $this->user();
			if ($session_user && isset($session_user["id"])) {
				$user = $user_module->getUser($session_user["id"]);
			} else {
				throw new \Exception("Invalid session user.", 400);
			}
			return $this->getTemplate($user, $current_url, $device_type);
		} catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
			throw $exc;
		} catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
			throw $exc;
		} catch (\Exception $exc) {
			throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
		}
	}

	public function getTemplate($session_user, $url, $device_type = "web")
	{
		try {
			$page = $this->parseUrl($url);

			$templates = $this->rest->get('cms/v4/templates?page=' . $page);
			if (empty($templates)) throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException("No templates found for current page.", 404);

			$data = [];
			foreach ($templates as $template) {
				$blocks_page = $this->getBlocksByTemplate($template["id"], ["page" => $page]);
				$blocks_all = $this->getBlocksByTemplate($template["id"], ["page" => 'all']);
				$blocks = array_merge($blocks_page, $blocks_all);

				$newpos = [];
				foreach ($template["positions"] as $position) {
					foreach ($blocks as $block) {
						if ($this->blockHasUserRoles($block, $session_user)) {
							$block = $this->blockHasTemplatePosition($block, $template["id"], $position);
							if ($block) {
								unset($block["pages"]);
								unset($block["templates"]);
								unset($block["roles"]); #this
								unset($block["created_at"]);
								unset($block["updated_at"]);
								$newpos[$position][] = $block;
							}
						}
					}
					if (isset($newpos[$position])) {
						usort($newpos[$position], function ($a, $b) {
							return $a["orderposition"] > $b["orderposition"];
						});
					}
				}
				$template["positions"] = $newpos;
				$data[] = $template;
			}
			return $data;
		} catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
			throw $exc;
		} catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
			throw $exc;
		} catch (\Exception $exc) {
			throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
		}
	}

	public function blockHasTemplatePosition($block, $template_id, $position)
	{
		foreach ($block["templates"] as $t) {
			if ($t["template_id"] === $template_id && $t["position"] === $position) {
				$block["orderposition"] = $t["orderposition"];
				return $block;
			}
		}
		return null;
	}

	public function blockHasUserRoles($block, $session_user)
	{
		if (!is_array($session_user) && $session_user == "anon") return true;
		foreach ($block["roles"] as $r) {
			foreach ($session_user["roles"] as $role) {
				if ($r === $role["id"]) return true;
			}
		}
		return false;
	}

	public function getBlocksByTemplate($template_id, $params = [])
	{
		try {
			$params["template"] = $template_id;
			$url = http_build_query($params, '&amp;');
			return $this->rest->get('cms/v4/blocks?' . $url);
		} catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
			throw $exc;
		} catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
			throw $exc;
		} catch (\Exception $exc) {
			throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
		}
	}

	public function parseUrl($url)
	{
		# 0 - type
		# 1 - content
		if ($url === "/" || $url === "") return "home";

		$ex = explode("/", $url);
		$page = "";
		if (isset($ex[0])) {
			if ($ex[0] === "")
				$page = "home";
			else
				$page = $ex[0];

			if (isset($ex[1])) {
				$page = $ex[1];
			}
		}
		return $page;
	}
}
