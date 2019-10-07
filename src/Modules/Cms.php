<?php

namespace Fyi\Infinitum\Modules;

use Fyi\Infinitum\Infinitum;
use Fyi\Infinitum\Modules\Eshop;
use Fyi\Infinitum\Modules\User;

class Cms extends Infinitum
{
	protected $rest;

	public function __construct($rest)
	{
		$this->rest = $rest;
		$this->eshop = new Eshop($rest);
		$this->user = new User($rest);
	}

	public function getPage($session_user, $current_url, $device_type = "web", $params = [])
	{
		try {
			if (empty($session_user) || $session_user == null) {
				$session_user = "anon";
			}

			$user_module = $this->user();
			$user = null;
			if (isset($session_user["id"])) {
				$user = $user_module->getUser($session_user["id"]);
			}

			return $this->getTemplate($user, $current_url, $device_type, isset($params["lang"]) ? $params["lang"] : 1);
		} catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
			throw $exc;
		} catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
			throw $exc;
		} catch (\Exception $exc) {
			throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
		}
	}

	public function getTemplate($session_user, $current_url, $device_type = "web", $lang = null)
	{
		try {
			$url = $this->parseUrl($current_url, $lang);
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
						$template = $this->rest->get('cms/v4/templates/page/contents');
					}
				} else {
					$page = 'types_' . $url["type_id"];
					try {
						$template = $this->rest->get('cms/v4/templates/page/' . $page);
					} catch (\Throwable $th) {
						$template  = $this->rest->get('cms/v4/templates/page/types');
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

							$hasLang = false;
							if (isset($block["data"])) {
								foreach ($block["data"] as $key => $b) {
									if (!isset($b["language_id"])) {
										$hasLang = true;
										break;
									} else if ($b["language_id"] !== $lang) {
										unset($block["data"]["key"]);
									} else {
										$hasLang = true;
									}
								}
							} else {
								$hasLang = true;
							}

							if (!$hasLang) {
								continue;
							}

							if ($block["type"] == "content") {
								$block["data"]["objects"] = $this->getContents($block["data"], $url, $lang);
								if ($block["plugin"] === "inbox") {
									$inbox = $this->inbox();
									if ($session_user) {
										$user_threads = $inbox->getUserThreads($session_user["id"]);

										foreach ($block["data"]["objects"] as $key => $value) {
											try {
												$obj_id = $block["data"]["objects"][$key]["content"]["id"];
												$thread_content = $this->showContentThread($obj_id);
												$thread = current(array_filter($user_threads, function ($element) use ($thread_content) {
													return $element["id"] === $thread_content["infinitum_thread_id"];
												}));
												if ($thread) {
													foreach ($thread["messages"] as $key2 => $value) {
														$countRead = 0;
														$isRead = false;
														foreach ($value["users"] as $user) {
															if ($user["is_read"]) {
																$countRead += 1;
																if ($user["user_id"] === $session_user["id"]) {
																	$isRead = true;
																}
															}
														}
														$thread["messages"][$key2]["is_read"] = $isRead;
														$thread["messages"][$key2]["count_read"] = $countRead;
													}
													$block["data"]["objects"][$key]["messages"] = $thread["messages"];
												}
											} catch (\Throwable $th) { }
										}
									}
								}
								if (isset($url["content_id"])) {
									$template["object"] = $block["data"]["objects"];
								}
							}

							if ($block["type"] == "checkout") {
								$block["data"]["payment"] = $this->eshop->getPaymentMethods();
							}

							if (isset($block["data"]["fields"]))
								usort($block["data"]["fields"], array($this, "orderFields"));

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
		if ($session_user == null) return true;
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

	public function parseUrl($url, $lang = null)
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

	public function getContents($content, $url, $lang)
	{
		try {
			if ($content["url_dependence_limit"] == 1) {
				if (isset($url["content_id"])) {
					$response = $this->rest->get('cms/v4/contents?contents=' . $url["content_id"] . '&language_id=' . $lang . '&limit=' . $content["contents_limit"]);
					$final = [];
					if (!empty($response["data"])) {
						$obj = [];
						$obj["content"] = $response["data"][0]["languages"][0];
						$obj["fields"] = $this->getContentFields($response["data"][0]["fields"]);
						$obj["url"] =  $this->generateUrl($response["data"][0]["type"]["id"], $lang, $response["data"][0]["languages"][0]["alias"]);
						if (!empty($response["data"][0]["eshop_products"]))
							$obj["products"] = $this->getProducts($response["data"][0]["eshop_products"]);
						array_push($final, $obj);
					}
					return $final;
				} else if (isset($url["type_id"])) {
					$response = $this->rest->get('cms/v4/contents?types=' . $url["type_id"] . '&language_id=' . $lang . '&limit=' . $content["contents_limit"]);
					$final = [];

					usort($response["data"], array($this, "orderContent"));
					foreach ($response["data"] as $r) {
						$obj = [];
						$obj["content"] = $r["languages"][0];
						$obj["fields"] = $this->getContentFields($r["fields"]);
						$obj["url"] =  $this->generateUrl($r["type"]["id"], $lang, $r["languages"][0]["alias"]);
						if (!empty($r["eshop_products"]))
							$obj["products"] = $this->getProducts($r["eshop_products"]);
						array_push($final, $obj);
					}
					return $final;
				}
			} else if (!empty($content["contents"])) {
				$final = [];
				$response = $this->rest->get('cms/v4/contents?contents=' . implode(',', $content["contents"]) . '&language_id=' . $lang . '&limit=' . $content["contents_limit"]);
				usort($response["data"], array($this, "orderContent"));
				foreach ($response["data"] as $r) {
					$obj = [];
					$obj["content"] = $r["languages"][0];
					$obj["fields"] = $this->getContentFields($r["fields"]);
					$obj["url"] =  $this->generateUrl($r["type"]["id"], $lang, $r["languages"][0]["alias"]);
					if (!empty($r["eshop_products"]))
						$obj["products"] = $this->getProducts($r["eshop_products"]);
					array_push($final, $obj);
				}
				return $final;
			} else if (!empty($content["types"])) {
				$final = [];
				$response = $this->rest->get('cms/v4/contents?types=' . implode(',', $content["types"]) . '&language_id=' . $lang . '&limit=' . $content["contents_limit"]);
				usort($response["data"], array($this, "orderContent"));
				foreach ($response["data"] as $r) {
					$obj = [];
					$obj["content"] = $r["languages"][0];
					$obj["fields"] = $this->getContentFields($r["fields"]);
					$obj["url"] =  $this->generateUrl($r["type"]["id"], $lang, $r["languages"][0]["alias"]);
					if (!empty($r["eshop_products"]))
						$obj["products"] = $this->getProducts($r["eshop_products"]);
					array_push($final, $obj);
				}
				return $final;
			}
		} catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
			throw $exc;
		} catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
			throw $exc;
		} catch (\Exception $exc) {
			throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
		}
	}

	public function getContentFields($fields)
	{
		$final = [];
		foreach ($fields as $field) {
			$final[$field["type"]]["value"] = $field["pivot"]["value"];
			$final[$field["type"]]["orderfield"] = $field["orderfield"];
		}

		return $final;
	}

	public function getProducts($products)
	{
		$skus = array_column($products, "sku");
		return $this->eshop->getProducts(implode(",", $skus));
	}

	public function orderFields($a, $b)
	{
		return $a["orderfield"] - $b["orderfield"];
	}

	public function orderContent($a, $b)
	{
		return $a["ordercontent"] - $b["ordercontent"];
	}

	public function generateUrl($type, $lang, $content = "")
	{
		$type = $this->rest->get('cms/v4/types/' . $type);

		$url = "";
		foreach ($type["languages"] as $language) {
			if ($language["language_id"] == $lang) {
				$url = "/" . $language["alias"];
				break;
			}
		}

		$url .= "/" . $content;

		return $url;
	}

	public function createContent($params)
	{
		try {
			return $this->rest->post('cms/v4/contents', $params);
		} catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
			throw $exc;
		} catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
			throw $exc;
		} catch (\Exception $exc) {
			throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
		}
	}

	public function showContentThread($content_id)
	{
		try {
			return $this->rest->get('cms/v4/contents/' . $content_id . '/thread');
		} catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
			throw $exc;
		} catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
			throw $exc;
		} catch (\Exception $exc) {
			throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
		}
	}
}
