<?php


namespace src\views;


use src\creator\widget\Tools;

class ControladoraRELATORIO implements ControladoraCONTROLLER
{

    public function pesquisaGUI()
    {
        // TODO: Implement pesquisaGUI() method.
    }

    public function singleGUI()
    {
        return Tools::returnError("Este m�todo n�o pode ser invocado");
    }

    public function persist($obj)
    {
        return Tools::returnError("Este m�todo n�o pode ser invocado");
    }
}