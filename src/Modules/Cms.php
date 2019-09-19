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

	public function getTemplate($session_user, $current_url, $device_type = "web")
	{
		try {
			$url = $this->parseUrl($current_url);
			if (!is_array($url)) {
				if ($url === "home") {
					try {
						$template = $this->rest->get('cms/v4/templates/home');
					} catch (\Throwable $th) {
						throw new \Exception("Home template not found.", 404);
					}
				} else if ($url === "404") {
					try {
						$template = $this->rest->get('cms/v4/templates/error');
					} catch (\Throwable $th) {
						throw new \Exception("Error template not found.", 404);
					}
				}
				$page = $url;
			} else {
				if (!isset($url["type_id"])) throw new \Exception("Missing Type.", 400);
				if (isset($url["content_id"])) {
					$page = 'contents_' . $url["content_id"];
					try {
						$template = $this->rest->get('cms/v4/templates/page/' . $page);
					} catch (\Throwable $th) {
						$template = $this->rest->get('cms/v4/templates/contents');
					}
				} else {
					$page = 'types_' . $url["type_id"];
					try {
						$template = $this->rest->get('cms/v4/templates/page/' . $page);
					} catch (\Throwable $th) {
						$template  = $this->rest->get('cms/v4/templates/types');
					}
				}
			}
			$blocks = $this->getBlocksByTemplate($template["id"], ["page" => $page]);
			if (empty($blocks)) {
				$blocks = $this->getBlocksByTemplate($template["id"], ["page" => $page . "_all"]);
			}
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
			return $template;
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
		if ($url == null) {
			$url = "";
		}

		if (substr($url, 0, 1) === "/") {
			$url = substr($url, 1, strlen($url));
		}

		# 0 - type
		# 1 - content
		$page = [];
		if ($url === "/" || $url === "") return "home";
		else if ($url === "/404" || $url === "404") return "404";
		else {
			$ex = explode("/", $url);
			if (count($ex) >= 2) {
				$page = ["type" => $ex[0], "page" => $ex[1]];
			} else if (count($ex) == 1) {
				$page = ["type" => $ex[0]];
			}
		}
		$final = [];
		if (isset($page["type"])) {
			$type = $this->rest->get('cms/v4/types/' . $page["type"]);
			$final["type_id"] = $type["id"];
			if (isset($page["page"])) {
				$content = $this->rest->get('cms/v4/contents/' . $page["page"]);
				$final["content_id"] = $content["id"];
			}
		}
		return $final;
	}
}
