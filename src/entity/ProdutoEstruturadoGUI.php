<?php


namespace src\entity;
use src\services\Transact\ExtPDO as PDO;


class ProdutoEstruturadoGUI extends ObjectGUI implements InterfaceGUI
{


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

        // inicializa array
        $this->itens = array();

        // puxa dados
        $sql = "SELECT E.*, P.NOME AS NOMEPAI, F.NOME AS NOMEFILHO,
				(P.MARGEMLUCRO / (P.PRECOVENDA - P.MARGEMLUCRO)) AS MARKUP, 
				F.CUSTOCOMPRAS, F.UNIDADEMEDIDAVENDAS AS UNIDADE, F.PRECOVENDA,
				UM.ABREVIATURA AS NOMEUNIDADE
				FROM K_FN_PRODUTOESTRUTURADO E
				LEFT JOIN PD_PRODUTOS P ON E.PAI = P.CODIGO
				LEFT JOIN PD_PRODUTOS F ON E.FILHO = F.CODIGO
				LEFT JOIN CM_UNIDADESMEDIDA UM ON F.UNIDADEMEDIDAVENDAS = UM.HANDLE
				WHERE E.PAI = :pai";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":pai", $this->pesquisa['pesq_pai']);
        $stmt->execute();

        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        $i = 0;
        // insere no array
        foreach($f as $r) {
            $item = new ProdutoEstruturadoETT();
            $item->cont = $i;
            $item->handle = $r->HANDLE;
            $item->pai = formataCase($r->NOMEPAI);
            $item->filho = formataCase($r->NOMEFILHO);
            $item->cod_pai = $r->PAI;
            $item->cod_filho = $r->FILHO;
            $item->unidade = strtoupper($r->NOMEUNIDADE);
            $item->quantidade = $r->QUANTIDADEFLOAT;
            $item->valor_unitario = $r->PRECOVENDA;
            $item->valor_total = $item->valor_unitario * $item->quantidade;
            $item->markup = $r->MARKUP;
            // $item->custo_compra = $r->CUSTOCOMPRAS;

            array_push($this->itens, $item);
            $i++;
        }
    }

}