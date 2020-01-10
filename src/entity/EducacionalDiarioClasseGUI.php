<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 25/06/2019
 * Time: 16:02
 */

namespace src\entity;

use src\services\Transact\ExtPDO as PDO;

class EducacionalDiarioClasseGUI extends ObjectGUI
{
    // propriedade para definir se é incluído o objeto horário
    public $usa_horario;

    // contadores de presença e falta (filtrar por aluno e turma)
    public $presencas;
    public $faltas;
    public $aulas;

    public $faltas_por_periodo;
    public $aulas_por_periodo;

    // construtor obrigatório
    public function __construct($handle = 0) {
        $this->header = array("Não implementado");
        $this->usa_horario = true;
        $this->presencas = 0;
        $this->faltas = 0;
        $this->aulas = 0;
    }

    // métodos públicos
    public function getCampo($linha, $coluna) {
        return campo("Não implementado");
    }

    public function fetch() {
        global $conexao;

        $this->itens = array();
        // $where = "WHERE ".filtraFilial("T.FILIAL", "Educacional");
        $where = "WHERE 1 = 1 \n";

        // mapeia filtros redundantes
        if(!empty($this->pesquisa["pesq_data_aula"])) $this->pesquisa["pesq_data"] = $this->pesquisa["pesq_data_aula"];

        // monta query de pesquisa
        if(!empty($this->pesquisa["pesq_codigo"])) 		$where .= "AND D.HANDLE = :handle \n";
        if(!empty($this->pesquisa["pesq_horario"])) 	$where .= "AND H.HANDLE = :horario \n";
        if(!empty($this->pesquisa["pesq_disciplina"]))	$where .= "AND H.DISCIPLINA = :disciplina \n";
        if(!empty($this->pesquisa["pesq_aluno"])) 		$where .= "AND P.HANDLE = :aluno \n";
        if(!empty($this->pesquisa["pesq_turma"]))		$where .= "AND T.HANDLE = :turma \n";
        if(!empty($this->pesquisa["pesq_periodo"]))		$where .= "AND T.PERIODO = :periodo \n";
        if(!empty($this->pesquisa["pesq_data"]))		$where .= "AND D.DATA = :data \n";
        if(!empty($this->pesquisa["pesq_data_inicial"]))$where .= "AND D.DATA >= :datainicial \n";
        if(!empty($this->pesquisa["pesq_data_final"]))	$where .= "AND D.DATA <= :datafinal \n";

        /* pesquisa exclusiva por conteúdo de aula
         * (proibido usar filtros por aluno aqui!)
         */
        if(!empty($this->pesquisa["pesq_aula"])) {
            // ordenação cronológica ou por última atividade
            $order_by = "D.DATA ASC, H.HORARIOINICIO ASC";

            if(!empty($this->pesquisa["pesq_dashboard"])) $order_by = "D.HANDLE DESC";

            // consulta
            $sql = "SELECT {$this->top}
					D.DATA, D.CONTEUDO, D.PROFESSOR,
					H.DIASEMANA, H.HORARIOINICIO, H.HORARIOTERMINO, H.TIMESLOT,
					M.HANDLE AS DISCIPLINA, M.NOME AS NOMEDISCIPLINA,
					T.HANDLE AS TURMA, T.NOME AS NOMETURMA, T.PERIODO, T.SEGMENTO,
					F.NOME AS NOMEPROFESSOR
					
					FROM K_AULA D
					LEFT JOIN K_TURMAHORARIO H ON D.HORARIO = H.HANDLE
					LEFT JOIN K_DISCIPLINA M ON H.DISCIPLINA = M.HANDLE
					LEFT JOIN K_TURMA T ON H.TURMA = T.HANDLE
					LEFT JOIN K_FN_PESSOA F ON D.PROFESSOR = F.HANDLE
					{$where}
					ORDER BY {$order_by}";
        }
        // busca padrão (cruza dados de aluno e presença/falta)
        else {
            $sql = "SELECT {$this->top} D.*,
					H.DIASEMANA, H.HORARIOINICIO, H.HORARIOTERMINO, H.TIMESLOT,
					P.NOME AS NOMEALUNO,
					M.HANDLE AS DISCIPLINA, M.NOME AS NOMEDISCIPLINA,
					T.HANDLE AS TURMA, T.NOME AS NOMETURMA, T.PERIODO, T.SEGMENTO,
					F.HANDLE AS PROFESSOR, F.NOME AS NOMEPROFESSOR,
					A.CONTEUDO, A.PROFESSOR AS PROFESSORSUBSTITUTO
					
					FROM K_DIARIOCLASSE D
					LEFT JOIN K_TURMAHORARIO H ON D.HORARIO = H.HANDLE
						LEFT JOIN K_TURMA T ON H.TURMA = T.HANDLE
						LEFT JOIN K_DISCIPLINA M ON H.DISCIPLINA = M.HANDLE
						LEFT JOIN K_FN_PESSOA F ON H.PROFESSOR = F.HANDLE
					LEFT JOIN K_FN_PESSOA P ON D.ALUNO = P.HANDLE
					LEFT JOIN K_AULA A ON (D.HORARIO = A.HORARIO AND D.DATA = A.DATA)
					{$where}";
        }

        $stmt = $conexao->prepare($sql);

        // mapeando filtros
        if(!empty($this->pesquisa["pesq_codigo"])) 		$stmt->bindValue(":handle", $this->pesquisa["pesq_codigo"]);
        if(!empty($this->pesquisa["pesq_horario"])) 	$stmt->bindValue(":horario", $this->pesquisa["pesq_horario"]);
        if(!empty($this->pesquisa["pesq_disciplina"]))	$stmt->bindValue(":disciplina", $this->pesquisa["pesq_disciplina"]);
        if(!empty($this->pesquisa["pesq_aluno"])) 		$stmt->bindValue(":aluno", $this->pesquisa["pesq_aluno"]);
        if(!empty($this->pesquisa["pesq_turma"]))		$stmt->bindValue(":turma", $this->pesquisa["pesq_turma"]);
        if(!empty($this->pesquisa["pesq_periodo"]))		$stmt->bindValue(":periodo", $this->pesquisa["pesq_periodo"]);
        if(!empty($this->pesquisa["pesq_data"]))		$stmt->bindValue(":data", converteData($this->pesquisa["pesq_data"]));
        if(!empty($this->pesquisa["pesq_data_inicial"]))$stmt->bindValue(":datainicial", converteData($this->pesquisa["pesq_data_inicial"]));
        if(!empty($this->pesquisa["pesq_data_final"]))	$stmt->bindValue(":datafinal", converteData($this->pesquisa["pesq_data_final"]));

        $stmt->execute();
        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        // itera os itens
        $i = 0;

        if(!empty($f)) {
            foreach($f as $r) {
                $item = new EducacionalDiarioClasseETT();
                $item->cont = $i;

                $item->handle = $r->HANDLE;
                $item->aluno = formataCase($r->NOMEALUNO, true);
                $item->cod_aluno = $r->ALUNO;
                $item->data = $r->DATA;
                $item->presenca = $r->PRESENCA;
                $item->historico = $r->HISTORICO;
                $item->conteudo = $r->CONTEUDO;
                $item->professor = formataCase($r->NOMEPROFESSOR, true);
                $item->cod_professor = $r->PROFESSORSUBSTITUTO;
                $item->periodo = $r->PERIODO;
                $item->segmento = $r->SEGMENTO;

                // dados do horário
                if($this->usa_horario) {
                    $item->obj_horario = new EducacionalGradeHorariaETT();
                    $item->obj_horario->handle = $r->HORARIO;
                    $item->obj_horario->turma = formataCase($r->NOMETURMA, true);
                    $item->obj_horario->professor = formataCase($r->NOMEPROFESSOR, true);
                    $item->obj_horario->disciplina = formataCase($r->NOMEDISCIPLINA, true);
                    $item->obj_horario->dia_semana = $item->obj_horario->getNomeDiaSemana($r->DIASEMANA);
                    $item->obj_horario->cod_turma = $r->TURMA;
                    $item->obj_horario->cod_professor = $r->PROFESSOR;
                    $item->obj_horario->cod_disciplina = $r->DISCIPLINA;
                    $item->obj_horario->cod_dia_semana = $r->DIASEMANA;
                    $item->obj_horario->horario_inicio = $r->HORARIOINICIO;
                    $item->obj_horario->horario_termino = $r->HORARIOTERMINO;
                    $item->obj_horario->time_slot = $r->TIMESLOT;
                }

                // atualiza contadores de presença e falta
                if($item->presenca == "S") {
                    $this->presencas++;
                }
                elseif($item->presenca == "N") {
                    $this->faltas++;
                    $this->faltas_por_periodo[$item->periodo]++;
                }

                $this->aulas++;
                $this->aulas_por_periodo[$item->periodo]++;

                // insere no array e incrementa o contador
                array_push($this->itens, $item);
                $i++;
            }
        }
    }
}