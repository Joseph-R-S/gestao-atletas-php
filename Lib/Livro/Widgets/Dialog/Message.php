<?php

namespace Livro\Widgets\Dialog;

use Livro\Widgets\Base\Element;

class Message extends Element
{
    public function __construct(string $type, string $message) 
    {
        $div = new Element('div');
        if ($type == 'info') {
            $div->class = 'alert alert-info';
        } 
        elseif ($type == 'error') {
            $div->class = 'alert alert-danger';
            $div->role = 'alert';
        }

        $div->add($message);
        $div->show();
    }
}
