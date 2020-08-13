<?php

namespace Fyi\Infinitum\Modules;

use Fyi\Infinitum\Infinitum;
use Fyi\Infinitum\Modules\Eshop;
use Fyi\Infinitum\Modules\User;
use Fyi\Infinitum\Modules\Metadatas;

class Cms extends Infinitum
{
    protected $rest;
    protected $exclude_contents = [];

    public function __construct($rest)
    {
        $this->rest = $rest;
        $this->eshop = new Eshop($rest);
        $this->user = new User($rest);
        $this->metadatas = new Metadatas($rest);
        $this->worklog = new Worklog($rest);
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
            return $this->getTemplate($user, $current_url, $device_type, isset($params["lang"]) ? $params["lang"] : 1,  isset($params["inbox"]) ? $params["inbox"] : []);
        } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
            throw $exc;
        } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
            throw $exc;
        } catch (\Exception $exc) {
            throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
        }
    }

    public function getTemplate($session_user, $current_url, $device_type = "web", $lang = null, $inbox_params)
    {
        try {
            # get url information + metadatas
            $url = $this->parseUrl($current_url, $lang);

            // check if content and if is published
            if (isset($url["content"])) {
                $validContent = $this->checkContentPublished($url["content"], $current_url);

                if (!$validContent) {
                    throw new \Exception("Content unpublished", 400);
                }
            }
            if (!is_array($url)) {
                if ($url === "home") {
                    # template
                    try {
                        $template = $this->rest->get('cms/v4/templates/home');
                    } catch (\Throwable $th) {
                        throw new \Exception("Home template not found.", 404);
                    }

                    $page = $url;

                    # metadatas
                    try {
                        $metadatas = $this->metadatas->getMetadataByPageAndLanguage($url, $lang);
                        $template['metadatas'] = $metadatas;
                    } catch (\Throwable $th) {
                    }
                }
            } else {
                if (empty($url)) {
                    throw new \Exception("Missing Type/Content", 400);
                }

                if (isset($url["content"])) {
                    # template
                    try {
                        $template = $this->rest->get('cms/v4/templates/page/content_' . $url["content"]["id"]);
                    } catch (\Throwable $th) {
                        try {
                            $template = $this->rest->get('cms/v4/templates/page/type_' . $url["content"]["type"]["id"] . '_contents');
                        } catch(\Throwable $th) {
                            $template = $this->rest->get('cms/v4/templates/page/contents');
                        }
                    }

                    $page = "content_" . $url["content"]["id"] . ",type_" . $url["content"]["type"]["id"] . "_all,type_" . $url["content"]["type"]["id"] . "_contents";

                    # metadatas
                    try {
                        $metadatas = $this->metadatas->getMetadataByPageAndLanguage('content_' . $url["content"]["id"], $lang);
                        $template['metadatas'] = $metadatas;
                    } catch (\Throwable $th) {
                    }
                }
                else if (isset($url["category"])) {
                    # template
                    try {
                        $template = $this->rest->get('cms/v4/templates/page/type_' . $url["type"]["id"]);
                    } catch (\Throwable $th) {
                        $template  = $this->rest->get('cms/v4/templates/page/types');
                    }

                    $page = "category_" . $url["category"]["id"] . ",type_" . $url["type"]["id"] . "_category_" . $url["category"]["id"] . ",type_" . $url["type"]["id"] . "_categories";

                    # metadatas
                    try {
                        $metadatas = $this->metadatas->getMetadataByPageAndLanguage("category_" . $url["category"]["id"], $lang);
                        $template['metadatas'] = $metadatas;
                    } catch (\Throwable $th) {
                    }
                }
                else if (isset($url["type"])) {
                    # template
                    try {
                        $template = $this->rest->get('cms/v4/templates/page/type_' . $url["type"]["id"]);
                    } catch (\Throwable $th) {
                        $template  = $this->rest->get('cms/v4/templates/page/types');
                    }

                    $page = "type_" . $url["type"]["id"] . ",type_" . $url["type"]["id"] . "_all";
                    //$page = "type_" . $url["type"]["id"] . ",type_" . $url["type"]["id"] . "_all,type_" . $url["type"]["id"] . "_contents";
                    # metadatas
                    try {
                        $metadatas = $this->metadatas->getMetadataByPageAndLanguage("type_" . $url["type"]["id"], $lang);
                        $template['metadatas'] = $metadatas;
                    } catch (\Throwable $th) {
                    }
                }
            }

            # get blocks by template
            $templateParam = ["page" => $page];
            if ($lang) {
                $templateParam["languageId"] = $lang;
            }

            $blocks = $this->getBlocksByTemplate($template["id"], $templateParam);
			$newpos = [];
			$test = [];
            $no_roles = [];
            foreach ($template["positions"] as $position) {
                foreach ($blocks as $block) {
                    if ($this->blockHasUserRoles($block, $session_user)) {
                        $temp_block = $block;

                        $block = $this->blockHasTemplatePosition($block, $template["id"], $position);

                        if ($block) {
                            $block["plugin"] = current(array_filter($block["templates"], function ($element) use ($template) {
                                return $element["template_id"] === $template["id"];
                            }))["plugin"];
                            # clear array
                            unset($block["pages"]);
                            unset($block["templates"]);
                            unset($block["roles"]);
                            unset($block["created_at"]);
                            unset($block["updated_at"]);

                            # validate languages
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
                            if ($block["type"] === "inbox") {
                                $inbox = $this->inbox();
                                if ($session_user) {
                                    $inbox_params["unread"] = true;
                                    $user_threads = $inbox->getUserThreads($session_user["id"], $inbox_params);

                                    foreach ($user_threads as $key => $value) {
                                        try {
                                            foreach ($user_threads[$key]["messages"] as $key2 => $value) {
                                                $countRead = 0;
                                                $isRead = false;
                                                foreach ($value["users"] as $user) {
                                                    if (
                                                        in_array($user["id"], array_column($session_user["relations"], "id")) ||
                                                        count(array_intersect(array_column($user["groups"], "id"), array_column($session_user["groups"], "id"))) > 0 ||
                                                        $user["id"] == $session_user["id"]
                                                    ) {
                                                        if (!$user["pivot"]["is_read"]) {
                                                            $isRead = false;
                                                            break;
                                                        } else {
                                                            $isRead = true;
                                                        }
                                                    }
                                                    // if ($user["pivot"]["is_read"]) {
                                                    // 	$countRead += 1;
                                                    // 	if ($user["id"] === $session_user["id"] || $inbox->isUser($value, $session_user)) {
                                                    // 		$isRead = true;
                                                    // 	}
                                                    // }
                                                }
                                                if (!$isRead && $user_threads[$key]["messages"][$key2]["sender_id"] === $session_user["id"]) {
                                                    $isRead = true;
                                                }
                                                $user_threads[$key]["messages"][$key2]["is_read"] = $isRead;
                                                $user_threads[$key]["is_read"] = $isRead;
                                                $user_threads[$key]["messages"][$key2]["count_read"] = $countRead;
                                                #TODO: MUDART RELATION
                                            }
                                        } catch (\Throwable $th) {
                                            if (get_class($th) !== "Fyi\Infinitum\Exceptions\InfinitumAPIException")
                                                dd($th);
                                        }
                                    }
                                    $block["data"]["threads"] = $user_threads;
                                }
                            }

                            if (($block["type"] == "content" || $block["type"] == "checkout") && isset($block["data"])) {
								$contents = $this->getContents($block["data"], $url, $lang);
                                $block["data"]["contents"] = $contents["contents"];
                                $block["data"]["nextPage"] = $contents["nextPage"];
                                if(isset($contents["filters"])) {
                                    $block["data"]["filters"] = $contents["filters"];
                                }
                                $block["data"]["total"] = $contents["total"];
                                if (isset($contents["type_id"])) {
                                    $block["data"]["type_id"] = $contents["type_id"];
                                }

                                if ($block["plugin"] === "inbox") {
                                    $inbox = $this->inbox();
                                    if ($session_user) {
                                        $user_threads = $inbox->getUserThreads($session_user["id"]);
                                        $inbox_contents = [];
                                        foreach ($block["data"]["contents"] as $key => $value) {
                                            try {
                                                $block["data"]["contents"][$key]["tos"] = [];
                                                $obj_id = $block["data"]["contents"][$key]["content"]["content_id"];
                                                $thread_content = $this->showContentThread($obj_id);
                                                $thread = current(array_filter($user_threads, function ($element) use ($thread_content) {
                                                    return $element["id"] === $thread_content["infinitum_thread_id"];
                                                }));
                                                if ($thread) {
                                                    $messages = [];
                                                    foreach ($thread["messages"] as $key2 => $value) {
                                                        if ($inbox->isUser($thread["messages"][$key2], $session_user)) {
                                                            $countRead = 0;
                                                            $isRead = false;
                                                            foreach ($value["users"] as $user) {
                                                                if ($user["pivot"]["is_read"]) {
                                                                    $countRead += 1;
                                                                    if ($user["id"] === $session_user["id"] || in_array($user["id"], array_column($session_user["relations"], 'id'))) {
                                                                        $isRead = true;
                                                                    }
                                                                }
                                                            }
                                                            if (!$isRead && $thread["messages"][$key2]["sender_id"] === $session_user["id"]) {
                                                                $isRead = true;
                                                            }
                                                            $thread["messages"][$key2]["is_read"] = $isRead;
                                                            $thread["messages"][$key2]["count_read"] = $countRead;
                                                            $thread["messages"][$key2]["sender"] = $this->user()->getUser($thread["messages"][$key2]["sender_id"]); #TODO: MUDART RELATION
                                                            $thread["messages"][$key2]["infinitum_thread_id"] = $thread_content["infinitum_thread_id"];
                                                            $block["data"]["contents"][$key]["infinitum_thread_id"] = $thread_content["infinitum_thread_id"];
                                                            $block["data"]["contents"][$key]["infinitum_thread_token"] = $thread["token"];
                                                            $block["data"]["contents"][$key]["tos"] = array_merge($block["data"]["contents"][$key]["tos"], array_column($thread["messages"][$key2]["users"], 'id'));
                                                            $block["data"]["contents"][$key]["infinitum_thread_groups"] = $thread["groups"];
                                                            $block["data"]["contents"][$key]["is_read"] = $isRead;

                                                            $messages[] = $thread["messages"][$key2];
                                                        }
                                                    }
                                                    $block["data"]["contents"][$key]["messages"] = $messages;
                                                    $inbox_contents[] = $block["data"]["contents"][$key];
                                                }
                                            } catch (\Throwable $th) {
                                                if (get_class($th) !== "Fyi\Infinitum\Exceptions\InfinitumAPIException")
                                                    dd($th);
                                            }
                                        }

                                        $block["data"]["contents"] = $inbox_contents;
                                    }
                                }
                            }

                            if ($block["type"] == "checkout" || $block["plugin"] == "checkout") {
                                $block["data"]["payment"] = $this->eshop->getPaymentMethods();
                            }

                            if ($block["type"] == "frequency") {
                                if ($session_user) {
                                    $ids = array_column($session_user["relations"], "id");
                                    $block["data"]["worklogs"] = $this->worklog->getWorklogFromUsers(["ids" => implode(",", $ids), "limit" => 10, "offset" => 0]);
                                }
							}

							if ($block["type"] == "type") {
								$block_types = [];
								if(isset($block["data"]["types"])) {
									foreach ($block["data"]["types"] as $type_id) {
										$t = $this->getType($type_id);

										foreach ($t["languages"] as $l) {
											if($l["language_id"] === $lang) {
												$block_types[] = $l;
												break;
											}
										}
									}
									$block["data"]["types"] = $block_types;
									$block["data"]["name"] = $block["name"];
								}
							}

                            if ($block["type"] == "link") {
                                if ($block["plugin"] == "category") {
 									$categories = $this->rest->get('cms/v4/categories');
                                    $block["data"] = $block["data"][0];
                                    $block["data"]["categories"] = [];
                                    foreach ($categories as $c) {
										$c_types = [];
										foreach ($c["types"] as $t) {
											$type = $this->getType($t);
											foreach ($type["languages"] as $l) {
												if($l["language_id"] === $lang) {
													$c_types[] = $l;
												}
											}
										}
										$c["types"] = $c_types;
                                        foreach ($c["languages"] as $cl) {
                                            if($cl["language"]["id"] === $lang) {
                                                $cl["types"] = $c["types"];
                                                $block["data"]["categories"][] = $cl;
                                            }
                                        }
									}
                                }
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

    private function checkContentPublished($content, $url) {
        if ($content["published"] === 0) {
            $query = explode("?", $url);
            if (count($query) > 1) {
                parse_str($query[1], $queryParams);

                if (isset($queryParams["token"])) {
                    return $queryParams["token"] === $content["token"];
                }
            }

            return false;
        }
        return true;
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
        foreach ($block["roles"] as $r) {
            if ($session_user == null) {
                if ($r === 0) return true;
            } else {
                foreach ($session_user["roles"] as $role) {
                    if ($r === $role["id"]) return true;
                }
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

        $url = strtok($url, '?');
        if (substr($url, 0, 1) === "/") {
            $url = substr($url, 1, strlen($url));
        }

        # position 0 - /type
        # position 1 - /type/content
        # position 2 - /type/c/category
        # position 3 - /type/c/category/content
        $page = [];
        if ($url === "/" || $url === "") {
            return "home";
        } else {
            $ex = explode("/", $url);
            if (count($ex) == 4) {
                $page = ["type" => $ex[0], "category" => $ex[2], "content" => $ex[3]];
            }
            else if (count($ex) == 3) {
                $page = ["type" => $ex[0], "category" => $ex[2]];
            }
            else if (count($ex) == 2) {
                $page = ["type" => $ex[0], "content" => $ex[1]];
            } else if (count($ex) == 1) {
                $page = ["type" => $ex[0]];
            }
        }

        $params = [];
        if ($lang) {
            $params[] = "language_id=" . $lang;
        }

        $urlParams = implode("&", $params);

        # get content or category or type
        if (isset($page["content"])) {
            $content = $this->rest->get('cms/v4/contents/' . $page["content"] . "?" . $urlParams);
            return ["content" => $content];
        } else if (isset($page["category"])) {
            $type = $this->rest->get('cms/v4/types/' . $page["type"] . "?" . $urlParams);
            $category = $this->rest->get('cms/v4/categories/' . $page["category"] . "?" . $urlParams);
            return ["type" => $type, "category" => $category];
        } else if (isset($page["type"])) {
            $type = $this->rest->get('cms/v4/types/' . $page["type"] . "?" . $urlParams);
            return ["type" => $type];
        }

        return "";
    }

    public function getContents($content, $url, $lang)
    {
        try {
            $attributes = null;
            if ($content["url_dependence_limit"] == 1) {
                if (isset($url["content"])) {
                    $final = [
                        "contents" => [],
                        "nextPage" => [],
                        "total" => []
                    ];

                    if (isset($url["content"]["type"]))
                    {
                        $final["type_id"] = $url["content"]["type"]["id"];
                    }

                    # get content on url metadatas
                    $response = $url["content"];

                    $obj = [];
                    $obj["content"] = array_values($response["languages"])[0];
                    $obj["content"]["content_id"] = $response["id"];
                    $obj["content"]["type"] = $response["type"];

                    if (isset($response["fields"])) {
                        $obj["fields"] = $this->getContentFields($response["fields"]);
                    }

                    if (isset($response["attachments"])) {
                        $obj["attachments"] = $response["attachments"];
                    }

                    if (isset($response["categories"])) {
                        $obj["categories"] = $response["categories"];
                    }

                    if (isset($response["maps"])) {
                        $obj["maps"] = $response["maps"];
                    }

                    if (isset($response["events"])) {
                        $obj["events"] = $response["events"];
                    }
                    
                    if (isset($response["related"])) {
                        $obj["related"] = $response["related"];
					}

					if (isset($response["eshop_products"]) && count($response["eshop_products"]) > 0) {
                        if(!isset($attributes))
                        $attributes = $this->getAttributes();

                        $obj["attributes"] = $attributes;
						$obj["products"] = $this->getProducts($response["eshop_products"]);
                        $obj["eshop_products"] = $response["eshop_products"];
                        $obj["filters"] = [];
						$obj_filters = [];
                        foreach ($obj["products"] as $prod) {
                            foreach ($prod["variations"] as $var) {
                                if(!empty($var["attributes"])) {
                                    foreach ($var["attributes"] as $attr) {
                                        $obj_filters[] = $attr["attribute_value_id"];
                                        $global_filters[] = $attr["attribute_value_id"];
                                    }
                                }
                            }
                        }
                        if(!empty($obj_filters)) {
                            $obj["filters"] = $this->getValuesFromAttributes($attributes, array_unique($obj_filters));
                        }
                    }

                    $obj["url"] = array_values($response["languages"])[0]["url"];

                    array_push($final["contents"], $obj);
                    return $final;
                } else if (isset($url["category"]) || isset($url["type"])) {
                    $urlParams = 'types=' . $url["type"]["id"] . '&language_id=' . $lang;

                    if (isset($content["contents_limit"]))
                        $urlParams .= "&limit=" . $content["contents_limit"];

                    if (isset($content["page"]))
                        $urlParams .= "&page=" . $content["page"];

                    if (isset($content["event_start"]))
                        $urlParams .= "&event_start=" . $content["event_start"];

                    if (isset($content["event_end"]))
                        $urlParams .= "&event_end=" . $content["event_end"];

                    if (isset($content["contents"]) && is_array($content["contents"]) && !empty($content["contents"]))
                        $urlParams .= "&contents=" . implode(',', $content["contents"]);
                    else if (isset($content["contents"]) && !is_array($content["contents"]))
                        $urlParams .= "&contents=" . $content["contents"];


                    if (isset($url["category"])) {
                        $urlParams .= "&categories=" . $url["category"]["id"];
                    }
                    else if (isset($content["categories"])) {
                        $categories = is_array($content["categories"]) ? implode(',', $content["categories"]) : $content["categories"];
                        $urlParams .= "&categories=" . $categories;
                    }

                    if (isset($content["field_1"])) {
                        $field_1 = is_array($content["field_1"]) ? implode(',', $content["field_1"]) : $content["field_1"];
                        $urlParams .= "&field_1=" . $field_1;
                    }

                    if (isset($content["order"]))
                        $urlParams .= "&order=" . $content["order"];
                    else if (isset($content["contents_order"]))
						$urlParams .= "&order=" . $content["contents_order"];

                    $response = $this->rest->get('cms/v4/contents?' . $urlParams);
                    $final = [
                        "contents" => [],
                        "nextPage" => $response["nextPage"],
                        "total" => $response["total"]
                    ];

                    $final["type_id"] = $url["type"]["id"];
					$global_filters = [];
                    foreach ($response["data"] as $r) {
                        $obj = [];

                        $obj["content"] = array_values($r["languages"])[0];
                        $obj["content"]["content_id"] = $r["id"];

                        if (isset($r["fields"])) {
                            $obj["fields"] = $this->getContentFields($r["fields"]);
                        }

                        if (isset($r["attachments"])) {
                            $obj["attachments"] = $r["attachments"];
                        }

                        if (isset($r["categories"])) {
                            $obj["categories"] = $r["categories"];
                        }

                        if (isset($r["maps"])) {
                            $obj["maps"] = $r["maps"];
                        }

                        if (isset($r["events"])) {
                            $obj["events"] = $r["events"];
						}

						if (isset($r["eshop_products"]) && count($r["eshop_products"]) > 0) {
							if(!isset($attributes))
								$attributes = $this->getAttributes();

							$obj["attributes"] = $attributes;
							$obj["products"] = $this->getProducts($r["eshop_products"]);
							$obj["eshop_products"] = $r["eshop_products"];
							$obj["filters"] = [];
							$obj_filters = [];
							foreach ($obj["products"] as $prod) {

								foreach ($prod["variations"] as $var) {
									if(!empty($var["attributes"])) {
										foreach ($var["attributes"] as $attr) {
											$obj_filters[] = $attr["attribute_value_id"];
											$global_filters[] = $attr["attribute_value_id"];
										}
									}//dd aqi
								}
							}
							if(!empty($obj_filters)) {
								$obj["filters"] = $this->getValuesFromAttributes($attributes, array_unique($obj_filters));
							}
						}


						$obj["url"] = array_values($r["languages"])[0]["url"];
						$global_filters = array_unique($global_filters);

						array_push($final["contents"], $obj);
					}
					if(!empty($global_filters)) {
						//$final["filters"] = $this->getAttributeValues(implode(",", array_unique($global_filters)));
						$final["filters"] = $this->getValuesFromAttributes($attributes, array_unique($global_filters));
						//dd($final);
					}

                    return $final;
                }
            } else if (isset($content["url_related"]) && $content["url_related"] == 1) {
                $content_id = null;
                if (isset($url["content"]))
                    $content_id = $url["content"]["id"];

                $urlParams = $this->getParams($content, $lang, $content_id);

                $response = $this->rest->get('cms/v4/contents?' . $urlParams);

                $final = [
                    "contents" => [],
                    "nextPage" => $response["nextPage"],
                    "total" => $response["total"]
                ];

                foreach ($response["data"] as $r) {
                    $obj = [];
                    $obj["content"] = array_values($r["languages"])[0];
                    $obj["content"]["content_id"] = $r["id"];
                    $obj["content"]["type"] = $r["type"];

                    if (isset($r["fields"])) {
                        $obj["fields"] = $this->getContentFields($r["fields"]);
                    }

                    if (isset($r["attachments"])) {
                        $obj["attachments"] = $r["attachments"];
                    }

                    if (isset($r["categories"])) {
                        $obj["categories"] = $r["categories"];
                    }

                    if (isset($r["maps"])) {
                        $obj["maps"] = $r["maps"];
                    }

                    if (isset($r["events"])) {
                        $obj["events"] = $r["events"];
					}

					if (isset($r["eshop_products"]) && count($r["eshop_products"]) > 0) {
                        if(!isset($attributes))
                            $attributes = $this->getAttributes();

						$obj["attributes"] = $attributes;
						$obj["products"] = $this->getProducts($r["eshop_products"]);
                        $obj["eshop_products"] = $r["eshop_products"];
					}

                    $obj["url"] = array_values($r["languages"])[0]["url"];

                    array_push($final["contents"], $obj);
                }
                return $final;
            } else if (!empty($content["contents"])) {

                $urlParams = $this->getParams($content, $lang);

                $response = $this->rest->get('cms/v4/contents?' . $urlParams);

				if (isset($response["eshop_products"]) && count($response["eshop_products"]) > 0) {
                    if(!isset($attributes))
                        $attributes = $this->getAttributes();

                    $obj["attributes"] = $attributes;
					$obj["products"] =  $this->getProducts($response["eshop_products"]);
                    $obj["eshop_products"] = $response["eshop_products"];
				}

                $final = [
                    "contents" => [],
                    "nextPage" => $response["nextPage"],
                    "total" => $response["total"]
                ];

                foreach ($response["data"] as $r) {
                    $obj = [];
                    $obj["content"] = array_values($r["languages"])[0];
                    $obj["content"]["content_id"] = $r["id"];
                    $obj["content"]["type"] = $r["type"];

                    if (isset($r["fields"])) {
                        $obj["fields"] = $this->getContentFields($r["fields"]);
                    }

                    if (isset($r["attachments"])) {
                        $obj["attachments"] = $r["attachments"];
                    }

                    if (isset($r["categories"])) {
                        $obj["categories"] = $r["categories"];
                    }

                    if (isset($r["maps"])) {
                        $obj["maps"] = $r["maps"];
                    }

                    if (isset($r["events"])) {
                        $obj["events"] = $r["events"];
                    }
                    
                    if (isset($r["relateds"])) {
                        $obj["relateds"] = $r["relateds"];
					}

					if (isset($r["eshop_products"]) && count($r["eshop_products"]) > 0) {
                        if(!isset($attributes))
                            $attributes = $this->getAttributes();

						$obj["attributes"] = $attributes;
						$obj["products"] = $this->getProducts($r["eshop_products"]);
					}

                    $obj["url"] = array_values($r["languages"])[0]["url"];

                    array_push($final["contents"], $obj);
                }
                return $final;
            } else if (!empty($content["types"])) {
                if (!empty($content["data"]))
                {
                    $fields = json_decode($content["data"]);
                    foreach($fields as $field => $value)
                    {
                        $content[$field] = $value;
                    }
                }

                $types = is_array($content["types"]) ? implode(',', $content["types"]) : $content["types"];

                $urlParams = 'types=' . $types . '&language_id=' . $lang;

                if (isset($content["contents_limit"]))
                    $urlParams .= "&limit=" . $content["contents_limit"];

				if (isset($content["contents"]) && is_array($content["contents"]) && !empty($content["contents"]))
					$urlParams .= "&contents=" . implode(',', $content["contents"]);
				else if (isset($content["contents"]) && !is_array($content["contents"]))
					$urlParams .= "&contents=" . $content["contents"];

                if (isset($content["page"]))
                    $urlParams .= "&page=" . $content["page"];

                if (isset($content["start_date"]))
                    $urlParams .= "&start_date=" . $content["start_date"];

                if (isset($content["end_date"]))
                    $urlParams .= "&end_date=" . $content["end_date"];

                if (isset($content["event_start"]))
                    $urlParams .= "&event_start=" . $content["event_start"];

                if (isset($content["event_end"]))
                    $urlParams .= "&event_end=" . $content["event_end"];

                if (isset($content["categories"])) {
                    $categories = is_array($content["categories"]) ? implode(',', $content["categories"]) : $content["categories"];
                    $urlParams .= "&categories=" . $categories;
				} else if(isset($url["category"])) {
					$urlParams .= "&categories=" . $url["category"]["id"];
				}

                if (isset($content["field_1"])) {
                    $field_1 = is_array($content["field_1"]) ? implode(',', $content["field_1"]) : $content["field_1"];
                    $urlParams .= "&field_1=" . $field_1;
                }

                if (isset($content["order"]))
                    $urlParams .= "&order=" . $content["order"];
                else if (isset($content["contents_order"]))
                    $urlParams .= "&order=" . $content["contents_order"];

                if (isset($content["exclude_contents"]) && $content["exclude_contents"] && !empty($this->exclude_contents)) {
                    $urlParams .= "&exclude_contents=" . implode(',', $this->exclude_contents);
				}
				$response = $this->rest->get('cms/v4/contents?' . $urlParams);

                $final = [
                    "contents" => [],
                    "filters" => [],
                    "nextPage" => $response["nextPage"],
                    "total" => $response["total"]
                ];

                $global_filters = [];
                foreach ($response["data"] as $r) {
                    $obj = [];
                    $obj["content"] = array_values($r["languages"])[0];
                    $obj["content"]["content_id"] = $r["id"];
                    $obj["content"]["type"] = $r["type"];
                    if (isset($content["exclude_contents"]) && $content["exclude_contents"])
                        array_push($this->exclude_contents, $r["id"]);

                    if (isset($r["fields"])) {
                        $obj["fields"] = $this->getContentFields($r["fields"]);
                    }

                    if (isset($r["attachments"])) {
                        $obj["attachments"] = $r["attachments"];
                    }

                    if (isset($r["categories"])) {
                        $obj["categories"] = $r["categories"];
                    }

                    if (isset($r["maps"])) {
                        $obj["maps"] = $r["maps"];
                    }

                    if (isset($r["events"])) {
                        $obj["events"] = $r["events"];
                    }

                    if (isset($r["relateds"])) {
                        $obj["relateds"] = $r["relateds"];
                    }

					if (isset($r["eshop_products"]) && count($r["eshop_products"]) > 0) {
                        if(!isset($attributes))
                            $attributes = $this->getAttributes();

						$obj["attributes"] = $attributes;
						$obj["products"] = $this->getProducts($r["eshop_products"]);
                        $obj["eshop_products"] = $r["eshop_products"];
						$obj["filters"] = [];
						$obj_filters = [];
						foreach ($obj["products"] as $prod) {
							foreach ($prod["variations"] as $var) {
								if(!empty($var["attributes"])) {
									foreach ($var["attributes"] as $attr) {
										$obj_filters[] = $attr["attribute_value_id"];
										$global_filters[] = $attr["attribute_value_id"];
									}
								}
							}
						}
						if(!empty($obj_filters)) {
							$obj["filters"] = $this->getValuesFromAttributes($attributes, array_unique($obj_filters));
						}
                    }

                    $obj["url"] = array_values($r["languages"])[0]["url"];
                    $global_filters = array_unique($global_filters);

                    array_push($final["contents"], $obj);
                }
                if(!empty($global_filters)) {
                    //$final["filters"] = $this->getAttributeValues(implode(",", array_unique($global_filters)));
                    $final["filters"] = $this->getValuesFromAttributes($attributes, array_unique($global_filters));
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

    private function getParams($content, $lang, $content_id = null)
    {
        $types = "";
        if (isset($content["types"]))
            $types = is_array($content["types"]) ? implode(',', $content["types"]) : $content["types"];

        $urlParams = 'language_id=' . $lang;

        if (isset($content["contents"]))
            $urlParams .= '&contents=' . implode(',', $content["contents"]);

        if (isset($content["contents_limit"]))
            $urlParams .= "&limit=" . $content["contents_limit"];

        if ($types !== "")
            $urlParams .= "&types=" . $types;

        if (isset($content["page"]))
            $urlParams .= "&page=" . $content["page"];

        if (isset($content["event_start"]))
            $urlParams .= "&event_start=" . $content["event_start"];

        if (isset($content["event_end"]))
            $urlParams .= "&event_end=" . $content["event_end"];

        if (isset($content["categories"])) {
            $categories = is_array($content["categories"]) ? implode(',', $content["categories"]) : $content["categories"];
            $urlParams .= "&categories=" . $categories;
        }

        if (isset($content["url_related"]) && $content_id)
            $urlParams .= "&related_id=" . $content_id;

        if (isset($content["field_1"])) {
            $field_1 = is_array($content["field_1"]) ? implode(',', $content["field_1"]) : $content["field_1"];
            $urlParams .= "&field_1=" . $field_1;
        }

        if (isset($content["order"]))
            $urlParams .= "&order=" . $content["order"];
        else if (isset($content["contents_order"]))
            $urlParams .= "&order=" . $content["contents_order"];

        return $urlParams;
    }

    public function getContentFields($fields)
    {
        $final = [];
        foreach ($fields as $field) {
            $final[$field["id"]]["id"] = $field["id"];
            $final[$field["id"]]["label"] = $field["languages"][0]["label"];
            $final[$field["id"]]["value"] = $field["pivot"]["value"];
            $final[$field["id"]]["orderfield"] = $field["orderfield"];
            $final[$field["id"]]["type"] = $field["type"];
        }

        return $final;
    }

    public function getProducts($products)
    {
        $prod = [];
        foreach($products as $p){
            if(isset($p["product"]))
                array_push($prod, $p["product"]);
        }
        return $prod;
	}

	public function getAttributes()
    {
        return $this->eshop->getAttributes();
	}

	public function getAttribute($id)
    {
        return $this->eshop->getAttribute($id);
	}

	public function getAttributeValues($ids)
    {
        return $this->eshop->getAttributeValues($ids);
    }

    public function getValuesFromAttributes($attributes, $filter){
        $final = [];
        foreach($attributes as $attribute){
			foreach($attribute["values"] as $value){
				if(in_array($value["id"], $filter)){
                    $tmp = [];
					$tmp["id"] = $value["id"];
                    $tmp["attribute_id"] = $value["attribute_id"];
                    $tmp["value"] = $value["value"];
                    $tmp["attribute"] = [];
                    $tmp["attribute"]["id"] = $attribute["id"];
                    $tmp["attribute"]["name"] = $attribute["name"];
                    $tmp["attribute"]["alias"] = $attribute["alias"];
                    $tmp["attribute"]["type"] = $attribute["type"];
                    array_push($final, $tmp);
                }
            }
		}
		return $final;
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
            return $this->rest->post('cms/v4/contents', $this->flatten($params), [], [], true);
        } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
            throw $exc;
        } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
            throw $exc;
        } catch (\Exception $exc) {
            throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
        }
    }

    private function flatten($array, $prefix = '') {
        $result = array();
        foreach($array as $key=>$value) {
            if(is_array($value)) {
                $result = $result + $this->flatten_aux($value, $key);
            }
            else {
                $result[$prefix.$key] = $value;
            }
        }
        return $result;
    }

    private function flatten_aux($array, $prefix = '') {
        $result = array();
        foreach($array as $key=>$value) {
            if(is_array($value)) {
                $result = $result + $this->flatten_aux($value, $prefix . '[' . $key . ']');
            }
            else {
                $result[$prefix . '[' . $key . ']'] = $value;
            }
        }
        return $result;
    }

    public function updateContent($id, $params)
    {
        try {
            return $this->rest->put('cms/v4/contents/' . $id, $params);
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

    public function getContentsFromUser($id, $type, $lang)
    {
        try {
            return $this->rest->get('cms/v4/contents?infinitum_user_id=' . $id . '&language_id=' . $lang . '&types=' . $type);
        } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
            throw $exc;
        } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
            throw $exc;
        } catch (\Exception $exc) {
            throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
        }
    }

    public function getContent($id)
    {
        try {
            return $this->rest->get('cms/v4/contents/' . $id);
        } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
            throw $exc;
        } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
            throw $exc;
        } catch (\Exception $exc) {
            throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
        }
    }

    public function getCategories()
    {
        try {
            return $this->rest->get('cms/v4/categories');
        } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
            throw $exc;
        } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
            throw $exc;
        } catch (\Exception $exc) {
            throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
        }
    }

    public function getFields()
    {
        try {
            return $this->rest->get('cms/v4/fields');
        } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
            throw $exc;
        } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
            throw $exc;
        } catch (\Exception $exc) {
            throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
        }
    }

    public function getType($id)
    {
        try {
            return $this->rest->get('cms/v4/types/' . $id);
        } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
            throw $exc;
        } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
            throw $exc;
        } catch (\Exception $exc) {
            throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
        }
    }

    public function checkApiContent($params)
    {
        try {
            return $this->rest->post('cms/v4/addons/api/check', $params);
        } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
            throw $exc;
        } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
            throw $exc;
        } catch (\Exception $exc) {
            throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
        }
    }

    public function getContentByApi($params)
    {
        try {
            $r = $this->rest->post('cms/v4/addons/api/getContent', $params);
            $obj = [];
            $obj["content"] = array_values($r["languages"])[0];
            $obj["content"]["content_id"] = $r["id"];
            $obj["content"]["type"] = $r["type"];

            if (isset($r["fields"])) {
                $obj["fields"] = $this->getContentFields($r["fields"]);
            }

            if (isset($r["attachments"])) {
                $obj["attachments"] = $r["attachments"];
            }

            if (isset($r["categories"])) {
                $obj["categories"] = $r["categories"];
            }

            if (isset($r["maps"])) {
                $obj["maps"] = $r["maps"];
            }

            if (isset($r["events"])) {
                $obj["events"] = $r["events"];
            }

            $obj["url"] = array_values($r["languages"])[0]["url"];

            $contents = [
                "contents" => [$obj]
            ];

            return $contents;
        } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
            throw $exc;
        } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
            throw $exc;
        } catch (\Exception $exc) {
            throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
        }
    }

    public function searchContent($params)
    {
        try {
            return $this->rest->post('cms/v4/contents/search', $params);
        } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
            throw $exc;
        } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
            throw $exc;
        } catch (\Exception $exc) {
            throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
        }
    }

    public function contents($query)
    {
        try {
            return $this->rest->get('cms/v4/contents?' . $query);
        } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
            throw $exc;
        } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
            throw $exc;
        } catch (\Exception $exc) {
            throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
        }
    }

    public function getFilteredProducts($current_url, $lang, $params)
    {
        try {

            $products = $this->eshop()->getFilteredProducts($params);
            if(count($products) > 0) {
                $contents_with_products = $this->getContentsWithProducts($current_url, $lang, array_column($products, "sku"));
                foreach ($contents_with_products["data"] as $key => $value) {
                    $contents_with_products["data"][$key]["products"] = [];
                    foreach ($value["eshop_products"] as $e_prod) {
                        $prod_key = array_search($e_prod["sku"], array_column($products, 'sku'));
                        if($prod_key !== null) {
                            $contents_with_products["data"][$key]["products"][] = $products[$prod_key];
                        }
                    }
                    unset($contents_with_products["data"][$key]["eshop_products"]);
                }


                return $contents_with_products;
            }
            return [];
        } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
            throw $exc;
        } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
            throw $exc;
        } catch (\Exception $exc) {
            throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
        }
    }

    public function getContentsWithProducts($current_url, $lang, $ids)
    {
        try {
			$url = $this->parseUrl($current_url, $lang);
            if(isset($url["category"]) && $url["category"]["id"] !== null) {
                return $this->rest->get('cms/v4/contents?categories='.$url["category"]["id"].'&products='.implode(",", $ids));
            } else if(isset($url["type"]) && $url["type"]["id"] !== null) {
                return $this->rest->get('cms/v4/contents?types='.$url["type"]["id"].'&products='.implode(",", $ids));
            } else {
                return $this->rest->get('cms/v4/contents?products='.implode(",", $ids));
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
