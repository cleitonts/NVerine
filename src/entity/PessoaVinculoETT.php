<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 02/05/2019
 * Time: 13:29
 */

namespace src\entity;


class PessoaVinculoETT extends ObjectETT
{
    // propriedades
    public $pai;
    public $filho;
    public $tipo;
    public $responsavel; // (bool) respons�vel financeiro

    // para a gui
    public $relacionado;
    public $cod_pai;
    public $cod_filho;
    public $cod_tipo;
    public $complemento;

    // dados extras para relat�rio
    public $profissao;

    // m�todos privados
    public function vazio()
    {
        if (empty($this->tipo)) return true;

        return false;
    }

    // m�todos p�blicos
    public function cadastra()
    {
        // vazio?
        if ($this->vazio() || empty($this->pai) || empty($this->filho)) {
            mensagem("Sem novos v�nculos para cadastrar.");
            return;
        }

        // sim, usu�rios burros fazem isso
        if ($this->pai == $this->filho) {
            mensagem("N�o se pode vincular uma pessoa a ela mesma.", MSG_ERRO);
            finaliza();
        }

        // gera handle
        $this->handle = newHandle("K_FN_PESSOAVINCULO");

        // insere
        $stmt = $this->insertStatement("K_FN_PESSOAVINCULO",
            array(
                "HANDLE" => $this->handle,
                "PAI" => validaVazio($this->pai),
                "FILHO" => validaVazio($this->filho),
                "TIPOVINCULO" => validaVazio($this->tipo),
                "RESPONSAVEL" => left($this->responsavel, 1)
            ));
        retornoPadrao($stmt, "V�nculo cadastrado com sucesso.", "N�o foi poss�vel cadastrar v�nculo");
    }

    public function atualiza()
    {
        // atualiza
        if (!$this->vazio()) {
            $stmt = $this->updateStatement("K_FN_PESSOAVINCULO",
                array(
                    "HANDLE" => $this->handle,
                    "PAI" => validaVazio($this->pai),
                    "FILHO" => validaVazio($this->filho),
                    "TIPOVINCULO" => validaVazio($this->tipo),
                    "RESPONSAVEL" => left($this->responsavel, 1)
                ));
            retornoPadrao($stmt, "V�nculo atualizado com sucesso", "N�o foi poss�vel atualizar o v�nculo");
        } // exclui
        else {
            $stmt = $this->deleteStatement("K_FN_PESSOAVINCULO", array("HANDLE" => $this->handle));

            retornoPadrao($stmt, "V�nculo foi removido.", "N�o foi poss�vel remover o v�nculo");
        }
    }

    public function getTipo($tipo)
    {
        return "N�o implementado";
    }
}