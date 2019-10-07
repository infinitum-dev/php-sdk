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

    public function getProducts($ids)
    {
        return $this->rest->get('eshop/products?skus=' . $ids);
    }

    public function getPaymentMethods()
    {
        return $this->rest->get('eshop/payments/methods');
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
            $this->createShipping(["type" => $params["type"], "order_id" => $params["order_id"], "value" => json_encode($params["shipping"])]);
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
            $params["data"]["my_key"] = $params["order_id"];
            $params["data"]["my_key"] = $params["order_id"];
            $params["redirect_success_url"] = "http://localhost:8000";
            $params["redirect_cancel_url"] = "http://localhost:8000";
        }
        return $this->rest->post('eshop/payments', $params);
    }
}
