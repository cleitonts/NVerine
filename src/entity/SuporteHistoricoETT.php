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
// "propriedade" do hist�rico: chamado a que pertence, usu�rio que abriu
    public $chamado;
    public $usuario;

    // dados do hist�rico
    public $comentarios;
    public $anexo;

    // dados para controle
    public $data;
    public $hora;
    public $status_chamado; // guarda hist�rico das mudan�as de status
    public $revisao;        // n�mero de revis�o referente a uma mudan�a

    // dados para valida��o
    public $tipo_chamado;

    // refer�ncias para gui
    public $cod_usuario;
    public $cod_status_chamado;
    public $assunto_chamado;
    public $avatar;
    public $sumario;        // coment�rio reduzido e sem tags

    // --------------------------------------------------------------------------------------
    // m�todos p�blicos
    public function cadastra()
    {
        global $conexao;

        // valida status
        if ($this->status_chamado == SuporteChamadoETT::STATUS_RESOLVIDO && $this->tipo_chamado == SuporteChamadoETT::TIPO_DESENVOLVIMENTO && empty($this->revisao)) {
            mensagem("Para resolver uma demanda de DESENVOLVIMENTO, por gentileza, informe o n�mero de revis�o.", MSG_ERRO);
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

        retornoPadrao($stmt, "Hist�rico do chamado cadastrado com sucesso", "N�o foi poss�vel cadastrar o hist�rico do chamado");
    }

    public function atualiza()
    {
        // n�o implementa! por enquanto...
    }
}