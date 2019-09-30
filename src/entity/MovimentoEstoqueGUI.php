<?php

namespace src\entity;

use ExtPDO as PDO;
use src\entity\ObjectGUI;
use src\entity\ProdutoETT;
use src\entity\ProdutoGUI;
use src\entity\InterfaceGUI;

Class MovimentoEstoqueGUI extends ObjectGUI implements InterfaceGUI {


    /**
     * @param $linha
     * @param $coluna
     * @return mixed
     * valor dos campos para exibir nos relatorios
     */
    public function getCampo($linha, $coluna)
    {
        // TODO: Implement getCampo() method.
    }

    /**
     * @return mixed
     * mapeia os itens salvos no banco de dados e transforma em um array de ETT
     */
    public function fetch()
    {
        // TODO: Implement fetch() method.
    }
}