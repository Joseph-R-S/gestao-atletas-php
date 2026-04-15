<?php

namespace App\Services;

abstract class Logger
{
    protected string $filename;
    public function __construct(string $filename)
    {
        $this->filename = $filename;
        file_put_contents($filename, '');
    }

    abstract function write($message);
}
