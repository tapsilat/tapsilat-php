<?php

namespace Tapsilat\Models;

/**
 * Creates a Tapsilat Elements session, for example
 * new ElementsSessionCreateRequest(['purpose' => ElementsSessionCreateRequest::PURPOSE_PAY,
 * 'order_reference_id' => $referenceId]). purpose is always sent; fields left null are omitted.
 */
class ElementsSessionCreateRequest extends Model
{
    public const PURPOSE_TOKENIZE = ElementsSession::PURPOSE_TOKENIZE;
    public const PURPOSE_PAY = ElementsSession::PURPOSE_PAY;

    /** Tokenize mode: 3D Secure. */
    public const MODE_SECURE = 1;
    /** Tokenize mode: half secure. */
    public const MODE_HALF_SECURE = 2;
    /** Tokenize mode: non-secure. */
    public const MODE_NON_SECURE = 3;

    /** "tokenize" or "pay". Required. */
    public $purpose;
    /** Required for "pay". */
    public $order_reference_id;
    /** "tokenize": the card owner, scoped to the caller's organization. */
    public $user_id;
    public $user_email;
    /** "tokenize": MODE_SECURE, MODE_HALF_SECURE or MODE_NON_SECURE. */
    public $mode;
    /** "pay": lets the buyer save the card. */
    public $save_card;

    public function toArray()
    {
        return array_merge(['purpose' => ''], parent::toArray());
    }
}
