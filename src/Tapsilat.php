<?php

namespace Tapsilat;

/**
 * Tapsilat PHP Package
 * 
 * A simple package that returns "tapsilat"
 */
class Tapsilat
{
    /**
     * Get the tapsilat string
     *
     * @return string
     */
    public function get(): string
    {
        return 'tapsilat';
    }

    /**
     * Get the tapsilat string (static method)
     *
     * @return string
     */
    public static function getStatic(): string
    {
        return 'tapsilat';
    }

    /**
     * Echo the tapsilat string
     *
     * @return void
     */
    public function echo(): void
    {
        echo 'tapsilat';
    }
} 