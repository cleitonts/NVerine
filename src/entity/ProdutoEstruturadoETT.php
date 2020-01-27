<?php


namespace src\entity;


class ProdutoEstruturadoETT extends ObjectETT
{
    public $pai;
    public $filho;
    public $cod_pai;
    public $cod_filho;
    public $unidade;
    public $quantidade;
    public $valor_unitario;
    public $valor_total;
    public $markup;

    // métodos públicos
    public function cadastra()
    {
        global $conexao;

        if (empty($this->cod_filho) || empty(intval($this->cod_filho))) {
            mensagem("um dos filhos do produto estruturado nao foi preenchido corretamente, pulando", MSG_AVISO);
            return;
        }

        $this->handle = newHandle("K_FN_PRODUTOESTRUTURADO", $conexao);
        
        // insere produto
        $stmt = $this->insertStatement("K_FN_PRODUTOESTRUTURADO",
            array(
                "HANDLE" =>  $this->handle,
                "PAI" =>  $this->cod_pai,
                "FILHO" =>  intval($this->cod_filho),
                "UNIDADE" =>  substr($this->unidade, 0, 2),
                "QUANTIDADE" =>  intval($this->quantidade),
                "QUANTIDADEFLOAT" =>  $this->quantidade,
                "UNITARIO" =>  $this->valor_unitario,
            ));

        retornoPadrao($stmt, "Produto filho inserido com sucesso.", "Erro inserindo produto filho. Por favor, tente reinserir os itens.");
    }

    public function limpa()
    {
        $stmt = $this->deleteStatement("K_FN_PRODUTOESTRUTURADO", array( "PAI" => $this->cod_pai));

        // não uso retornoPadrão porque um delete que retornou vazio não é necessariamente um erro
        $err = $stmt->errorInfo();
        $msg = "Atualizando produto estruturado...";
        if (__DEBUG__) $msg .= " [" . $err[2] . "]";
        mensagem($msg);
    }

}