<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 08/10/2019
 * Time: 10:22
 */

namespace src\entity;


class EducacionalOcorrenciaETT extends ObjectETT
{
    public $data;
    public $tipo;
    public $usuario;
    public $notas;

    // gui apenas
    public $cod_tipo;
    public $cod_usuario;
    public $severidade;
    public $envolvidos; // descrição textual de pessoas vinculadas

    // alunos envolvidos - array $handle => $observacoes
    public $pessoas;

    private function atualizaPessoas()
    {
        // limpa vínculos anteriores
        $this->deleteStatement("K_OCORRENCIAPESSOA", array("OCORRENCIA" => $this->handle));

        // recadastra
        if (empty($this->pessoas)) {
            mensagem("Ocorrência sem pessoa ou aluno vinculado", MSG_AVISO);
        } else {
            foreach ($this->pessoas as $handle => $observacoes) {
                $stmt = $this->insertStatement("K_OCORRENCIAPESSOA", array(
                    "HANDLE" => newHandle("K_OCORRENCIAPESSOA"),
                    "OCORRENCIA" => $this->handle,
                    "PESSOA" => $handle,
                    "OBSERVACOES" => left($observacoes, 200)
                ));

                retornoPadrao($stmt, "Ocorrência vinculada à pessoa #{$handle}", "Não foi possível cadastrar o vínculo da ocorrência");
            }
        }
    }

    public function cadastra()
    {
        $this->handle = newHandle("K_OCORRENCIA");

        // defaults
        if (empty($this->usuario)) $this->usuario = $_SESSION["ID"];

        $stmt = $this->insertStatement("K_OCORRENCIA", array(
            "HANDLE" => $this->handle,
            "DATA" => $this->data,
            "TIPO" => validaVazio($this->tipo),
            "USUARIO" => validaVazio($this->usuario),
            "NOTAS" => $this->notas
        ));

        retornoPadrao($stmt, "Ocorrência inserida com sucesso", "Não foi possível cadastrar a ocorrência");

        $this->atualizaPessoas();
    }

    public function atualiza()
    {
        $stmt = $this->updateStatement("K_OCORRENCIA", array(
            "HANDLE" => $this->handle,
            "DATA" => $this->data,
            "TIPO" => validaVazio($this->tipo),
            "USUARIO" => validaVazio($this->usuario),
            "NOTAS" => $this->notas
        ));

        retornoPadrao($stmt, "Ocorrência atualizada com sucesso", "Não foi possível atualizar a ocorrência");

        $this->atualizaPessoas();
    }
}