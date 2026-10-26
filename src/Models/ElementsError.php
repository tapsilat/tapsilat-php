<?php

namespace Tapsilat\Models;

/**
 * Why the last attempt of a Tapsilat Elements session failed, for example
 * {"code": "card_declined", "message": "...", "retryable": false}. This is session data, not an
 * exception; failed API calls throw Tapsilat\APIException.
 */
class ElementsError implements \JsonSerializable
{
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
     * @return string|null A stable code such as "card_declined" or "authentication_failed"
     */
    public function getCode()
    {
        return isset($this->data['code']) ? $this->data['code'] : null;
    }

    public function getMessage()
    {
        return isset($this->data['message']) ? $this->data['message'] : null;
    }

    /**
     * @return bool True when the buyer can try again
     */
    public function isRetryable()
    {
        return isset($this->data['retryable']) && $this->data['retryable'] === true;
    }
}
