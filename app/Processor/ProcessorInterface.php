<?php

namespace App\Processor;

use App\Server\Request;

interface ProcessorInterface
{
    public function process(Request $request);
}