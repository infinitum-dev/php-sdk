<?php

/**
 * FYI Infinitum SDK WEB
 * 
 * @package FYI
 * @subpackage Infinitum SDK
 * @since 0.0.1
 */

namespace Fyi\Infinitum;

//require_once '../vendor/autoload.php';

use Fyi\Infinitum\Http\Rest;
use Fyi\Infinitum\Modules\Auth;
use Fyi\Infinitum\Modules\Device;
use Fyi\Infinitum\Modules\User;

use Fyi\Infinitum\Utils\Response;

/**
 * A client to access the Infinitum API
 */
class Infinitum extends Http\Rest
{
   protected $rest;

   protected $workspace;
   protected $app_token;
   protected $identity;

   protected $config;

   protected $access_token;

   public function __construct($workspace, $app_token, $identity)
   {
      $this->workspace  = $workspace;
      if (strpos($workspace, "localhost") > -1) {
         $url = "http://" . $this->workspace . "/api/";
      } else {
         $url = "https://" . $this->workspace . ".infinitum.app/api/";
      }
      $this->rest = new Rest($url);
      $this->setAppToken($app_token);
      $this->identity = $identity;
   }

   public function setAccessToken($access_token)
   {
      $this->access_token = $access_token;
      $this->rest->addRequestHeader("Authorization", "Bearer " . $access_token);
      return true;
   }

   public function setAppToken($app_token)
   {
      $this->app_token = $app_token;
      $this->rest->addRequestHeader("AppToken", $app_token);
      return true;
   }

   public function init()
   {
      try {
         $response = $this->rest->post('init', ['app_token' => $this->app_token, 'identity' => $this->identity]);
         $this->setAccessToken($response["access_token"]);
         return $response;
      } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
         throw $exc;
      } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
         throw $exc;
      } catch (\Exception $exc) {
         return $exc->getMessage();
         throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException("Unexpected error.", 500);
      }
   }

   /**
    * Auth module

    */
   public function auth()
   {
      return new Auth($this->rest);
   }

   /**
    * Device module

    */
   public function device()
   {
      return new Device($this->rest);
   }


   /**
    * User module
    */
   public function user()
   {
      return new User($this->rest);
   }
}
