<?php
namespace Tapsilat\Models;

class OrderPaymentTermDeleteDTO
{
    public $order_id;
    public $term_reference_id;

    public function __construct(
        $order_id = null,
        $term_reference_id = null
    ) {
        $this->order_id = $order_id;
        $this->term_reference_id = $term_reference_id;
    }

    public function toArray()
    {
        $data = array_filter([
            'order_id' => $this->order_id,
            'term_reference_id' => $this->term_reference_id,
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
