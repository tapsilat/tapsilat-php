<?php

namespace Tapsilat;

use Exception;

class APIException extends Exception
{
    public $statusCode;
    public $code;
    public $error;
    /** @var string|null The response body's "status" field, when it has one */
    public $status;
    /** @var array|null Per-field validation messages, when the API returns them */
    public $errors;
    /** @var string|null The raw response body */
    public $rawBody;
    /** @var bool True when the error object says the request can be retried, as Elements errors do */
    public $retryable;

    public function __construct(
        $statusCode,
        $code,
        $error,
        $status = null,
        $errors = null,
        $rawBody = null,
        $retryable = false
    ) {
        parent::__construct(
            "Tapsilat API Error\nstatus_code:{$statusCode}\ncode:{$code}\nerror:{$error}",
            is_numeric($code) ? (int)$code : 0
        );
        $this->statusCode = $statusCode;
        $this->code = $code;
        $this->error = $error;
        $this->status = $status;
        $this->errors = $errors;
        $this->rawBody = $rawBody;
        $this->retryable = (bool)$retryable;
    }

    /**
     * Builds the exception from an error response body such as {"error": "...", "code": 5},
     * {"message": "INVALID_REQUEST_BODY", "code": 400000, "errors": {...}} or, on the Elements
     * routes, {"error": {"code": "session_not_found", "message": "...", "retryable": false}}.
     */
    public static function fromResponse($statusCode, $body)
    {
        $body = (string)$body;
        $data = json_decode($body, true);
        if (!is_array($data)) {
            $message = trim($body) !== '' ? $body : 'HTTP error';
            return new self($statusCode, -1, $message, null, null, $body);
        }

        $nested = isset($data['error']) && is_array($data['error']) ? $data['error'] : [];
        if (array_key_exists('code', $data) && is_scalar($data['code'])) {
            $code = $data['code'];
        } elseif (isset($nested['code']) && is_scalar($nested['code'])) {
            $code = $nested['code'];
        } else {
            $code = -1;
        }
        if (isset($data['message']) && is_scalar($data['message'])) {
            $message = (string)$data['message'];
        } elseif (isset($nested['message']) && is_scalar($nested['message'])) {
            $message = (string)$nested['message'];
        } elseif (isset($data['error']) && is_scalar($data['error'])) {
            $message = (string)$data['error'];
        } else {
            $message = 'HTTP error';
        }
        $status = isset($data['status']) && is_scalar($data['status']) ? (string)$data['status'] : null;
        $errors = isset($data['errors']) && is_array($data['errors']) ? $data['errors'] : null;
        $retryable = isset($nested['retryable']) && $nested['retryable'] === true;

        return new self($statusCode, $code, $message, $status, $errors, $body, $retryable);
    }

    /**
     * Builds the exception for a response that is not JSON, such as the panel's HTML page.
     */
    public static function unexpectedResponse($statusCode, $contentType, $body)
    {
        $message = sprintf(
            'unexpected non-JSON response (Content-Type "%s"); check that the endpoint URL and route are correct',
            $contentType
        );
        return new self($statusCode, -1, $message, null, null, (string)$body);
    }
}
