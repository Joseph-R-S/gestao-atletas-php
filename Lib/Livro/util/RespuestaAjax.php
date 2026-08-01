<?php
namespace Livro\util;

class RespuestaAjax
{
    public static function setValue($target, $data)
    {
        echo json_encode([
            'tipo'   => 'setValue',
            'target' => $target,
            'data'   => $data
        ]);
        exit;
    }

    public static function appendHTML($target, $html)
    {
        echo json_encode([
            'tipo'   => 'appendHTML',
            'target' => $target,
            'html'   => $html
        ]);
        exit;
    }

    public static function removerElemento($target)
    {
        echo json_encode([
            'tipo'   => 'removerElemento',
            'target' => $target
        ]);
        exit;
    }

}