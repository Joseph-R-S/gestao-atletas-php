<?php

namespace Livro\Core;

class AppLoader
{
    private $directories = [];

    public function addDirectory(string $dir)
    {
        $this->directories[] = rtrim($dir, '/');
    }

    public function register()
    {
        spl_autoload_register([$this, 'loadClass']);
    }

    public function loadClass(string $class)
    {
        // Remove qualquer namespace se a classe vier com um (ex: App\Services\PessoaServices vira PessoaServices)
        // Isso ajuda o AppLoader a buscar puramente pelo nome do arquivo nas pastas adicionadas
        $parts = explode('\\', $class);
        $className = end($parts);

        foreach ($this->directories as $dir) {
            $file = $dir . '/' . $className . '.php';
            if (file_exists($file)) {
                require_once $file;
                return true;
            }
        }
        return false;
    }
}