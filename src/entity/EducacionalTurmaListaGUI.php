<?php
/**
 * Created by PhpStorm.
 * User: rafael
 * Date: 18/04/2019
 * Time: 11:47
 */

namespace src\entity;

use src\services\Transact\ExtPDO as PDO;

class EducacionalTurmaListaGUI extends ObjectGUI implements InterfaceGUI
{

    /**
     * InterfaceGUI constructor.
     * @param null $handle
     * entre outras coisas no momento da inicialização,
     * monta o header com nomes de colunas para os relatorio
     */
    public function __construct($handle = null)
    {
//        parent::__construct($handle);
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

        $this->itens = array();
        $where = " WHERE 1 = 1 \n ";

        // monta query de pesquisa
        if(!empty($this->pesquisa["pesq_lista"])) $where .= " AND T.TURMA = :turma \n";

        $sql = "SELECT T.*, P.NOME AS NOMEALUNO
                FROM K_TURMAALUNO AS T
				LEFT JOIN K_FN_PESSOA AS P ON T.ALUNO = P.HANDLE
				{$where} 
				ORDER BY T.NUMERO ASC";
        $stmt = $conexao->prepare($sql);

        // mapeando filtros
        if(!empty($this->pesquisa["pesq_lista"])) $stmt->bindValue(":turma", $this->pesquisa["pesq_lista"]);

        $stmt->execute();
        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        // itera os itens
        $i = 0;

        if(!empty($f)) {
            foreach($f as $r) {
                $item = new EducacionalTurmaListaETT();
                $item->cont = $i;
                $item->handle = $r->HANDLE;
                $item->aluno = $r->NOMEALUNO;
                $item->data_entrada = $r->DATAENTRADA;
                $item->data_saida = $r->DATASAIDA;
                $item->turma = $r->TURMA;
                $item->ativo = $r->ATIVO;
                $item->numero = $r->NUMERO;
                $item->turno = $r->TURNO;
                $item->situacao = $r->SITUACAO;
                $item->cod_aluno = $r->ALUNO;

                // insere no array e incrementa o contador
                array_push($this->itens, $item);
                $i++;
            }
        }

    }
}