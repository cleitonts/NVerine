<?php


namespace src\entity;


use PDO;

class TabelaPrecosGUI extends ObjectGUI implements InterfaceGUI
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
        $sql = "SELECT INDICE, PORCENTAGEM, NOME, QUANTIDADE FROM K_FN_TABELAPRECOS WHERE PRODUTO = :codigo";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":codigo", $this->pesquisa["pesq_prouto"]);
        $stmt->execute();
        $tabelas = $stmt->fetchAll(PDO::FETCH_OBJ);

        if(!empty($tabelas)){
            foreach($tabelas as $key => $t){
                $item = new TabelaPrecosETT();
                $item->indice = $t->INDICE;
                $item->perc_tab = $t->PORCENTAGEM;
                $item->nome = $t->NOME;
                $item->qtd_tab = $t->QUANTIDADE;
                $this->itens[] = $item;
            }
        }
    }
}
