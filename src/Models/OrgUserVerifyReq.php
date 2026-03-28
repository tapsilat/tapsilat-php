<?php
namespace Tapsilat\Models;

class OrgUserVerifyReq
{
    public $user_id;

    public function __construct(
        $user_id = null
    ) {
        $this->user_id = $user_id;
    }

    public function toArray()
    {
        $data = array_filter([
            'user_id' => $this->user_id,
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
