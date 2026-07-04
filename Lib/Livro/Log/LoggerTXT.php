<?php

namespace Livro\Log;

class LoggerTXT extends Logger
{
    public function write(string $message)
    {
        $text = date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL;
        $handler = fopen($this->filename, 'a');
        fwrite($handler, $text);
        fclose($handler);
    }
}
