<?php

namespace Livro\Widgets\Dialog;

use Livro\Widgets\Base\Element;
use Livro\Control\Action;

class Question extends Element
{
    public function __construct(string $message, Action $action_yes, ?Action $action_no = null)
    {
        // Serializa a ação do botão "Sim"
        $url_yes = $action_yes->serialize();
        
        // Verifica se existe uma ação para o "Não", caso contrário define como falso no JS
        $url_no = $action_no ? $action_no->serialize() : false;

        echo "<script>";
        echo "
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Confirmação',
                    text: '{$message}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sim',
                    cancelButtonText: 'Não',
                    // Impede o usuário de fechar clicando fora ou apertando Esc
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Se clicou em Sim, redireciona para a URL do PHP
                        window.location.href = '{$url_yes}';
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        // Se clicou em Não
                        if ('{$url_no}' !== '') {
                            window.location.href = '{$url_no}';
                        }
                    }
                });
            } else {
                // Fallback com o confirm nativo do navegador caso o SweetAlert falhe
                if (confirm('{$message}')) {
                    window.location.href = '{$url_yes}';
                } else if ('{$url_no}' !== '') {
                    window.location.href = '{$url_no}';
                }
            }
        ";
        echo "</script>";
    }
}