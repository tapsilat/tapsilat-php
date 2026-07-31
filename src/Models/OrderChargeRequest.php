<?php

namespace Tapsilat\Models;

/**
 * @category API Models
 * @summary Order charge request model
 * @description Request model for charging an order
 */
class OrderChargeRequest
{
    /**
     * @var string
     */
    public $order_reference_id;

    public function __construct($order_reference_id)
    {
        $this->order_reference_id = $order_reference_id;
    }

    public function toArray()
    {
        return [
            'order_reference_id' => $this->order_reference_id
        ];
    }
}
