<?php

namespace Fyi\Infinitum\Modules;

use Fyi\Infinitum\Infinitum;

class Eshop extends Infinitum
{
    protected $rest;

    public function __construct($rest)
    {
        $this->rest = $rest;
    }

    public function getOrders($state, $user_id, $history)
    {
        return $this->rest->get('eshop/orders?state='. $state .'&user_id=' . $user_id . "&history=".$history);
    }

    public function getProducts($ids)
    {
        return $this->rest->get('eshop/products?skus=' . $ids);
    }

    public function getFilteredProducts($params)
    {
        $url = "eshop/products?";
        if(!empty($params)) {
            if(isset($params["ids"])) {
                $url = $url."skus=".implode(",", $params["ids"])."&";
            }

            if(isset($params["filters"])) {
                if(is_array($params["filters"])) {
                    $url = $url."filters=".implode(",", $params["filters"])."&";
                } else {
                    $url = $url."filters=". $params["filters"]."&";
                }
            }

            if(isset($params["priceRange"])) {
                if(is_array($params["priceRange"])) {
                    $url = $url."priceRange=".implode(",", $params["priceRange"]);
                } else {
                    $url = $url."priceRange=". $params["priceRange"];
                }
            }
        }
        return $this->rest->get($url);
    }


    public function getAttributeValues($ids = null)
    {
        if (isset($ids)) {
            return $this->rest->get('eshop/attributes/values?ids=' . $ids);
        }
        else
            return $this->rest->get('eshop/attributes/values');
    }

    public function getAttributes()
    {
        return $this->rest->get('eshop/attributes');
    }

    public function getAttribute($id)
    {
        return $this->rest->get('eshop/attributes/'.$id);
    }

    public function getPaymentMethods()
    {
        return $this->rest->get('eshop/payments/methods');
    }

    public function getOrderByToken($token)
    {
        return $this->rest->get('eshop/orders/'.$token);
    }

    public function createOrder($params)
    {
        if (isset($params["data"]["value"])) {
            $params["total"] = $params["data"]["value"];
        } else if (!isset($params["total"])) {
            $params["total"] = 0;
        }
        return $this->rest->post('eshop/orders', $params);
    }

    public function createShipping($params)
    {
        return $this->rest->post('eshop/shipping', $params);
    }

    public function createPayment($params)
    {

        if (!isset($params["order_id"])) {
            $params["order_id"] = intval($this->createOrder($params)["id"]);
        }
        if (isset($params["shipping"])) {
            //$this->createShipping(["type" => $params["type"], "order_id" => $params["order_id"], "value" => json_encode($params["shipping"])]);
        }
        if (!isset($params["due_date"])) {
            $nextWeek = time() + (7 * 24 * 60 * 60);
            $params["due_date"] = date('Y-m-d', $nextWeek);
        }
        if (!isset($params["data"]["description"])) {
            $params["data"]["description"] = "";
        }
        if ($params["type"] !== "donation") {
            $params["data"]["capture_date"] = $params["due_date"];
            $params["data"]["expiration_date"] = $params["due_date"] . " 23:59";
        }
        return $this->rest->post('eshop/payments', $params);
    }

    public function getPayments($products = null)
    {
        if (isset($products))
            return $this->rest->get('eshop/payments?products=' . $products);
        else
            return $this->rest->get('eshop/payments');
    }
    
    public function getPayment($id)
    {
        return $this->rest->get('eshop/payments/' . $id);
    }

    public function updatePayment($id, $params){
        return $this->rest->put('eshop/payments/'.$id, $params);
    }

    public function notify($params){
        return $this->rest->post('eshop/payments/notify', $params);
    }

    public function shippingMethods() {
        return $this->rest->get('eshop/shipping-methods');
    }
}
