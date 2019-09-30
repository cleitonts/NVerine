<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 31/07/2019
 * Time: 17:38
 */

namespace src\entity;


class SuporteHistoricoETT extends ObjectETT
{
// "propriedade" do histórico: chamado a que pertence, usuário que abriu
    public $chamado;
    public $usuario;

    // dados do histórico
    public $comentarios;
    public $anexo;

    // dados para controle
    public $data;
    public $hora;
    public $status_chamado; // guarda histórico das mudanças de status
    public $revisao;        // número de revisão referente a uma mudança

    // dados para validação
    public $tipo_chamado;

    // referências para gui
    public $cod_usuario;
    public $cod_status_chamado;
    public $assunto_chamado;
    public $avatar;
    public $sumario;        // comentário reduzido e sem tags

    // --------------------------------------------------------------------------------------
    // métodos públicos
    public function cadastra()
    {
        global $conexao;

        // valida status
        if ($this->status_chamado == SuporteChamadoETT::STATUS_RESOLVIDO && $this->tipo_chamado == SuporteChamadoETT::TIPO_DESENVOLVIMENTO && empty($this->revisao)) {
            mensagem("Para resolver uma demanda de DESENVOLVIMENTO, por gentileza, informe o número de revisão.", MSG_ERRO);
            finaliza();
        }

        // sobe arquivo
//        if (temArquivo($this->anexo)) {
//            $up = new Upload();
//            $up->anexo = $this->anexo;
//            $url = $up->getUrl();
//        } else {
//            $url = "";
//        }

        // trata defaults
        if (empty($this->data)) $this->data = hoje();
        if (empty($this->hora)) $this->hora = date("H:i:s");
        if (empty($this->cod_usuario)) $this->cod_usuario = $_SESSION["ID"];
        $this->handle = newHandle("K_CHAMADOHISTORICO", $conexao);



        $stmt = $this->insertStatement("K_CHAMADOHISTORICO",
            array(
                "HANDLE"		    => $this->handle,
                "CHAMADO"		    => $this->chamado,
                "USUARIO"			=> validaVazio($this->cod_usuario),
                "COMENTARIOS"		=> $this->comentarios,
                //"ANEXO"	            => $url,
                "DATA"		        => $this->data,
                "HORA"              => $this->hora,
                "STATUS"		    => $this->status_chamado,
                "REVISAO"		    => $this->revisao,
            ));

        retornoPadrao($stmt, "Histórico do chamado cadastrado com sucesso", "Não foi possível cadastrar o histórico do chamado");
    }

    public function atualiza()
    {
        // não implementa! por enquanto...
    }
}