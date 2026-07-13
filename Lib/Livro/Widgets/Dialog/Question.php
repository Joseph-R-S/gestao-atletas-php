<?php

namespace Livro\Widgets\Dialog;

use Livro\Widgets\Base\Element;
use Livro\Control\Action;

class Question extends Element
{
    public function __construct(string $message, Action $action_yes, ?Action $action_no = null)
    {
        $div = new Element('div');
        $div->class = 'alert alert-warning alert-dismissible fade show d-flex align-items-center" role="alert"';

        $url_yes = $action_yes->serialize();

        $link_yes = new Element('a');
        $link_yes->href = $url_yes;
        $link_yes->class = 'btn btn-danger';
        $link_yes->style = 'float:right';
        $link_yes->add('Sim');

        $message .= '&nbsp' . $link_yes;

        if ($action_no) {
            $url_no = $action_no->serialize();
            $link_no = new Element('a');
            $link_no->href = $url_no;
            $link_no->class = 'btn btn-default';
            $link_no->style = 'float:right';
            $link_no->add('Não');

            $message .= '&nbsp' . $link_no;
        }
        $div->add($message);
        $div->show();
    }
}
