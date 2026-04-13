<?php
require_once __DIR__ . '/vendor/autoload.php';

spl_autoload_register(function ($class_name) {
    $class = str_replace('App\\', '', $class_name);
    $path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $file = __DIR__ . '/App/' . $path . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader, [
    'debug' => true,
]);
$twig->addExtension(new \Twig\Extension\DebugExtension());

$classeOriginal = $_GET['class'] ?? 'AtletaList';
$metodo = $_GET['method'] ?? 'show';

$classeComNamespace = "App\\Controllers\\" . $classeOriginal;
if (class_exists($classeComNamespace)) {
    // Injeta o Twig no construtor da classe
    $pagina = new $classeComNamespace($twig);

    if (method_exists($pagina, $metodo)) {
        $dados = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST : $_GET;
        $pagina->$metodo($dados);

        if ($metodo !== 'show' && method_exists($pagina, 'show')) {
            $pagina->show();
        }
    } else {
        echo "Método $metodo não encontrado na classe $classeComNamespace.";
    }
} else {
    echo "Classe $classeComNamespace não encontrada.";
}
