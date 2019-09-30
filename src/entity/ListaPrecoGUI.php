<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 11/02/2019
 * Time: 13:41
 */

namespace src\entity;

use src\services\Transact\ExtPDO as PDO;

class ListaPrecoGUI extends ObjectGUI implements InterfaceGUI
{
    public function __construct($handle = null)
    {
        $this->handle = $handle;

        $this->header = array(
            "Índice", "Nome", "Data inicio", "Data fim", "Global"
        );
    }

    public function getCampo($linha, $coluna)
    {
        // indexa o item
        $item = $this->itens[$linha];

        // para a coluna, retorna um array com o valor e a classe a aplicar
        switch ($coluna)
        {
            case 0:        return campo($item->indice);
            case 1:        return campo($item->nome);
            case 2:        return campo($item->data_inicio);
            case 3:        return campo($item->data_fim);
            case 4:        return campo($item->ativo);
        }
    }

    public function fetch()
    {
        global $conexao;

        $where = "WHERE 1 = 1 \n";
        // caso seja edição
        if (!empty($this->handle)) {
            $where .= "AND L.INDICE = :indice \n";
        }

        if(!empty($this->pesquisa["pesq_global"])){
            $where .= "AND L.ATIVO = :global \n";
        }

        // tras os indices da lista
        $sql = "SELECT L.NOME, L.DATAINICIO, L.DATAFIM, L.INDICE, L.ATIVO
				FROM K_LISTAPRECO L
				{$where}
				GROUP BY L.INDICE, L.NOME, L.DATAINICIO, L.DATAFIM, L.ATIVO
				ORDER BY L.INDICE DESC \n";
        $stmt = $conexao->prepare($sql);

        if (!empty($this->handle)) $stmt->bindValue(":indice", $this->handle);
        if(!empty($this->pesquisa["pesq_global"])) $stmt->bindValue(":global", $this->pesquisa["pesq_global"]);

        $stmt->execute();
        $listas = $stmt->fetchAll(PDO::FETCH_OBJ);

        $i = 0; //inicia contador

        if(!empty($listas)) {
            foreach ($listas as $r) {
                $item = new ListaPrecoETT();

                //CABEÇALHO DA LISTA
                $item->handle = $r->INDICE;
                $item->indice = $r->INDICE;
                $item->nome = $r->NOME;
                $item->data_inicio = $r->DATAINICIO;
                $item->data_fim = $r->DATAFIM;
                $item->ativo = $r->ATIVO;

                if (!empty($this->handle)) {
                    $itens = new ListaPrecoItemGUI($item->indice);
                    $itens->fetch();
                    $item->produtos = $itens->itens;
                }

                $this->itens[] = $item;
                $i++;
            }
        }
    }
}