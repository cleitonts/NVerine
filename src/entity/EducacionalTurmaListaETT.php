<?php
/**
 * Created by PhpStorm.
 * User: rafael
 * Date: 18/04/2019
 * Time: 11:45
 */

namespace src\entity;


class EducacionalTurmaListaETT extends ObjectETT
{
    public $aluno;
    public $cod_aluno;
    public $ativo;
    public $data_entrada;
    public $data_saida;
    public $conceito;
    public $situacao;
    public $turno;
    public $turma;
    public $numero;

    public function validaForm(){
        global $transact;

        // campos obrigatorios
        validaCampo($this->cod_aluno, "Aluno");
        validaCampo($this->data_entrada, "Data entrada");
        //validaCampo($this->turno, "Turno");
    }

    public function cadastra() {
        global $conexao;

        $this->validaForm();

        if(empty($this->filial)) $this->filial = __FILIAL__;
        $this->handle = newHandle("K_TURMAALUNO", $conexao);

        $stmt = $this->insertStatement("K_TURMAALUNO",
            array(
                "HANDLE"		=> $this->handle,
                "ALUNO"		    => $this->cod_aluno,
                "TURMA"         => $this->turma,
                "ATIVO"			=> $this->ativo,
                "NUMERO"        => $this->numero,
                "DATAENTRADA"   => $this->data_entrada,
                "DATASAIDA"		=> validaVazio($this->data_saida),
                //"SITUACAO"		=> $this->situacao,
                "TURNO"		    => $this->turno,

            ));

        retornoPadrao($stmt, "Aluno #'{$this->cod_aluno}' cadastrado(a) com sucesso", "Não foi possível cadastrar aluno #'{$this->cod_aluno}'");
    }

    public function atualiza() {
        $this->validaForm();

        $stmt = $this->updateStatement("K_TURMAALUNO",
            array(
                "HANDLE"		=> $this->handle,
                "ALUNO"		    => $this->cod_aluno,
                "TURMA"         => $this->turma,
                "ATIVO"			=> $this->ativo,
                "NUMERO"        => $this->numero,
                "DATAENTRADA"   => $this->data_entrada,
                "DATASAIDA"		=> validaVazio($this->data_saida),
                //"SITUACAO"		=> $this->situacao,
                "TURNO"		    => $this->turno,

            ));

        retornoPadrao($stmt, "Aluno #'{$this->cod_aluno}' atualizado com sucesso", "Não foi possível atualizar aluno #'{$this->cod_aluno}'");
    }
}


