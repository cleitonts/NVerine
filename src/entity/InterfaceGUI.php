<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 11/02/2019
 * Time: 14:19
 */

namespace src\entity;


interface InterfaceGUI
{
    /**
     * InterfaceGUI constructor.
     * @param null $handle
     * entre outras coisas no momento da inicializa��o,
     * monta o header com nomes de colunas para os relatorios
     */
    public function __construct($handle = null);

    /**
     * @param $linha
     * @param $coluna
     * @return mixed
     * valor dos campos para exibir nos relatorios
     */
    public function getCampo($linha, $coluna);

    /**
     * @return mixed
     * mapeia os itens salvos no banco de dados e transforma em um array de ETT
     */
    public function fetch();
}