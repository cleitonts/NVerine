<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 09/10/2019
 * Time: 08:58
 */

namespace src\entity;


use src\services\Transact\ExtPDO;

class EducacionalAvaliacaoETT extends ObjectETT
{
// propriedades
    public $turma;
    public $disciplina;
    public $nome;
    public $descricao;
    public $peso;
    public $data;
    public $conteudo;

    // apenas gui
    public $cod_turma;
    public $cod_disciplina;
    public $ano;                // propriedade da turma
    public $periodo;            // ||
    public $cod_segmento;        // ||

    // apenas para herança
    public $serie;

    // métodos públicos
    public function cadastra()
    {
        $this->handle = newHandle("K_TURMAAVALIACAO");

        $stmt = $this->insertStatement("K_TURMAAVALIACAO",
            array(
                "HANDLE" => $this->handle,
                "TURMA" => validaVazio($this->cod_turma),
                "DISCIPLINA" => validaVazio($this->cod_disciplina),
                "NOME" => left($this->nome, 250),
                "DESCRICAO" => $this->descricao,
                "PESO" => floatval($this->peso),
                "DATA" => $this->data,
                "CONTEUDO" => $this->conteudo
            ));

        // lança evento na agenda
        $evento = $this->criaEvento();
        $evento->cadastra();

        retornoPadrao($stmt, "Avaliação '{$this->nome}' cadastrada com sucesso.", "Não foi possível cadastrar a avaliação '{$this->nome}'");
    }

    public function atualiza()
    {
        $stmt = $this->updateStatement("K_TURMAAVALIACAO",
            array(
                "HANDLE" => $this->handle,
                "TURMA" => validaVazio($this->cod_turma),
                "DISCIPLINA" => validaVazio($this->cod_disciplina),
                "NOME" => left($this->nome, 250),
                "DESCRICAO" => $this->descricao,
                "PESO" => floatval($this->peso),
                "DATA" => $this->data,
                "CONTEUDO" => $this->conteudo
            ));

        retornoPadrao($stmt, "Avaliação '{$this->nome}' atualizada com sucesso.", "Não foi possível atualizar a avaliação '{$this->nome}'");
    }

    public function criaEvento(){
        global $conexao;

        // seleciona as disciplinas
        // tras os indices da lista
        $sql = "SELECT * FROM K_DISCIPLINA WHERE HANDLE = {$this->cod_disciplina}";
        $stmt = $conexao->prepare($sql);

        $stmt->execute();
        $f = $stmt->fetchAll(ExtPDO::FETCH_OBJ);
        $disciplina = $f[0]->NOME;

        $evento = new AgendaETT();
        $evento->cod_escola = __FILIAL__;
        $evento->data_inicial = $this->data;
        $evento->data_final = $this->data;
        $evento->evento_ativo = "S";
        $evento->cod_regiao = $_SESSION["REGIAO"];
        $evento->conteudo = "Avaliação de {$disciplina}. Agendada à partir do módulo de Avaliações";
        $evento->cod_turma = $this->cod_turma;
        $evento->tipo_evento = $evento::AVALIACAO;
        $evento->titulo = "Avaliação de ".$disciplina;

        return $evento;
    }
    public function remove()
    {
        $stmt = $this->deleteStatement("K_TURMAAVALIACAO", array("HANDLE" => $this->handle, "TURMA" => $this->cod_turma));

        retornoPadrao($stmt, "Avaliação '{$this->nome}' foi removida do banco de dados!", "Não é possível remover a avaliação '{$this->nome}'");
    }
}