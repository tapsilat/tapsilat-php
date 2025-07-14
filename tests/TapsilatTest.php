<?php

namespace Tapsilat\Tests;

use PHPUnit\Framework\TestCase;
use Tapsilat\Tapsilat;

class TapsilatTest extends TestCase
{
    public function testGetReturnsTapsilat()
    {
        $tapsilat = new Tapsilat();
        $this->assertEquals('tapsilat', $tapsilat->get());
    }

    public function testGetStaticReturnsTapsilat()
    {
        $this->assertEquals('tapsilat', Tapsilat::getStatic());
    }

    public function testEchoOutputsTapsilat()
    {
        $tapsilat = new Tapsilat();
        
        // Capture output
        ob_start();
        $tapsilat->echo();
        $output = ob_get_clean();
        
        $this->assertEquals('tapsilat', $output);
    }
} 