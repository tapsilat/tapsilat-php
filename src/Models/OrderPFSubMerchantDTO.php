<?php
namespace Tapsilat\Models;

class OrderPFSubMerchantDTO
{
    public $address;
    public $city;
    public $country;
    public $country_iso_code;
    public $id;
    public $mcc;
    public $name;
    public $national_id;
    public $org_id;
    public $postal_code;
    public $submerchant_nin;
    public $submerchant_url;
    public $switch_id;
    public $terminal_no;

    public function __construct(
        $address = null,
        $city = null,
        $country = null,
        $country_iso_code = null,
        $id = null,
        $mcc = null,
        $name = null,
        $national_id = null,
        $org_id = null,
        $postal_code = null,
        $submerchant_nin = null,
        $submerchant_url = null,
        $switch_id = null,
        $terminal_no = null
    ) {
        $this->address = $address;
        $this->city = $city;
        $this->country = $country;
        $this->country_iso_code = $country_iso_code;
        $this->id = $id;
        $this->mcc = $mcc;
        $this->name = $name;
        $this->national_id = $national_id;
        $this->org_id = $org_id;
        $this->postal_code = $postal_code;
        $this->submerchant_nin = $submerchant_nin;
        $this->submerchant_url = $submerchant_url;
        $this->switch_id = $switch_id;
        $this->terminal_no = $terminal_no;
    }

    public function toArray()
    {
        $result = [];
        foreach (get_object_vars($this) as $key => $value) {
            if ($value !== null) {
                $result[$key] = $value;
            }
        }
        return $result;
    }
}
