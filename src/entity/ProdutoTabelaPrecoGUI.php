<?php


namespace src\entity;

use src\services\Transact\ExtPDO as PDO;

class ProdutoTabelaPrecoGUI extends ObjectGUI implements InterfaceGUI
{
    public $pesquisa = array();

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
        global $conexao;
        $sql = "SELECT HANDLE, INDICE, PORCENTAGEM, NOME, QUANTIDADE FROM K_FN_TABELAPRECOS WHERE PRODUTO = :codigo";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":codigo", $this->pesquisa["pesq_produto"]);
        $stmt->execute();
        $tabelas = $stmt->fetchAll(PDO::FETCH_OBJ);

        if(!empty($tabelas)){
            foreach($tabelas as $key => $t){
                $item = new ProdutoTabelaPrecoETT();
                $item->handle = $t->HANDLE;
                $item->indice = $t->INDICE;
                $item->perc_tab = formataValor($t->PORCENTAGEM);
                $item->nome = $t->NOME;
                $item->qtd_tab = $t->QUANTIDADE;
                $this->itens[] = $item;
            }
        }
    }
}
