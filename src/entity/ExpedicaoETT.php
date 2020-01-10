<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 01/02/2019
 * Time: 23:10
 */

namespace src\entity;

class ExpedicaoETT extends FaturamentoETT
{
    public function __construct()
    {
        // cria um item vazio, para montar tabela do formulario
        $this->produtos[] = new FaturamentoProdutoServicoETT(null);
    }
}