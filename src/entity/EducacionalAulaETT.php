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

    // métodos públicos
    public function cadastra() {
        $this->handle = newHandle("K_AULA");

        /* não trabalhamos com update no diário de classe;
         * seria muito difícil controlar assim.
         * toda ação é um DELETE + INSERT
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

        retornoPadrao($stmt, "Conteúdo lançado para o dia {$this->data}, horário #{$this->horario}", "Não foi possível lançar o conteúdo do horário #{$this->horario}");
    }
}