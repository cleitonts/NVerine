<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 14/02/2019
 * Time: 14:34
 */

namespace src\entity;


class ListaPrecoItemETT extends ListaPrecoETT
{
    public $produto;
    public $valor;
    public $perc_desconto;
    public $valor_bruto;
    public $prod_nome;

    public function cadastra(){
        parent::setPercDesconto($this->perc_desconto);
        parent::setProduto($this->produto);
        parent::setValor($this->valor);
        parent::cadastra();
    }
}