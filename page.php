<?php

spl_autoload_register(function ($class) {
    include str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';
});

include("loader.php");

global $dumper;
global $permissoes;
global $perfil;

// so mostra se estive rlogado
if(isset($_SESSION["ID"])) {
    // define página a incluir
    $pagina = strtolower($_REQUEST["pagina"]);

    // define um fallback
    //if(empty($pagina)) $pagina = $perfil->pagina_inicial;
    if(empty($pagina)) $pagina = "pessoa";

    // busca conteúdo dinâmico
    $pagina_controller = str_replace(".php", "", $pagina)."CONTROLLER"; // sanitiza
    $arq_controller = "src/views/controller/{$pagina_controller}";
    $pagina_controller = str_replace("/", "\\", $arq_controller);

    // verifica se é um relatório
    if (substr($pagina, 0, 10) == "relatorio_") {
        $pagina = substr($pagina, 10);

        $pagina_controller = str_replace(".php", "", $pagina)."RELATORIO"; // sanitiza
        $arq_controller = "src/views/relatorio/{$pagina_controller}";
        $pagina_controller = str_replace("/", "\\", $arq_controller);
    }

    if(file_exists("{$arq_controller}.php")) {
        include_once($arq_controller."php");
        if(!isset($_REQUEST["pesq_num"])){
            $temp = new $pagina_controller();
            $temp->pesquisaGUI();
        }
        else{
            $temp = new $pagina_controller();
            $temp->singleGUI();
        }
    }
}