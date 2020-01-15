<?php

namespace src\services\Metadados;

use src\services\Transact\ExtPDO as PDO;

class TabelasGUI extends \src\entity\ObjectGUI
{
    private $tabela;

    public static function exibe(&$gui, $tabela)
    {
        $itens = new TabelasGUI();
        $itens->tabela = $tabela;
        $itens->fetch();

        $gui->exibe = $itens->itens;
    }

    /**
     * @param $linha
     * @param $coluna
     * @return mixed
     * valor dos campos para exibir nos relatorio
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
        global $conexao;

        //********************* manter simples para uma rápida execução ********************************//

        // tras os indices da lista
        $sql = "SELECT COLUNA FROM METADADOS_TABELAS 
                WHERE NOME_TABELA = '{$this->tabela}' AND USUARIO = {$_SESSION["ID"]} AND POSICAO IS NOT NULL
                ORDER BY POSICAO, HANDLE ASC";
        $stmt = $conexao->prepare($sql);
        $stmt->execute();

        $listas = $stmt->fetchAll(PDO::FETCH_OBJ);
        $i = 0;
        foreach ($listas as $r){
            $this->itens["e{$r->COLUNA}"] = $i;
            $i++;
        }
    }
}