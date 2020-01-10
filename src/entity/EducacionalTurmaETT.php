<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 15/04/2019
 * Time: 12:43
 */

namespace src\entity;

use src\services\Transact\ExtPDO as PDO;

class EducacionalTurmaETT extends ObjectETT
{
    // turnos
    const ED_MATUTINO = 1;
    const ED_VESPERTINO = 2;
    const ED_NOTURNO = 3;
    const ED_INTEGRAL = 4;

    // segmentos
    const ED_INFANTIL = 1;
    const ED_FUNDAMENTAL_I = 2;
    const ED_FUNDAMENTAL_II = 3;
    const ED_MEDIO = 4;
    const ED_SUPERIOR = 5;
    const ED_TECNICO = 6;
    const ED_OUTROS = 7;
    const ED_CRECHE = 8;

    // propriedades
    public $filial;
    public $nome;
    public $turno;				// string de 1 a 2 caracteres
    public $ano;				// inteiro
    public $periodo;			// ||
    public $segmento;			// ||
    public $eja;				// S/N
    public $carga_horaria;		// string livre, mas pode tratar como int (se houver somatórios, etc.)
    public $min_alunos;			// inteiro
    public $max_alunos;			// ||
    public $max_periodos;		// ||
    public $serie;
    public $nome_serie;
    public $divisao_periodo;
    public $periodo_nome;

    // gui apenas
    public $cod_segmento;
    public $cod_filial;
    public $cod_turno;
    public $curso;
    public $total_alunos;
    public $historico;			// propriedade booleana para turmas de histórico escolar
    public $horario_inicio;
    public $horario_termino;
    public $turma_aluno = array();
    public $grade_horaria = array();

    public function validaForm(){
        global $transact;

        // campos obrigatorios
        validaCampo($this->nome, "Nome");
        validaCampo($this->cod_filial, "Escola");
        validaCampo($this->serie, "Serie");
        validaCampo($this->cod_turno, "Turno");
        validaCampo($this->periodo, "Período");
        validaCampo($this->divisao_periodo, "Divisão de período");
    }

    // métodos públicos
    public function cadastra(){
        global $conexao;

        $this->validaForm();

        if(empty($this->filial)) $this->filial = __FILIAL__;
        $this->handle = newHandle("K_TURMA", $conexao);

        $stmt = $this->insertStatement("K_TURMA",
            array(
                "HANDLE"		=> $this->handle,
                "FILIAL"		=> $this->cod_filial,
                "NOME"			=> left($this->nome, 100),
                "TURNO"			=> left($this->cod_turno, 2),
                "SERIE"         => $this->serie,
                "PERIODO"		=> $this->periodo,
                "DIVISAO"       => $this->divisao_periodo,
                "MINALUNOS"		=> $this->min_alunos,
                "MAXALUNOS"		=> $this->max_alunos,
                "EJA"			=> $this->eja
            ));

        retornoPadrao($stmt, "Turma '{$this->nome}' cadastrada com sucesso", "Não foi possível cadastrar nova turma '{$this->nome}'");
    }

    public function atualiza(){
        $stmt = $this->updateStatement("K_TURMA",
            array(
                "HANDLE"		=> $this->handle,
                "FILIAL"		=> $this->cod_filial,
                "NOME"			=> left($this->nome, 100),
                "TURNO"			=> left($this->cod_turno, 2),
                "SERIE"         => $this->serie,
                "PERIODO"		=> $this->periodo,
                "DIVISAO"       => $this->divisao_periodo,
                "MINALUNOS"		=> $this->min_alunos,
                "MAXALUNOS"		=> $this->max_alunos,
                "EJA"			=> $this->eja
            ));
        retornoPadrao($stmt, "Turma '{$this->nome}' atualizada com sucesso", "Não foi possível atualizar turma '{$this->nome}'");
    }

    /**
     * retorna uma lista de periodos que foram preenchidos na agenda
     */
    public static function getPeriodo(){
        global $conexao;

        $sql = "SELECT * 
                FROM K_AGENDA 
                WHERE TIPO = ".AgendaETT::PERIODO_ANO." OR TIPO = ".AgendaETT::PERIODO_SEMESTRE;

        $stmt = $conexao->prepare($sql);

        $stmt->execute();
        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        $arr = array();

        // cria opção vazia
        $arr["handle"][] = "";
        $arr["nome"][] = "";

        foreach ($f as $r) {
            $dt_inicio = new \DateTime($r->DATA);
            $dt_final = new \DateTime($r->DATAFINAL);
            $string_data = $dt_inicio->format('m/Y')." - ".$dt_final->format('m/Y');


            $arr["handle"][] = $r->HANDLE;
            $arr["nome"][] = AgendaETT::getNomeEvento($r->TIPO)." ".$string_data;
        }
        return $arr;
    }

    public static function getTurno($lista = 0, $lista_completa = false, $abreviado = false)
    {
        $lista_idade = array(
            'Matutino',
            'Vespertino',
            'Noturno',
            'Integral'
        );

        if ($abreviado) {
            $lista_idade = array(
                'M',
                'V',
                'N',
                'I'
            );
        }

        if ($lista_completa) {
            return $lista_idade;
        }

        return $lista_idade[$lista];
    }
}
