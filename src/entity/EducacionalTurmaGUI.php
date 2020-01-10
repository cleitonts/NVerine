<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 15/04/2019
 * Time: 12:43
 */

namespace src\entity;

use src\services\Transact\ExtPDO as PDO;

class EducacionalTurmaGUI extends ObjectGUI implements InterfaceGUI
{
    // estatísticas/totalizadores
    public $alunos_por_segmento;
    public $alunos_por_unidade;
    public $alunos_por_turno;

    // parâmetro de ordenação
    public $order_by;

    // construtor obrigatório
    public function __construct($handle = null) {
        $this->header = array("Código", "Nome", "Período", "Ano", "Turno", "Serie", "Segmento", "Vigente", "Escola", "Alunos", "Capacidade");
        $this->handle = $handle;
        $this->alunos_por_segmento = array();
        $this->alunos_por_unidade = array();
        $this->alunos_por_turno = array();

        $this->order_by = "ORDER BY T.ANO DESC, T.PERIODO DESC, T.HANDLE DESC";
    }

    // métodos públicos
    public function getCampo($linha, $coluna) {
        $item = $this->itens[$linha];

        return $this->campos($coluna, array(
            campo($item->handle, "numerico"),
            campo($item->nome),
            campo($item->periodo_nome),
            campo($item->ano, "numerico"),
            campo($item->cod_turno),
            campo($item->nome_serie),
            campo($item->segmento),
            campo(formataLogico($item->atual)),
            campo($item->filial),
            campo($item->total_alunos, "numerico"),
            campo($item->max_alunos, "numerico")
        ));
    }

    public function fetch() {
        global $conexao;

        $this->itens = array();

        $where = "WHERE 1 = 1 \n";

        // pesquisa por escola
        if(!empty($this->pesquisa["pesq_filial"])){
            $where .= "AND T.FILIAL = :filial \n";
        }else{
            $where .= "AND ".filtraFilial("T.FILIAL", "Educacional \n");
        }

        if (!empty($this->handle)) {
            $where .= " AND T.HANDLE = :handle \n";
        }

        // pesquisa por nome
        if(!empty($this->pesquisa["pesq_historico"])) $where .= "AND T.NOME LIKE '%Histórico%' \n";

        // pesquisa por turmas ativas
        if(!empty($this->pesquisa["pesq_vigente"]))	$where .= "AND T.ATUAL = :vigente \n";

        // pesquisa por datas
        if(!empty($this->pesquisa["pesq_data_inicial"])) $where .= "AND T.ANO >= :inicio \n";
        if(!empty($this->pesquisa["pesq_data_final"])) $where .= "AND T.ANO <= :fim \n";
        if(!empty($this->pesquisa["pesq_ano"])) $where .= "AND T.ANO = :ano \n";
        if(!empty($this->pesquisa["pesq_periodo"])) $where .= "AND T.PERIODO = :periodo \n";
        if(!empty($this->pesquisa["pesq_professor"])) $where .= "AND H.PROFESSOR = :professor \n";

        $sql = "SELECT DISTINCT {$this->top} T.*,
				F.NOME AS NOMEFILIAL,
				S.NOME AS NOMESERIE,
				A.DATA, A.DATAFINAL, A.TIPO AS TIPOEVENTO,
				(SELECT COUNT(*) FROM K_TURMAALUNO R WHERE R.TURMA = T.HANDLE AND R.ATIVO = 'S') TOTAL,
				(SELECT MIN(HORARIOINICIO) FROM K_TURMAHORARIO H1 WHERE H1.TURMA = T.HANDLE) HORAINICIO,
				(SELECT MAX(HORARIOTERMINO) FROM K_TURMAHORARIO H2 WHERE H2.TURMA = T.HANDLE) HORATERMINO
				FROM K_TURMA T
				LEFT JOIN K_FN_FILIAL F ON T.FILIAL = F.HANDLE
				LEFT JOIN K_SERIE S ON S.HANDLE = T.SERIE
				LEFT JOIN K_AGENDA A ON T.PERIODO = A.HANDLE AND (TIPO = ".AgendaETT::PERIODO_ANO." OR TIPO = ".AgendaETT::PERIODO_SEMESTRE.")
				LEFT JOIN K_TURMAHORARIO H ON H.TURMA = T.HANDLE
				{$where} 
				{$this->order_by}";
        $stmt = $conexao->prepare($sql);

        // se houve filtros de pesquisa definidos, precisamos mapear aqui
        if (!empty($this->handle)) {$stmt->bindValue(':handle', $this->handle);}
        if(!empty($this->pesquisa["pesq_filial"])) $stmt->bindValue(":filial", $this->pesquisa["pesq_filial"]);
        if(!empty($this->pesquisa["pesq_vigente"])) $stmt->bindValue(":vigente", $this->pesquisa["pesq_vigente"]);
        if(!empty($this->pesquisa["pesq_data_inicial"])) $stmt->bindValue(":inicio", $this->pesquisa["pesq_data_inicial"]);
        if(!empty($this->pesquisa["pesq_data_final"])) $stmt->bindValue(":fim", $this->pesquisa["pesq_data_final"]);
        if(!empty($this->pesquisa["pesq_ano"])) $stmt->bindValue(":ano", $this->pesquisa["pesq_ano"]);
        if(!empty($this->pesquisa["pesq_periodo"])) $stmt->bindValue(":periodo", $this->pesquisa["pesq_periodo"]);
        if(!empty($this->pesquisa["pesq_professor"])) $stmt->bindValue(":professor", $this->pesquisa["pesq_professor"]);
        $stmt->execute();

        // recuperamos todos os itens do statement executado
        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        // itera os itens
        $i = 0;
        if(!empty($f)) {
            foreach($f as $r) {
                $item = new EducacionalTurmaETT();
                $item->cont = $i;
                $item->handle = $r->HANDLE;
                $item->nome = formataCase($r->NOME, true);
                $item->turno = $item->getTurno($r->TURNO);
                $item->cod_turno = $r->TURNO;

                $item->divisao_periodo = $r->DIVISAO;
                $item->periodo = $r->PERIODO;

                $dt_inicio = new \DateTime($r->DATA);
                $dt_final = new \DateTime($r->DATAFINAL);
                $string_data = $dt_inicio->format('m/Y')." - ".$dt_final->format('m/Y');
                $item->periodo_nome = AgendaETT::getNomeEvento($r->TIPOEVENTO)." ".$string_data;

                $item->ano = $dt_inicio->format('Y');
                $item->cod_segmento = $r->SEGMENTO;
                $item->serie = $r->SERIE;
                $item->nome_serie = $r->NOMESERIE;
                $item->carga_horaria = $r->CARGAHORARIA;
                $item->min_alunos = $r->MINALUNOS;
                $item->max_alunos = $r->MAXALUNOS;
                $item->max_periodos = $r->MAXPERIODOS;
                $item->filial = formataCase($r->NOMEFILIAL, true);
                $item->cod_filial = $r->FILIAL;
                $item->total_alunos = intval($r->TOTAL);
                $item->historico = stripos($item->nome, "Histórico") !== false ? true : false;
                $item->eja = $r->EJA;
                $item->horario_inicio = $r->HORAINICIO;
                $item->horario_termino = $r->HORATERMINO;

                // totalizadores
                $this->alunos_por_segmento[$item->segmento] += $item->total_alunos;
                $this->alunos_por_unidade[$item->filial] += $item->total_alunos;
                $this->alunos_por_turno[$item->turno] += $item->total_alunos;

                // insere no array e incrementa o contador
                if(!empty($this->handle)){
                    // carrega matriculas
                    $template = new EducacionalTurmaListaGUI();
                    $template->pesquisa['pesq_lista'] = $this->handle;
                    $template->fetch();
                    $item->turma_aluno = $template->itens;

                    // carrega grade horaria
                    $grade = new EducacionalGradeHorariaGUI();
                    $grade->pesquisa["pesq_turma"] = $this->handle;
                    $grade->fetch();
                    $item->grade_horaria = $grade->itens;

                    // carrega aulas

                }

                array_push($this->itens, $item);
                $i++;
            }
        }
    }
}