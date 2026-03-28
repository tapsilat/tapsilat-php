<?php
namespace Tapsilat\Models;

class OrderConsent
{
    public $title;
    public $url;

    public function __construct($title = null, $url = null)
    {
        $this->title = $title;
        $this->url = $url;
    }

    public function toArray()
    {
        return array_filter([
            'title' => $this->title,
            'url' => $this->url,
        ], function ($value) {
            return $value !== null;
        });
    }
}
