<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 18/01/2019
 * Time: 13:38
 */

namespace src\views;


interface ControladoraCONTROLLER
{
    public function pesquisaGUI();
    public function singleGUI();
    public function persist($obj);
}
