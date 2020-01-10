<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 25/06/2019
 * Time: 16:02
 */

namespace src\entity;


class EducacionalDiarioClasseETT extends ObjectETT
{
    // propriedades
    public $aluno;
    public $horario;		// apenas cadastro?
    public $data;
    public $presenca;		// S | N
    public $historico;		// observações sobre aluno

    // propriedades do horário
    public $obj_horario;	// classe GradeHoraria

    // propriedades da aula
    public $conteudo;
    public $cod_professor;

    // apenas gui
    public $cod_aluno;
    public $periodo;		// para organização do boletim
    public $segmento;		// propriedade da turma
    public $professor;

    // métodos públicos
    public function cadastra() {
        $this->handle = newHandle("K_DIARIOCLASSE");

        /* não trabalhamos com update no diário de classe;
         * seria muito difícil controlar assim.
         * toda ação é um DELETE + INSERT
         */
        $stmt = $this->deleteStatement("K_DIARIOCLASSE",
            array(
                "HORARIO"		=> validaVazio($this->horario),
                "DATA"			=> $this->data,
                "ALUNO"			=> validaVazio($this->cod_aluno),
            ));

        $stmt = $this->insertStatement("K_DIARIOCLASSE",
            array(
                "HANDLE"		=> $this->handle,
                "HORARIO"		=> validaVazio($this->horario),
                "DATA"			=> $this->data,
                "ALUNO"			=> validaVazio($this->cod_aluno),
                "PRESENCA"		=> left($this->presenca, 1),
                "HISTORICO"		=> left($this->historico, 250)
            ));

        $texto = $this->presenca == "S" ? "Presença" : "Falta";
        retornoPadrao($stmt, "{$texto} lançada para o aluno #{$this->cod_aluno}, horário #{$this->horario}.", "Não foi possível lançar a presença para aluno #{$this->aluno}");
    }
}