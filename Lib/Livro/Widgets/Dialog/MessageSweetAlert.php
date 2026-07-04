<?php

namespace Livro\Widgets\Dialog;

use Livro\Widgets\Base\Element;
use Override;

class MessageSweetAlert extends Element
{
    #[Override]
    public function __construct(string $type, string $message)
    {
        // Mapeia os tipos do seu sistema para os tipos nativos do SweetAlert2
        // O Bootstrap usa 'danger', mas o SweetAlert2 usa 'error'
        $swalType = ($type == 'error') ? 'error' : 'info';
        $title = ($type == 'error') ? 'Ops...' : 'Informação';

        // Abre a tag de script para o navegador executar o JavaScript
        echo "<script>";
        echo "
            // Garante que o SweetAlert2 existe antes de chamar
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '{$title}',
                    text: '{$message}',
                    icon: '{$swalType}',
                    confirmButtonText: 'Fechar'
                });
            } else {
                alert('{$message}');
            }
        ";
        echo "</script>";
    }
}