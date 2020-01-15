<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 14/02/2019
 * Time: 14:44
 */

namespace src\entity;

use src\services\Transact\ExtPDO as PDO;

class ListaPrecoItemGUI extends ObjectGUI implements InterfaceGUI
{
    public $indice;

    public function __construct($indice = null)
    {
        if(empty($indice)){
            return "rotina não implementada";
        }

        $this->indice = $indice;
    }

    public function fetch(){
        global $conexao;

        // filtra os itens
        $where = "WHERE L.INDICE = :indice \n";

        $sql = "SELECT L.* , 
                P.NOME AS PRODNOME, P.PRECOVENDA AS VALORBRUTO
                FROM K_LISTAPRECO L
                LEFT JOIN PD_PRODUTOS P ON L.PRODUTO = P.HANDLE
                {$where}
                ORDER BY L.INDICE DESC ";

        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":indice", $this->indice);
        $stmt->execute();
        $itens = $stmt->fetchAll(PDO::FETCH_OBJ);

        $i = 0;
        //mapeia os resultados single
        if(!empty($itens)) {
            foreach($itens as $r) {
                $item = new ListaPrecoItemETT();

                //CABEÇALHO DA LISTA
                $item->handle = $r->HANDLE;
                $item->indice = $r->INDICE;
                $item->nome = $r->NOME;
                $item->data_inicio = $r->DATAINICIO;
                $item->data_fim = $r->DATAFIM;
                $item->ativo = $r->ATIVO;

                //PRODUTOS
                $item->valor_bruto = $r->VALORBRUTO;
                $item->prod_nome = $r->PRODNOME;
                $item->valor = $r->VALOR;
                $item->produto = $r->PRODUTO;
                $item->perc_desconto = $r->PERCDESCONTO;

                array_push($this->itens, $item);
                $i++;
            }
        }
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
}