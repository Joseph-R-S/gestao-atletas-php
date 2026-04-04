<?php
// 1. Carrega as dependências
require_once __DIR__ . '/vendor/autoload.php';

// 2. Autoload das suas classes
spl_autoload_register(function ($class_name) {
    $file = __DIR__ . '/Classes/' . $class_name . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// 3. Configura o Twig uma única vez para o sistema todo
$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader, [
    'debug' => true,
]);
$twig->addExtension(new \Twig\Extension\DebugExtension());

// 4. Lógica de Roteamento (Qual página abrir?)
$classe = $_GET['class'] ?? 'AtletaList';
$metodo = $_GET['method'] ?? 'show';

if (class_exists($classe)) {
    // Injeta o Twig no construtor da classe
    $pagina = new $classe($twig);

    if (method_exists($pagina, $metodo)) {
        // Chama o método (show, edit, save, delete) passando o $_GET ou $_POST
        $dados = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST : $_GET;
        $pagina->$metodo($dados);

        // se o método chamado NÃO for o show, chama o show agora
        if ($metodo !== 'show' && method_exists($pagina, 'show')) {
            $pagina->show();
        }
        //$pagina->$metodo($dados);
    } else {
        echo "Método $metodo não encontrado na classe $classe.";
    }
} else {
    echo "Classe $classe não encontrada.";
}
