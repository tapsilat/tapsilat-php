<?php

require_once __DIR__ . '/vendor/autoload.php';

use Tapsilat\Tapsilat;

echo "=== Tapsilat PHP Package Example ===\n\n";

// Create instance
$tapsilat = new Tapsilat();

// Method 1: Get the string
echo "Method 1 - get(): " . $tapsilat->get() . "\n";

// Method 2: Static method
echo "Method 2 - getStatic(): " . Tapsilat::getStatic() . "\n";

// Method 3: Echo directly
echo "Method 3 - echo(): ";
$tapsilat->echo();
echo "\n";

echo "\n=== End Example ===\n"; 