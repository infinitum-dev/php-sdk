<?php

namespace Fyi\Infinitum\Modules;

use Fyi\Infinitum\Infinitum;
use Fyi\Infinitum\Modules\Eshop;
use Fyi\Infinitum\Modules\User;

class Metadatas extends Infinitum
{
    protected $rest;

    public function __construct($rest)
    {
        $this->rest = $rest;
    }

    /**
     * Get metadatas information by page type and language id
     *
     * @param {string} $page
     * @param {string} language_id
     *
     * @return {object}
     */
    public function getMetadataByPageAndLanguage($page, $language_id)
    {
        try {
            return $this->rest->get('cms/v4/metadatas/' . $page . '/' . $language_id);
        } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
            throw $exc;
        } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
            throw $exc;
        } catch (\Exception $exc) {
            throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
        }
    }
}
