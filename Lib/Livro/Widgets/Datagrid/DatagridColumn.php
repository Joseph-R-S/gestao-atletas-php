<?php

namespace Livro\Widgets\Datagrid;

use Livro\Control\ActionInterface;

class DatagridColumn
{
    private string $name; //atributo do banco
    private string $label;
    private string $align;
    private string $width;
    private ?ActionInterface $action = null;
    private mixed $transformer = null;

    public function __construct(string $name, string $label, string $align, string $width)
    {
        $this->name = $name;
        $this->label = $label;
        $this->align = $align;
        $this->width = $width;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getAlign(): string
    {
        return $this->align;
    }

    public function getWidth(): string
    {
        return $this->width;
    }

    public function setAction(ActionInterface $action): void
    {
        $this->action = $action;
    }

    // El signo '?' le avisa a PHP que el retorno puede ser la interfaz O un valor null
    public function getAction() : ?ActionInterface
    {
        return isset($this->action) ? $this->action : null;
    }

    public function setTransformer(callable $callback)
    {
        $this->transformer  = $callback;
    }

    public function getTransformer() : mixed
    {
        return $this->transformer ? $this->transformer : null;
    }
}