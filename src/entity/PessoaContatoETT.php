<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 02/05/2019
 * Time: 10:43
 */

namespace src\entity;


class PessoaContatoETT extends ObjectETT
{
// propriedades
    public $pessoa;    // pai
    public $cod_pessoa; // handle do pai
    public $nome;
    public $area;
    public $telefone;
    public $email;
    public $ordem;        // por enquanto é inutilizado, mas serviria para reordenar a lista de contatos

    public function validaForm()
    {
        global $transact;

        // campos obrigatorios
        $transact->validaCampo($this->cod_pessoa, "Pessoa");
        $transact->validaCampo($this->nome, "Nome");
        $transact->validaCampo($this->email, "E-mail");
        $transact->validaCampo($this->telefone, "Telefone");
    }

    // métodos públicos
    public function cadastra()
    {
        global $conexao;

        $this->validaForm();

        // gera handle
        $this->handle = newHandle("K_FN_CONTATO", $conexao);

        // insere
        $stmt = $this->insertStatement("K_FN_CONTATO",
            array(
                "HANDLE" => $this->handle,
                "ORDEM" => $this->ordem,
                "NOME" => $this->nome,
                "AREA" => $this->area,
                "TELEFONE" => $this->telefone,
                "EMAIL" => $this->email,
                "PESSOA" => $this->cod_pessoa
            ));

        retornoPadrao($stmt, "Contato cadastrado com sucesso.", "Não foi possível cadastrar o contato");
    }

    public function atualiza()
    {
        $this->validaForm();

        // atualiza
        $stmt = $this->updateStatement("K_FN_CONTATO",
            array(
                "HANDLE" => $this->handle,
                "ORDEM" => $this->ordem,
                "NOME" => $this->nome,
                "AREA" => $this->area,
                "TELEFONE" => $this->telefone,
                "EMAIL" => $this->email
            ));

        retornoPadrao($stmt, "Contato atualizado com sucesso.", "Não foi possível atualizar o contato");
    }

    public function delete()
    {

        $stmt = $this->deleteStatement("K_FN_CONTATO", array("HANDLE" => $this->handle));

        retornoPadrao($stmt, "Contato foi removido.", "Não foi possível remover o contato");
    }
}