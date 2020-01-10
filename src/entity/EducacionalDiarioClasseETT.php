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
    public $historico;		// observa��es sobre aluno

    // propriedades do hor�rio
    public $obj_horario;	// classe GradeHoraria

    // propriedades da aula
    public $conteudo;
    public $cod_professor;

    // apenas gui
    public $cod_aluno;
    public $periodo;		// para organiza��o do boletim
    public $segmento;		// propriedade da turma
    public $professor;

    // m�todos p�blicos
    public function cadastra() {
        $this->handle = newHandle("K_DIARIOCLASSE");

        /* n�o trabalhamos com update no di�rio de classe;
         * seria muito dif�cil controlar assim.
         * toda a��o � um DELETE + INSERT
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

        $texto = $this->presenca == "S" ? "Presen�a" : "Falta";
        retornoPadrao($stmt, "{$texto} lan�ada para o aluno #{$this->cod_aluno}, hor�rio #{$this->horario}.", "N�o foi poss�vel lan�ar a presen�a para aluno #{$this->aluno}");
    }
}