<?php
namespace src\entity;

use src\services\Transact\ExtPDO as PDO;

class ProdutoFornecedorGUI extends ObjectGUI implements InterfaceGUI
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

        $where = "WHERE 1 = 1\n";
        if (!empty($this->pesquisa["pesq_produto"])) {
            $where .= "AND F.PRODUTO = :produto \n";
        }

        $sql = "SELECT F.*, P.NOME AS NOMEFORNECEDOR
                FROM PD_PRODUTOSFORNECEDORES F 
                LEFT JOIN K_FN_PESSOA P ON P.HANDLE = F.FORNECEDOR
                {$where}";

        $stmt = $conexao->prepare($sql);

        if (!empty($this->pesquisa["pesq_produto"])) $stmt->bindValue(":produto", $this->pesquisa["pesq_produto"]);
        $stmt->execute();
        
        $tabelas = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        $i = 0;
        if (!empty($tabelas)) {
            foreach ($tabelas as $t) {
                $item = new ProdutoFornecedorETT();
                $item->handle = $t->HANDLE;
                $item->cont = $i;
                $item->produto = $t->PRODUTO;
                $item->preco = $t->PRECOFORNECEDOR;
                $item->fornecedor = $t->NOMEFORNECEDOR;
                $item->cod_fornecedor = $t->FORNECEDOR;
                $item->codigo_fornecedor = $t->CODIGOFORNECEDOR;
                $this->itens[] = $item;
                $i++;
            }
        }
    }
}
