<?php
namespace Tapsilat\Models;

class OrderPostAuthRequest
{
    public $amount;
    public $reference_id;

    public function __construct($amount, $reference_id)
    {
        $this->amount = $amount;
        $this->reference_id = $reference_id;
    }

    public function toArray()
    {
        return [
            'amount' => $this->amount,
            'reference_id' => $this->reference_id
        ];
    }
}
