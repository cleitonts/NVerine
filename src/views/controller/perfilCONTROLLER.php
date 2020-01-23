<?php


namespace src\views\controller;

use src\creator\widget\Tools;
use src\views\ControladoraCONTROLLER;
use src\views\forms\perfilFORM;

global $__MODULO__;
global $__PAGINA__;
$__MODULO__ = 'Principal';
$__PAGINA__ = 'Perfil';

class perfilCONTROLLER implements ControladoraCONTROLLER
{

    public function pesquisaGUI()
    {
        Tools::render(Tools::returnError("Método não instanciado!"));
    }

    public function singleGUI()
    {
        $pesquisa = new perfilFORM();
        Tools::render($pesquisa->createForm(intval($_REQUEST['pesq_num'])));
    }

    public function persist($obj)
    {
        global $mensagens;

        // retorna para a index para não causar erro
        $mensagens->retorno = "index.php";

        if($_REQUEST["senha_nova"] != $_REQUEST["senha_nova2"]){
            mensagem("Os campos de senha precisam ser identicos!", MSG_ERRO);
            finaliza();
        }

        $obj->senha = $_REQUEST["senha_nova"];
        $obj->senha_atual = $_REQUEST["senha_antiga"];
        $obj->atualiza();
    }
}