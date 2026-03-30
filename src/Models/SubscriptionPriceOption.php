<?php
namespace Tapsilat\Models;

class SubscriptionPriceOption
{
    public $count;
    public $price;

    public function __construct($count = null, $price = null)
    {
        $this->count = $count;
        $this->price = $price;
    }

    public function toArray()
    {
        return array_filter([
            'count' => $this->count,
            'price' => $this->price,
        ], function ($value) {
            return $value !== null;
        });
    }
}
