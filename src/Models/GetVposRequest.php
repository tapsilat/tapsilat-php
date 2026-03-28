<?php
namespace Tapsilat\Models;

class GetVposRequest
{
    public $currency_id;

    public function __construct(
        $currency_id = null
    ) {
        $this->currency_id = $currency_id;
    }

    public function toArray()
    {
        $data = array_filter([
            'currency_id' => $this->currency_id,
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
