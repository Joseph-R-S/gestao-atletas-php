<?php

namespace Livro\Control;

interface ActionInterface
{
    /**
     * Transforma la acción en una cadena de tipo URL Query String
     * * @return string|null
     */
    public function serialize();
}