<?php
namespace Tapsilat\Models;

class UpdateBasketItemRequest
{
    public $order_reference_id;
    public $basket_item;

    public function __construct(
        $order_reference_id = null,
        $basket_item = null
    ) {
        $this->order_reference_id = $order_reference_id;
        $this->basket_item = $basket_item;
    }

    public function toArray()
    {
        $data = array_filter([
            'order_reference_id' => $this->order_reference_id,
            'basket_item' => $this->basket_item,
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
