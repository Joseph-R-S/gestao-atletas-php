<?php

namespace Livro\Control;

use Livro\Widgets\Base\Element;

class Page extends Element
{
    public function __construct()
    {
        parent::__construct('div');
    }

    public function pageDestino(string $class, ?string $metodo, ?string $key = null, ?string $id = null)
    {
        $action = new Action([new $class, $metodo]);
        $action->setParameter('key', $key);
        $action->setParameter('id', $id);
        $url = $action->serialize();

        header("Location: {$url}");
        exit;
    }

    public function show()
    {
        if ($_GET) {
            $method = isset($_GET['method']) ? $_GET['method'] : '';
            if (method_exists($this, $method)) {
                call_user_func([$this, $method], $_GET);
            }
        }
        parent::show();
    }

}