<?php


use PHPUnit\Framework\TestCase;

class ErrorTest extends TestCase
{
    public function testGetError()
    {
        echo $a;
        print_r(error_get_last());
        $a = '';
        print_r(error_get_last());

    }
}