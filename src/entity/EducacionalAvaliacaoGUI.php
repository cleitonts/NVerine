<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 09/10/2019
 * Time: 08:59
 */

namespace src\entity;

use src\services\Transact\ExtPDO as PDO;

class EducacionalAvaliacaoGUI extends ObjectGUI
{
// construtor obrigatório
    public function __construct($handle = 0)
    {
        $this->header = array("cód.", "Nome", "Turma", "Disciplina", "Data");
    }

    // métodos públicos
    public function getCampo($linha, $coluna)
    {
        // indexa o item
        $item = $this->itens[$linha];

        // para a coluna, retorna um array com o valor e a classe a aplicar
        switch ($coluna) {
            case 0:
                return campo($item->handle);
            case 1:
                return campo($item->nome);
            case 2:
                return campo($item->turma);
            case 3:
                return campo($item->disciplina);
            case 4:
                return campo($item->data->format("d-m-Y"));
        }
    }

    public function fetch()
    {
        global $conexao;

        $this->itens = array();
        $where = "WHERE " . filtraFilial("T.FILIAL", "Educacional");

        // monta query de pesquisa
        if (!empty($this->pesquisa["pesq_num"])) $where .= "AND A.HANDLE = :handle \n";
        if (!empty($this->pesquisa["pesq_turma"])) $where .= "AND A.TURMA = :turma \n";
        if (!empty($this->pesquisa["pesq_disciplina"])) $where .= "AND A.DISCIPLINA = :disciplina \n";
        if (!empty($this->pesquisa["pesq_data_inicial"])) $where .= "AND A.DATA >= :data_inicial \n";
        if (!empty($this->pesquisa["pesq_data_final"])) $where .= "AND A.DATA <= :data_final \n";

        $sql = "SELECT {$this->top} A.*,
				D.NOME AS NOMEDISCIPLINA,
				T.NOME AS NOMETURMA, T.ANO, T.PERIODO, T.SEGMENTO
				FROM K_TURMAAVALIACAO A
				LEFT JOIN K_DISCIPLINA D ON A.DISCIPLINA = D.HANDLE
				LEFT JOIN K_TURMA T ON A.TURMA = T.HANDLE
				{$where}
				ORDER BY A.DISCIPLINA ASC, A.HANDLE ASC";
        $stmt = $conexao->prepare($sql);

        // mapeando filtros
        if (!empty($this->pesquisa["pesq_num"])) $stmt->bindValue(":handle", $this->pesquisa["pesq_num"]);
        if (!empty($this->pesquisa["pesq_turma"])) $stmt->bindValue(":turma", $this->pesquisa["pesq_turma"]);
        if (!empty($this->pesquisa["pesq_disciplina"])) $stmt->bindValue(":disciplina", $this->pesquisa["pesq_disciplina"]);
        if (!empty($this->pesquisa["pesq_data_inicial"])) $stmt->bindValue(":data_inicial", converteData($this->pesquisa["pesq_data_inicial"]));
        if (!empty($this->pesquisa["pesq_data_final"])) $stmt->bindValue(":data_final", converteData($this->pesquisa["pesq_data_final"]));


        $stmt->execute();
        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        // itera os itens
        $i = 0;

        if (!empty($f)) {
            foreach ($f as $r) {
                $item = new EducacionalAvaliacaoETT();
                $item->cont = $i;

                $item->handle = $r->HANDLE;
                $item->turma = formataCase($r->NOMETURMA, true);
                $item->disciplina = formataCase($r->NOMEDISCIPLINA, true);
                $item->cod_turma = $r->TURMA;
                $item->cod_disciplina = $r->DISCIPLINA;
                $item->nome = $r->NOME;
                $item->descricao = $r->DESCRICAO;
                $item->peso = $r->PESO;
                $item->data = new \DateTime($r->DATA);
                $item->conteudo = $r->CONTEUDO;
                $item->ano = $r->ANO;
                $item->periodo = $r->PERIODO;
                $item->cod_segmento = $r->SEGMENTO;

                // insere no array e incrementa o contador
                array_push($this->itens, $item);
                $i++;
            }
        }
    }
}