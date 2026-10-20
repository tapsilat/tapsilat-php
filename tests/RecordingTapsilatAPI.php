<?php

namespace Tapsilat\Tests;

use Tapsilat\TapsilatAPI;

/**
 * Records requests and answers them with canned responses keyed by "METHOD /path".
 */
class RecordingTapsilatAPI extends TapsilatAPI
{
    public $requests = [];
    public $responses = [];

    public function __construct()
    {
        parent::__construct('test-key', 10, 'https://panel.example/api/v1');
    }

    public function on($method, $path, $status, $body, $contentType = 'application/json')
    {
        $this->responses[$method . ' ' . $path] = [$status, $contentType, $body];
    }

    public function last($method, $path)
    {
        foreach (array_reverse($this->requests) as $request) {
            if ($request['method'] === $method && $request['path'] === $path) {
                return $request;
            }
        }
        return null;
    }

    protected function sendHttp($method, $url, array $headers, $body)
    {
        $parts = parse_url($url);
        $request = [
            'method' => $method,
            'path' => $parts['path'],
            'query' => isset($parts['query']) ? $parts['query'] : '',
            'headers' => $headers,
            'body' => $body,
            'json' => $body !== null && $body !== '' ? json_decode($body, true) : null,
        ];
        $this->requests[] = $request;
        $key = $method . ' ' . $parts['path'];
        return isset($this->responses[$key])
            ? $this->responses[$key]
            : [501, 'application/json', '{"error":"no stub"}'];
    }
}
