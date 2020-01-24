<?php


namespace src\services\UAC;

use src\entity\ObjectGUI;
use src\services\Transact\ExtPDO as PDO;

class PermissoesGUI extends ObjectGUI
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

        if (!empty($this->pesquisa['pesq_num'])) {
            $where = "WHERE P.GRUPO = :grupo";
            $where2 = "WHERE G.HANDLE = :grupo";
        }

        // puxa dados do grupo
        $sql = "SELECT G.* FROM K_FN_GRUPOUSUARIO G {$where2}";
        $stmt = $conexao->prepare($sql);
        if (!empty($this->pesquisa['pesq_num'])) $stmt->bindValue(':grupo', $this->pesquisa['pesq_num']);
        $stmt->execute();

        $grupo = $stmt->fetchAll(PDO::FETCH_OBJ);

        // puxa dados de usuário
        $sql = "SELECT A.* FROM K_PD_ALCADAS A";
        $stmt = $conexao->prepare($sql);
        $stmt->execute();

        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        $arr = array();
        foreach ($f as $r){
            $arr[$r->HANDLE]["alcada"] = $r->HANDLE;
            $arr[$r->HANDLE]["nome"] = $r->NOME;
            $arr[$r->HANDLE]["compartilhado"] = $r->COMPARTILHADO;
            $arr[$r->HANDLE]["grupo"] = $grupo[0]->NOME;
            $arr[$r->HANDLE]["nivel"] = 0;
        }

        // puxa dados de usuário
        $sql = "SELECT P.*
                FROM K_FN_PERMISSOES P 
                {$where}";
        $stmt = $conexao->prepare($sql);
        if (!empty($this->pesquisa['pesq_num'])) $stmt->bindValue(':grupo', $this->pesquisa['pesq_num']);
        $stmt->execute();

        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        foreach ($f as $r) {
            $arr[$r->ALCADA]["nivel"] = $r->NIVEL;
        }

        $this->itens[] = $arr;
    }
}