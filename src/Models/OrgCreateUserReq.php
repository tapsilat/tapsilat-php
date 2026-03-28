<?php
namespace Tapsilat\Models;

class OrgCreateUserReq
{
    public $conversation_id;
    public $email;
    public $first_name;
    public $identity_number;
    public $is_mail_verified;
    public $last_name;
    public $phone;
    public $reference_id;

    public function __construct(
        $conversation_id = null,
        $email = null,
        $first_name = null,
        $identity_number = null,
        $is_mail_verified = false,
        $last_name = null,
        $phone = null,
        $reference_id = null
    ) {
        $this->conversation_id = $conversation_id;
        $this->email = $email;
        $this->first_name = $first_name;
        $this->identity_number = $identity_number;
        $this->is_mail_verified = $is_mail_verified;
        $this->last_name = $last_name;
        $this->phone = $phone;
        $this->reference_id = $reference_id;
    }

    public function toArray()
    {
        $data = array_filter([
            'conversation_id' => $this->conversation_id,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'identity_number' => $this->identity_number,
            'is_mail_verified' => $this->is_mail_verified,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'reference_id' => $this->reference_id,
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
