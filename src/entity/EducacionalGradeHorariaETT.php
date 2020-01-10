<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 06/05/2019
 * Time: 16:28
 */

namespace src\entity;


class EducacionalGradeHorariaETT extends ObjectETT
{
    // propriedades
    public $turma;
    public $disciplina;
    public $professor;
    public $dia_semana;            // 1 = segunda : 7 = domingo
    public $horario_inicio;        // em notação HH:MM
    public $horario_termino;    // ||
    public $time_slot;            // usar apenas quando houver montagem de grade horária automática

    // apenas gui
    public $cod_turma;
    public $cod_professor;
    public $cod_disciplina;
    public $cod_dia_semana;
    public $hora_inicio;        // horário em float (ex.: 16:30 = 16.5)
    public $hora_termino;        // ||
    public $ano;                // propriedade da turma
    public $periodo;            // ||

    public function validaForm(){
        global $transact;

        // campos obrigatorios
        validaCampo($this->cod_turma, "Turma");
        validaCampo($this->cod_professor, "Professor");
        validaCampo($this->cod_dia_semana, "Dia da semana");
        validaCampo($this->horario_inicio, "Hora inicio");
        validaCampo($this->horario_termino, "Hora termino");
    }

    // métodos públicos
    public function cadastra()
    {
        $this->validaForm();

        $this->handle = newHandle("K_TURMAHORARIO");

        $stmt = $this->insertStatement("K_TURMAHORARIO",
            array(
                "HANDLE" => $this->handle,
                "TURMA" => $this->cod_turma,
                "DISCIPLINA" => validaVazio($this->cod_disciplina),
                "PROFESSOR" => $this->cod_professor,
                "DIASEMANA" => $this->cod_dia_semana,
                "HORARIOINICIO" => left($this->horario_inicio, 10),
                "HORARIOTERMINO" => left($this->horario_termino, 10),
                "TIMESLOT" => intval($this->time_slot)
            ));

        retornoPadrao($stmt, "Horário {$this->horario_inicio} cadastrado com sucesso.", "Não foi possível cadastrar o horário {$this->horario_inicio}");
    }

    public function atualiza()
    {
        $this->validaForm();

        $stmt = $this->updateStatement("K_TURMAHORARIO",
            array(
                "HANDLE" => $this->handle,
                "TURMA" => $this->cod_turma,
                "DISCIPLINA" => validaVazio($this->cod_disciplina),
                "PROFESSOR" => $this->cod_professor,
                "DIASEMANA" => $this->cod_dia_semana,
                "HORARIOINICIO" => left($this->horario_inicio, 10),
                "HORARIOTERMINO" => left($this->horario_termino, 10),
                "TIMESLOT" => intval($this->time_slot)
            ));

        retornoPadrao($stmt, "Horário {$this->horario_inicio} atualizado com sucesso.", "Não foi possível atualizar o horário {$this->horario_inicio}");
    }

    public function remove()
    {
        $this->deleteStatement("K_TURMAHORARIO", array("TURMA" => $this->cod_turma));

        mensagem("Horário {$this->horario_inicio} foi removido do banco de dados!");
    }

    public static function getNomeDiaSemana($dia, $lista = false)
    {
        $dias = array("indefinido", "Segunda", "Terça", "Quarta", "Quinta", "Sexta", "Sábado", "Domingo");

        if($lista){
            return $dias;
        }

        return $dias[$dia];
    }
}