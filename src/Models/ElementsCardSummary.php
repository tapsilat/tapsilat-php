<?php

namespace Tapsilat\Models;

/**
 * The card of a Tapsilat Elements session: brand, last four digits and expiry, never the number.
 */
class ElementsCardSummary implements \JsonSerializable
{
    public const BRAND_VISA = 'visa';
    public const BRAND_MASTERCARD = 'mastercard';
    public const BRAND_AMEX = 'amex';
    public const BRAND_TROY = 'troy';
    public const BRAND_DISCOVER = 'discover';
    public const BRAND_UNIONPAY = 'unionpay';
    public const BRAND_UNKNOWN = 'unknown';

    private $data;

    public function __construct($data)
    {
        $this->data = is_array($data) ? $data : [];
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->data;
    }

    public function toArray()
    {
        return $this->data;
    }

    /**
     * @return string|null The saved card's ID, when the card was saved
     */
    public function getId()
    {
        return isset($this->data['id']) ? $this->data['id'] : null;
    }

    /**
     * @return string|null One of the BRAND_ constants, or a brand this SDK does not know yet
     */
    public function getBrand()
    {
        return isset($this->data['brand']) ? $this->data['brand'] : null;
    }

    public function getLast4()
    {
        return isset($this->data['last4']) ? $this->data['last4'] : null;
    }

    /**
     * @return int|null
     */
    public function getExpMonth()
    {
        return isset($this->data['exp_month']) ? $this->data['exp_month'] : null;
    }

    /**
     * @return int|null
     */
    public function getExpYear()
    {
        return isset($this->data['exp_year']) ? $this->data['exp_year'] : null;
    }
}
