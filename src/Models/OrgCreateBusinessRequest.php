<?php
namespace Tapsilat\Models;

class OrgCreateBusinessRequest
{
    public $address;
    public $business_name;
    public $business_type;
    public $email;
    public $first_name;
    public $identity_number;
    public $last_name;
    public $phone;
    public $tax_number;
    public $tax_office;
    public $zip_code;

    public function __construct(
        $address = null,
        $business_name = null,
        $business_type = null,
        $email = null,
        $first_name = null,
        $identity_number = null,
        $last_name = null,
        $phone = null,
        $tax_number = null,
        $tax_office = null,
        $zip_code = null
    ) {
        $this->address = $address;
        $this->business_name = $business_name;
        $this->business_type = $business_type;
        $this->email = $email;
        $this->first_name = $first_name;
        $this->identity_number = $identity_number;
        $this->last_name = $last_name;
        $this->phone = $phone;
        $this->tax_number = $tax_number;
        $this->tax_office = $tax_office;
        $this->zip_code = $zip_code;
    }

    public function toArray()
    {
        $data = array_filter([
            'address' => $this->address,
            'business_name' => $this->business_name,
            'business_type' => $this->business_type,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'identity_number' => $this->identity_number,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'tax_number' => $this->tax_number,
            'tax_office' => $this->tax_office,
            'zip_code' => $this->zip_code,
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
