<?php
namespace src\entity;
use ExtPDO as PDO;


class FornecedoresGUI extends ObjectGUI implements InterfaceGUI
{

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
        $sql = "SELECT INDICE, PORCENTAGEM, NOME, QUANTIDADE FROM K_FN_TABELAPRECOS";
        $smtp = $conexao->prepare($sql);
        $smtp->execute();
        $tabelas = $smtp->fetchAll(\PDO::FETCH_OBJ);

        if(!empty($tabelas)){
            foreach($tabelas as $key => $t){
                $item = new TabelaPrecosETT();
                $item->indice = $t->INDICE;
                $item->porcentagem = $t->PORCENTAGEM;
                $item->nome = $t->NOME;
                $item->quantidade = $t->QUANTIDADE;
                $this->itens[] = $item;
            }
        }
    }
}
