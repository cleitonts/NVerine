<?php

namespace src\services\Metadados;

use src\entity\AgendaETT;

class TabelasETT extends \src\entity\ObjectETT
{
    public $nome_tabela;
    public $usuario;
    public $coluna;
    public $posicao;

    public function __construct()
    {
        $this->usuario = $_SESSION["ID"];
    }

    public function cadastra()
    {
        global $conexao;
        $this->handle = newHandle('METADADOS_TABELAS', $conexao);

        dumper($this);

        $stmt = $this->insertStatement("METADADOS_TABELAS",
            array(
                "HANDLE" => $this->handle,
                "NOME_TABELA" => $this->nome_tabela,
                "USUARIO" => $this->usuario,
                "COLUNA" => $this->coluna,
                "POSICAO" => $this->posicao
            ));
        retornoPadrao($stmt, "Coluna atualizada", "Não foi possível atualiza a coluna");
    }

    public function limpa()
    {
        $stmt = $this->deleteStatement("METADADOS_TABELAS",
            array(
                "USUARIO"		=> validaVazio($this->usuario),
                "NOME_TABELA"			=> $this->nome_tabela
            ));

        mensagem("Limpando a tabela");
    }
}