<?php

namespace Livro\Core;

class ClassLoader
{
    private $prefixes = [];

    public function addNamespace(string $prefix, string $dir)
    {
        // Garante que o diretório termina com a barra correta
        $this->prefixes[$prefix] = rtrim($dir, '/') . '/';
    }

    public function register()
    {
        spl_autoload_register([$this, 'loadClass']);
    }

    public function loadClass(string $class)
    {
        foreach ($this->prefixes as $prefix => $dir) {
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) === 0) {
                $relativeClass = substr($class, $len);
                $file = $dir . str_replace('\\', '/', $relativeClass) . '.php';
                
                if (file_exists($file)) {
                    require_once $file;
                    return true;
                }
            }
        }
        return false;
    }
}