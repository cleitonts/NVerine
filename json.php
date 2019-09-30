<?php
/*
 * landing page: integra��es/requests formatados em JSON
 */

spl_autoload_register(function ($class) {
    include str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';
});

// precisamos permitir integra��o de todos os sites?
header("access-control-allow-origin: *");

include("loader.php");

// puxa as permiss�es do usu�rio logado
$permissoes = new \src\services\UAC\PermissionsDummy();

// instancia conex�o global
global $conexao;

// hack: passe codifica��o certa para a busca (seria melhor aplicar um array_map?)
if(isset($_GET["term"])) $_GET["term"] = utf8_decode($_GET["term"]);

// busca p�gina
if(isset($_GET["pagina"])) {
    $pag = $_GET["pagina"];

    // voc� � burro
    if(strpos($pag, ".php") !== false) {
        $pag = "src/views/json/{$pag}";
    }
    else {
        $pag = "src/views/json/{$pag}.php";
    }

    if(file_exists($pag)) {
        include($pag);
    }
    else {
        echo "Erro interno: arquivo '{$pag}' nao existe";
    }
}
