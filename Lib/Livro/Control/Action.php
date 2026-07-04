<?php

namespace Livro\Control;

class Action implements ActionInterface
{
    private mixed $action;
    private array $param = [];

    public function __construct(Callable $action)
    {
        $this->action = $action;
    }

    public function setParameter(string $param, mixed $value)
    {
        $this->param[$param] = $value;
    }

    //transforma a ação em uma string tipo url
    public function serialize()
    {
        //verofca se a ação é um método
        if (is_array($this->action)) {
            //obtem nome da classe
            $url['class'] = is_object($this->action[0]) ? get_class($this->action[0]) : $this->action[0];
            //obtem nome do metodo
            $url['method'] = $this->action[1];

            //verifca se há parametros
            if ($this->param) {
                $url = array_merge($url, $this->param);
            }

            return '?' . http_build_query($url);
        }
    }
}
