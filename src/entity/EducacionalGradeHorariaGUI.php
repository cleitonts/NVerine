<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 06/05/2019
 * Time: 16:28
 */

namespace src\entity;

use src\services\Transact\ExtPDO as PDO;

class EducacionalGradeHorariaGUI extends ObjectGUI
{
    // estas propriedades são usadas para os relatórios
    public $min_hora;
    public $max_hora;

    // construtor obrigatório
    public function __construct($handle = null) {
        $this->header = array("Não implementado");

        $this->min_hora = 24;
        $this->max_hora = 0;
    }

    // métodos públicos
    public function getCampo($linha, $coluna) {
        return campo("Não implementado");
    }

    public function fetch() {
        global $conexao;

        $this->itens = array();

        /* a pesquisa por aluno é um pouco mais complicada.
         * precisa de uma tabela adicional
         */
        if(!empty($this->pesquisa["pesq_aluno"])) {
            $where =
                "LEFT JOIN K_TURMAALUNO TA ON TA.TURMA = T.HANDLE
				WHERE TA.ALUNO = '".intval($this->pesquisa["pesq_aluno"])."' \n";
        }
        else {
            $where = "WHERE ".filtraFilial("T.FILIAL", "Educacional");
        }

        // monta query de pesquisa
        if(!empty($this->pesquisa["pesq_codigo"]))		$where .= "AND H.HANDLE = :handle \n";
        if(!empty($this->pesquisa["pesq_turma"]))		$where .= "AND H.TURMA = :turma \n";
        if(!empty($this->pesquisa["pesq_professor"]))	$where .= "AND H.PROFESSOR = :professor \n";
        if(!empty($this->pesquisa["pesq_periodo"]))		$where .= "AND T.PERIODO = :periodo \n";

        // se houver busca por data, tem que converter em dia da semana
        if(!empty($this->pesquisa["pesq_data"])) {
            $dia = diaDaSemana($this->pesquisa["pesq_data"]);
            if($dia == 0) $dia = 7; // conversão de domingo

            $where .= "AND H.DIASEMANA = '{$dia}' \n";
        }

        // busca dados
        $sql = "SELECT {$this->top}	H.*,
				P.NOME AS NOMEPROFESSOR, D.NOME AS NOMEDISCIPLINA,
				T.NOME AS NOMETURMA, T.ANO, T.PERIODO
				FROM K_TURMAHORARIO H
				LEFT JOIN K_FN_PESSOA P ON H.PROFESSOR = P.HANDLE
				LEFT JOIN K_DISCIPLINA D ON H.DISCIPLINA = D.HANDLE
				LEFT JOIN K_TURMA T ON H.TURMA = T.HANDLE
				{$where}
				ORDER BY H.DIASEMANA ASC, H.HORARIOINICIO ASC";
        $stmt = $conexao->prepare($sql);

        // mapeando filtros
        if(!empty($this->pesquisa["pesq_codigo"]))		$stmt->bindValue(":handle", $this->pesquisa["pesq_codigo"]);
        if(!empty($this->pesquisa["pesq_turma"]))		$stmt->bindValue(":turma", $this->pesquisa["pesq_turma"]);
        if(!empty($this->pesquisa["pesq_professor"]))	$stmt->bindValue(":professor", $this->pesquisa["pesq_professor"]);
        if(!empty($this->pesquisa["pesq_periodo"]))		$stmt->bindValue(":periodo", $this->pesquisa["pesq_periodo"]);

        $stmt->execute();
        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        // itera os itens
        $i = 0;

        if(!empty($f)) {
            foreach($f as $r) {
                $item = new EducacionalGradeHorariaETT();

                $item->handle = $r->HANDLE;
                $item->turma = formataCase($r->NOMETURMA, true);
                $item->professor = formataCase($r->NOMEPROFESSOR, true);
                $item->disciplina = formataCase($r->NOMEDISCIPLINA, true);
                $item->dia_semana = $item->getNomeDiaSemana($r->DIASEMANA);
                $item->cod_turma = $r->TURMA;
                $item->cod_professor = $r->PROFESSOR;
                $item->cod_disciplina = $r->DISCIPLINA;
                $item->cod_dia_semana = $r->DIASEMANA;
                $item->horario_inicio = $r->HORARIOINICIO;
                $item->horario_termino = $r->HORARIOTERMINO;
                $item->time_slot = $r->TIMESLOT;
                $item->ano = $r->ANO;
                $item->periodo = $r->PERIODO;

                // converte notação de horário
                $horas = left($item->horario_inicio, 2);
                $minutos = right($item->horario_inicio, 2);
                $item->hora_inicio = $horas + ($minutos / 60);

                $horas = left($item->horario_termino, 2);
                $minutos = right($item->horario_termino, 2);
                $item->hora_termino = $horas + ($minutos / 60);

                // calcula mínimos e máximos
                if($item->hora_inicio <= $this->min_hora) $this->min_hora = $item->hora_inicio;
                if($item->hora_termino >= $this->max_hora) $this->max_hora = $item->hora_termino;

                // insere no array e incrementa o contador
                array_push($this->itens, $item);
                $i++;
            }
        }
    }
}