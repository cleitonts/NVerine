<?php
spl_autoload_register(function ($class) {
    include str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';
});

include ("loader.php");

global $conexao;
global $dumper;

const __NO_ERROR_VIEW__ = true; // bloqueia erros que aparecem somente na view

// define p�gina a incluir
$pag = strtolower($_REQUEST["pagina"]);// define p�gina a incluir

// instancia mensagens globais
$mensagens = new \src\services\SysMessages\Messages();

if ($_REQUEST["act"] == "login"){
    $mensagens->retorno = "refresh";

    $login = new \src\services\UAC\Login();
    $login->usuario = $_REQUEST["usuario"];
    $login->senha = safercrypt($_REQUEST["senha"]);
    $login->logar();
    $mensagens->pronto(); // s� isso!
    die();
}

// so mostra se estiver logado
elseif(isset($_SESSION["ID"])) {
    // envio de form que alteram as propriedades do config.ini
    if(in_array($_REQUEST["pagina"], array("toggledebug", "toggleverempresa", "atualizaheaders")) ){
        $mensagens->retorno = "refresh";

        iniciaTransacao($conexao);

        switch ($_REQUEST["pagina"]) {
            case "toggledebug":

                if(__GLASS_DEBUG__) {
                    $mensagem = "desligar";
                    $v1 = "true";
                    $v2 = "false";
                }
                else {
                    $mensagem = "ligar";
                    $v1 = "false";
                    $v2 = "true";
                }

                $transact->mensagem("Tentanto {$mensagem} o Debug");

                // guarda o valor anterior para compara��o
                $mensagem_anterior = $mensagem;

                /* tenta alterar o arquivo .ini em uploads.
                 * outras pastas n�o devem ser acess�veis por seguran�a!
                 *
                 * note aqui que o espa�o entre o operador '=' e os �tomos � importante
                 */
                shell_exec("sed -i -e 's/debug = {$v1}/debug = {$v2}/g' uploads/config.ini.php");

                // tenta gerar uma nova conex�o para validar
               $transact->mensagem("O sistema tentar� reiniciar a conex�o para validar as mudan�as", MSG_ERRO);

                break;
            case "toggleverempresa":
                if($_SESSION["ver_empresa"]) {
                    $_SESSION["ver_empresa"] = false;
                    $transact->mensagem("Voc� est� agora visualizando apenas a filial ativa.", MSG_ERRO);
                }
                else {
                    $_SESSION["ver_empresa"] = true;
                    $transact->mensagem("Voc� est� agora visualizando todas as filiais da empresa atual.", MSG_ERRO);
                }
                break;
            case "atualizaheaders":
                $tabela = new \src\services\Metadados\TabelasETT();
                $tabela->nome_tabela = utf8_decode($_REQUEST["tabela"]);
                $tabela->limpa();

                foreach ($_REQUEST["inativo"] as $r){
                    $tabela->coluna = $r["num"];
                    $tabela->posicao = null;
                    $tabela->cadastra();
                }

                foreach ($_REQUEST["ativo"] as $r){
                    $tabela->coluna = $r["num"];
                    $tabela->posicao = $r["pos"];
                    $tabela->cadastra();
                }
                mensagem("Relat�rio atualizado atualize a tela para aplicar", MSG_ERRO);
                break;
            default:
                mensagem("burrice", MSG_ERRO);
        }

        redir($conexao);
    }

    // instancia o form para fazer a tradu��o
    $pagina = str_replace(".php", "", $pag)."FORM"; // sanitiza
    $arq = "src/views/forms/{$pagina}";
    $pagina = str_replace("/", "\\", $arq);

    // controladora
    $pagina2 = str_replace(".php", "", $pag)."CONTROLLER"; // sanitiza
    $arq2 = "src/views/controller/{$pagina2}";
    $pagina2 = str_replace("/", "\\", $arq2);

    if(file_exists("{$arq}.php")) {
        include_once($arq . "php");
        $temp = new $pagina();
        $base = $temp->createForm();
    }

    // n�o faz tradu��o caso seja apenas cadastro
    if(is_object($base)){
        $tradutor = new src\services\Translate();
        $traduzido = $tradutor->translate($_REQUEST, $base);
    }

    // se chegou aqui este arquivo deve existir tamb�m
    iniciaTransacao($conexao);
        include_once($arq2."php");
        $temp2 = new $pagina2();
        $temp2->persist($traduzido);
    redir($conexao);

}