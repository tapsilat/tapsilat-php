<?php
namespace Tapsilat\Models;

class OrderRelatedReferenceDTO
{
    public $reference_id;
    public $related_reference_id;

    public function __construct(
        $reference_id = null,
        $related_reference_id = null
    ) {
        $this->reference_id = $reference_id;
        $this->related_reference_id = $related_reference_id;
    }

    public function toArray()
    {
        $data = array_filter([
            'reference_id' => $this->reference_id,
            'related_reference_id' => $this->related_reference_id,
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
