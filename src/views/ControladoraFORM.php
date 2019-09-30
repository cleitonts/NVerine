<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 01/02/2019
 * Time: 22:05
 */

namespace src\views;


use src\creator\widget\Widget;

interface ControladoraFORM
{
    public function createSearch();
    public function createForm($handle = null);
}