<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 27/06/2019
 * Time: 10:59
 */

namespace src\entity;


class EducacionalAulaETT extends ObjectETT
{
    // propriedades
    public $horario;
    public $data;
    public $professor;
    public $substituto;		// S | N
    public $conteudo;

    // m�todos p�blicos
    public function cadastra() {
        $this->handle = newHandle("K_AULA");

        /* n�o trabalhamos com update no di�rio de classe;
         * seria muito dif�cil controlar assim.
         * toda a��o � um DELETE + INSERT
         */
        $stmt = $this->deleteStatement("K_AULA",
            array(
                "HORARIO"		=> validaVazio($this->horario),
                "DATA"			=> $this->data
            ));

        $stmt = $this->insertStatement("K_AULA",
            array(
                "HANDLE"		=> $this->handle,
                "HORARIO"		=> validaVazio($this->horario),
                "DATA"			=> $this->data,
                "PROFESSOR"		=> validaVazio($this->professor),
                "SUBSTITUTO"	=> "N",
                "CONTEUDO"		=> $this->conteudo
            ));

        retornoPadrao($stmt, "Conte�do lan�ado para o dia {$this->data}, hor�rio #{$this->horario}", "N�o foi poss�vel lan�ar o conte�do do hor�rio #{$this->horario}");
    }
}