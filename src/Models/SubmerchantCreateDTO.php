<?php
namespace Tapsilat\Models;

class SubmerchantCreateDTO
{
    public $address;
    public $city;
    public $country;
    public $email;
    public $gsm_number;
    public $iban;
    public $identity_number;
    public $name;
    public $contact_name;
    public $contact_surname;
    public $sub_merchant_type;
    public $tax_office;
    public $zip_code;
    public $district;
    public $iban_name;

    public function __construct(
        $address,
        $city,
        $country,
        $email,
        $gsm_number,
        $iban,
        $identity_number,
        $name,
        $contact_name,
        $contact_surname,
        $sub_merchant_type,
        $tax_office,
        $zip_code,
        $district = null,
        $iban_name = null
    ) {
        $this->address = $address;
        $this->city = $city;
        $this->country = $country;
        $this->email = $email;
        $this->gsm_number = $gsm_number;
        $this->iban = $iban;
        $this->identity_number = $identity_number;
        $this->name = $name;
        $this->contact_name = $contact_name;
        $this->contact_surname = $contact_surname;
        $this->sub_merchant_type = $sub_merchant_type;
        $this->tax_office = $tax_office;
        $this->zip_code = $zip_code;
        $this->district = $district;
        $this->iban_name = $iban_name;
    }

    public function toArray()
    {
        $result = [];
        foreach (get_object_vars($this) as $key => $value) {
            if ($value !== null) {
                if (is_object($value) && method_exists($value, 'toArray')) {
                    $result[$key] = $value->toArray();
                } elseif (is_array($value)) {
                    $result[$key] = array_map(function ($item) {
                        return is_object($item) && method_exists($item, 'toArray') ? $item->toArray() : $item;
                    }, $value);
                } else {
                    $result[$key] = $value;
                }
            }
        }
        return $result;
    }
}
