<?php

namespace Fyi\Infinitum\Modules;

use Fyi\Infinitum\Infinitum;
use Fyi\Infinitum\Modules\Eshop;

class Clients extends Infinitum
{
    protected $rest;

    public function __construct($rest)
    {
        $this->rest = $rest;
        $this->eshop = new Eshop($rest);        
    }

    /*************************************************************************
     * Clients
     * ***********************************************************************/

    /**
     * Criar uma nova cliente
     * 
     * @return {array}
     */
    public function clientsCreate($params)
    {
        try {
            return $this->rest->post('clients', $params, [], [], true);
        } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
            throw $exc;
        } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
            throw $exc;
        } catch (\Exception $exc) {
            throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
        }
    }

    /**
     * Obter a informação do cliente
     * 
     * @return {array}
     */
    public function getClient($id)
    {
        try {
            return $this->rest->get('clients/' . $id);
        } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
            throw $exc;
        } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
            throw $exc;
        } catch (\Exception $exc) {
            throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
        }
    }

    /*************************************************************************
     * SUBSCRIPTION
     * ***********************************************************************/

    /**
     * Criar uma nova subscrição
     * 
     * @return {array}
     */
    public function subscriptionsCreate($params)
    {
        try {
            return $this->rest->post('clients/subscriptions', $params, [], [], true);           
        } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
            throw $exc;
        } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
            throw $exc;
        } catch (\Exception $exc) {
            throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
        }
    }

    /**
     * Obter a informação das subscrições
     * 
     * @return {array}
     */
    public function subscriptionsUpdate($id, $params)
    {
        try {
            return $this->rest->put('clients/subscriptions/' . $id,$params);            
        } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
            throw $exc;
        } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
            throw $exc;
        } catch (\Exception $exc) {
            throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
        }
    }

    /**
     * Obter a informação das subscrições
     * 
     * @return {array}
     */
    public function getSubscriptions($query = '')
    {
        try {
            return $this->rest->get('clients/subscriptions?' . $query);
        } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
            throw $exc;
        } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
            throw $exc;
        } catch (\Exception $exc) {
            throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
        }
    }

    /**
     * Obter a informação das subscrições
     * 
     * @return {array}
     */
    public function getSubscriptionsPayments($id)
    {
        try {
            return $this->rest->get('clients/subscriptions/payment/' . $id);
        } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
            throw $exc;
        } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
            throw $exc;
        } catch (\Exception $exc) {
            throw new \Fyi\Infinitum\Exceptions\InfinitumSDKException($exc->getMessage(), $exc->getCode());
        }
    }
}
