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
    public $responsavel; // (bool) responsável financeiro

    // para a gui
    public $relacionado;
    public $cod_pai;
    public $cod_filho;
    public $cod_tipo;
    public $complemento;

    // dados extras para relatório
    public $profissao;

    // métodos privados
    public function vazio()
    {
        if (empty($this->tipo)) return true;

        return false;
    }

    // métodos públicos
    public function cadastra()
    {
        // vazio?
        if ($this->vazio() || empty($this->pai) || empty($this->filho)) {
            mensagem("Sem novos vínculos para cadastrar.");
            return;
        }

        // sim, usuários burros fazem isso
        if ($this->pai == $this->filho) {
            mensagem("Não se pode vincular uma pessoa a ela mesma.", MSG_ERRO);
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
        retornoPadrao($stmt, "Vínculo cadastrado com sucesso.", "Não foi possível cadastrar vínculo");
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
            retornoPadrao($stmt, "Vínculo atualizado com sucesso", "Não foi possível atualizar o vínculo");
        } // exclui
        else {
            $stmt = $this->deleteStatement("K_FN_PESSOAVINCULO", array("HANDLE" => $this->handle));

            retornoPadrao($stmt, "Vínculo foi removido.", "Não foi possível remover o vínculo");
        }
    }

    public function getTipo($tipo)
    {
        return "Não implementado";
    }
}