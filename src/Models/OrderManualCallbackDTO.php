<?php
namespace Tapsilat\Models;

class OrderManualCallbackDTO
{
    public $reference_id;
    public $conversation_id;

    public function __construct(
        $reference_id = null,
        $conversation_id = null
    ) {
        $this->reference_id = $reference_id;
        $this->conversation_id = $conversation_id;
    }

    public function toArray()
    {
        $data = array_filter([
            'reference_id' => $this->reference_id,
            'conversation_id' => $this->conversation_id,
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
