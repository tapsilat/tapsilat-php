<?php
namespace Tapsilat\Models;

class RemoveBasketItemRequest
{
    public $order_reference_id;
    public $basket_item_id;

    public function __construct(
        $order_reference_id = null,
        $basket_item_id = null
    ) {
        $this->order_reference_id = $order_reference_id;
        $this->basket_item_id = $basket_item_id;
    }

    public function toArray()
    {
        $data = array_filter([
            'order_reference_id' => $this->order_reference_id,
            'basket_item_id' => $this->basket_item_id,
        ], function ($value) {
            return $value !== null;
        });
        
        // Handle nested DTOs if any
        foreach ($data as $key => $val) {
            if (is_object($val) && method_exists($val, 'toArray')) {
                $data[$key] = $val->toArray();
            }
        }

        return $data;
    }
}
