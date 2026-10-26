<?php

namespace Tapsilat\Models;

/**
 * The authoritative state of a Tapsilat Elements session, as GET /elements/sessions/{id} returns it.
 * Every field may be absent; its getter then returns null. Timestamps are RFC 3339 strings.
 * Statuses, purposes and card brands that this SDK does not know are returned as they arrive.
 */
class ElementsSession implements \JsonSerializable
{
    public const PURPOSE_TOKENIZE = 'tokenize';
    public const PURPOSE_PAY = 'pay';

    public const STATUS_REQUIRES_PAYMENT_METHOD = 'requires_payment_method';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_REQUIRES_ACTION = 'requires_action';
    public const STATUS_SUCCEEDED = 'succeeded';
    public const STATUS_FAILED = 'failed';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELED = 'canceled';

    /** What var_dump() and print_r() show in place of the client secret. */
    public const REDACTED = '[redacted]';

    protected $data;

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
     * Keeps the client secret out of var_dump() and print_r() output.
     */
    public function __debugInfo()
    {
        $data = $this->data;
        if (array_key_exists('client_secret', $data)) {
            $data['client_secret'] = self::REDACTED;
        }
        return ['data' => $data];
    }

    /**
     * @return string|null A session ID such as "es_test_8Kq2nV0rX4"
     */
    public function getId()
    {
        return $this->field('id');
    }

    /**
     * @return string|null PURPOSE_TOKENIZE or PURPOSE_PAY
     */
    public function getPurpose()
    {
        return $this->field('purpose');
    }

    /**
     * @return string|null One of the STATUS_ constants
     */
    public function getStatus()
    {
        return $this->field('status');
    }

    public function getOrderReferenceId()
    {
        return $this->field('order_reference_id');
    }

    public function getUserId()
    {
        return $this->field('user_id');
    }

    /**
     * @return int|null
     */
    public function getAttempts()
    {
        return $this->field('attempts');
    }

    /**
     * @return int|null
     */
    public function getMaxAttempts()
    {
        return $this->field('max_attempts');
    }

    public function getCreatedAt()
    {
        return $this->field('created_at');
    }

    public function getExpiresAt()
    {
        return $this->field('expires_at');
    }

    public function getCompletedAt()
    {
        return $this->field('completed_at');
    }

    /**
     * @return ElementsCardSummary|null The card used or saved, when there is one
     */
    public function getCard()
    {
        return is_array($this->field('card')) ? new ElementsCardSummary($this->data['card']) : null;
    }

    /**
     * @return ElementsError|null Why the last attempt failed, when one did
     */
    public function getLastError()
    {
        return is_array($this->field('last_error')) ? new ElementsError($this->data['last_error']) : null;
    }

    protected function field($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }
}
