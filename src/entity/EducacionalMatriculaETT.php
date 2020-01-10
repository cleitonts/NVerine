<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 02/05/2019
 * Time: 14:01
 */

namespace src\entity;


class EducacionalMatriculaETT extends ObjectETT
{
// propriedades
    public $aluno;
    public $turma;
    public $ativo;
    public $data_entrada;
    public $data_saida;
    public $historico;
    public $conceito;
    public $turno;                    // propriedade da turma, mas pode ser sobrescrito no aluno

    // apenas gui
    public $cod_turma;
    public $cod_aluno;
    public $numero_matricula_aluno; // registro, n�o matr�cula na turma
    public $ano;                    // propriedade da turma
    public $periodo;                // ||
    public $cod_segmento;            // ||
    public $filial;                    // ||
    public $vigente;                // ||
    public $carga_horaria;            // ||
    public $situacao;                // falecido, transferido.....
    public $data_nascimento;        // dados do aluno
    public $responsavel;            // ||
    public $sexo;                    // ||
    public $vinculos;

    // soft-campos de turmas de hist�rico escolar
    public $escola;
    public $municipio;
    public $estado;
    public $serie;
    public $total_aulas;
    public $total_faltas;

    // m�todos privados
    private function checaDuplicidade()
    {
        global $conexao;

        $sql = "SELECT * FROM K_TURMAALUNO WHERE TURMA = :turma AND ALUNO = :aluno";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":turma", $this->turma);
        $stmt->bindValue(":aluno", $this->aluno);
        $stmt->execute();
        $f = $stmt->fetch(PDO::FETCH_OBJ);

        if (!empty($f->HANDLE)) {
            mensagem("Duplicidade: aluno #{$this->aluno} j� foi cadastrado na turma #{$this->turma}", MSG_ERRO);
            finaliza();
        }
    }

    // m�todos p�blicos
    public function cadastra()
    {
        // valida duplicidade de aluno
        $this->checaDuplicidade();

        // insere registro
        $this->handle = newHandle("K_TURMAALUNO");
        if (empty($this->data_entrada)) $this->data_entrada = hoje();

        $stmt = $this->insertStatement("K_TURMAALUNO",
            array(
                "HANDLE" => $this->handle,
                "ALUNO" => $this->aluno,
                "TURMA" => $this->turma,
                "ATIVO" => left($this->ativo, 1),
                "DATAENTRADA" => converteData($this->data_entrada),
                "DATASAIDA" => converteData($this->data_saida),
                "HISTORICO" => left($this->historico, 250),
                "CONCEITO" => left($this->conceito, 10),
                "TURNO" => left($this->turno, 2)
            ));

        retornoPadrao($stmt, "Matr�cula cadastrada com sucesso", "N�o foi poss�vel cadastrar a matr�cula");
    }

    public function atualiza()
    {
        // preenche data de sa�da como hoje se for atualiza��o inativa
        if (empty($this->data_saida) && $this->ativo == "N") $this->data_saida = hoje();

        $stmt = $this->updateStatement("K_TURMAALUNO",
            array(
                "HANDLE" => $this->handle,
                // "ALUNO"			=> $this->aluno,
                // "TURMA"			=> $this->turma,
                "ATIVO" => left($this->ativo, 1),
                "DATAENTRADA" => converteData($this->data_entrada),
                "DATASAIDA" => converteData($this->data_saida),
                "HISTORICO" => left($this->historico, 250),
                "CONCEITO" => left($this->conceito, 10),
                "TURNO" => left($this->turno, 2)
            ));

        retornoPadrao($stmt, "Matr�cula atualizada com sucesso", "N�o foi poss�vel atualizar a matr�cula");
    }

    public function remove()
    {
        $stmt = $this->deleteStatement("K_TURMAALUNO", array("HANDLE" => $this->handle, "ALUNO" => $this->aluno, "TURMA" => $this->turma));

        retornoPadrao($stmt, "Matr�cula #{$this->handle} foi removida desta turma", "N�o � poss�vel remover a matr�cula #'{$this->handle}'");
    }
}