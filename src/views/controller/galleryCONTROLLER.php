<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 03/07/2019
 * Time: 12:43
 */

namespace src\views\controller;


use src\entity\GaleriaETT;
use src\views\ControladoraCONTROLLER;

class galleryCONTROLLER implements ControladoraCONTROLLER
{

    public function pesquisaGUI()
    {
        return "Não implementado";
    }

    public function singleGUI()
    {
        return "Não implementado";
    }

    /**
     * @param $obj
     * neste caso somente persist
     */
    public function persist($obj)
    {
        // garante que seja processado
        set_time_limit(120);

        global $mensagens;
        $mensagens->retorno = '?pagina=pessoa';

        // diferenciar upload de delete e atualização
        if(!empty($_FILES)){
            foreach ($_FILES as $file){
                $galeria = new GaleriaETT($_REQUEST["target"]);
                $galeria->nome = utf8_decode($_REQUEST["nome"]);
                $galeria->referencia = $_REQUEST["referencia"];
                $galeria->legenda = $_REQUEST["legenda"];
                $galeria->upload($file);
            }
        }
        else{
            //checa se tem deletar
            if($_REQUEST["deletar"] == "true"){
                $galeria = new GaleriaETT($_REQUEST["target"]);
                $galeria->nome = $_REQUEST["nome"];
                $galeria->referencia = $_REQUEST["referencia"];
                $galeria->delete();
            }
            // apenas atualiza
            else{
                $galeria = new GaleriaETT($_REQUEST["target"]);
                $galeria->nome = $_REQUEST["nome"];
                $galeria->referencia = $_REQUEST["referencia"];
                $galeria->legenda = $_REQUEST["legenda"];
                $galeria->atualiza($_REQUEST["old_nome"]);
            }
        }
    }
}