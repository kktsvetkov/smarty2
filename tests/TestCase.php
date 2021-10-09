<?php

namespace Smarty2\Tests;

use Smarty2\Engine;
use PHPUnit\Framework\TestCase as PHPUnit_TestCase;

class TestCase extends PHPUnit_TestCase
{
        protected Engine $smarty;

        function setUp() : void
        {
                $this->smarty = new Engine;
        }

        function tearDown() : void
        {
                unset($this->smarty);
        }
}
