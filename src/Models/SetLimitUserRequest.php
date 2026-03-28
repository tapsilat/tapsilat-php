<?php
namespace Tapsilat\Models;

class SetLimitUserRequest
{
    public $limit_id;
    public $user_id;

    public function __construct(
        $limit_id = null,
        $user_id = null
    ) {
        $this->limit_id = $limit_id;
        $this->user_id = $user_id;
    }

    public function toArray()
    {
        $data = array_filter([
            'limit_id' => $this->limit_id,
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
