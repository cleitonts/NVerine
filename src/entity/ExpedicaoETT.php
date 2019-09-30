<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 01/02/2019
 * Time: 23:10
 */

namespace src\entity;

include_once("class/Faturamento.php");

use Faturamento\Nota;
use Faturamento\ProdutoServico;
use ExtPDO as PDO;

class ExpedicaoETT extends Nota
{
    public function __construct()
    {
        // cria um item vazio, para montar tabela do formulario
        $this->produtos[] = new ProdutoServico(null);
    }
}