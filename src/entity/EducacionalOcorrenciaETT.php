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
    public $envolvidos; // descri��o textual de pessoas vinculadas

    // alunos envolvidos - array $handle => $observacoes
    public $pessoas;

    private function atualizaPessoas()
    {
        // limpa v�nculos anteriores
        $this->deleteStatement("K_OCORRENCIAPESSOA", array("OCORRENCIA" => $this->handle));

        // recadastra
        if (empty($this->pessoas)) {
            mensagem("Ocorr�ncia sem pessoa ou aluno vinculado", MSG_AVISO);
        } else {
            foreach ($this->pessoas as $handle => $observacoes) {
                $stmt = $this->insertStatement("K_OCORRENCIAPESSOA", array(
                    "HANDLE" => newHandle("K_OCORRENCIAPESSOA"),
                    "OCORRENCIA" => $this->handle,
                    "PESSOA" => $handle,
                    "OBSERVACOES" => left($observacoes, 200)
                ));

                retornoPadrao($stmt, "Ocorr�ncia vinculada � pessoa #{$handle}", "N�o foi poss�vel cadastrar o v�nculo da ocorr�ncia");
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

        retornoPadrao($stmt, "Ocorr�ncia inserida com sucesso", "N�o foi poss�vel cadastrar a ocorr�ncia");

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

        retornoPadrao($stmt, "Ocorr�ncia atualizada com sucesso", "N�o foi poss�vel atualizar a ocorr�ncia");

        $this->atualizaPessoas();
    }
}