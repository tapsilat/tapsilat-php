<?php
namespace Tapsilat\Models;

class OrderAccountingRequest
{
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
