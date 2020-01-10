<?php
namespace src\entity;

use src\services\Transact\ExtPDO as PDO;

class EducacionalBoletimETT extends ObjectETT {
    // propriedades
    public $avaliacao;
    public $aluno;
    public $nota;
    public $nota_revisao;
    public $historico;

    // propriedades da avaliação
    public $obj_avaliacao;

    // apenas gui
    public $nota_pesada;
    public $cod_avaliacao;
    public $cod_aluno;

    // métodos públicos
    public function cadastra() {
        $this->handle = newHandle("K_BOLETIM");

        $stmt = $this->insertStatement("K_BOLETIM",
            array(
                "HANDLE"		=> $this->handle,
                "AVALIACAO"		=> validaVazio($this->cod_avaliacao),
                "ALUNO"			=> validaVazio($this->cod_aluno),
                "NOTA"			=> floatval($this->nota),
                "NOTAREVISAO"	=> floatval($this->nota),
                "HISTORICO"		=> $this->historico
            ));

        retornoPadrao($stmt, "Nota lançada para aluno #{$this->aluno}.", "Não foi possível lançar a nota para aluno #{$this->aluno}");
    }

    public function atualiza() {
        $stmt = $this->updateStatement("K_BOLETIM",
            array(
                "HANDLE"		=> $this->handle,
                "AVALIACAO"		=> validaVazio($this->cod_avaliacao),
                "ALUNO"			=> validaVazio($this->cod_aluno),
                // "NOTA"		=> floatval($this->nota),
                "NOTAREVISAO"	=> floatval($this->nota_revisao),
                "HISTORICO"		=> $this->historico
            ));

        retornoPadrao($stmt, "Nota atualizada para aluno #{$this->aluno}.", "Não foi possível atualizar a nota para aluno #{$this->aluno}");
    }
}