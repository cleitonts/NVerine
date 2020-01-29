<?php


namespace src\entity;

use src\services\Transact\ExtPDO as PDO;

class FaturamentoOperacaoGUI extends ObjectGUI
{
    public function __construct($handle = null)
    {
        $this->header = array("Cód. registro", "Cód. numérico", "Nome", "Tipo", "CST", "CFOP", "CSOSN", "ST PIS/COFINS", "ST IPI", "cEnq IPI");
    }

    public function getCampo($linha, $coluna)
    {
        // indexa o item
        $item = $this->itens[$linha];

        // para a coluna, retorna um array com o valor e a classe a aplicar
        return $this->campos($coluna, array(
            campo($item->handle, "numerico"),
            campo($item->codigo, "numerico stronk"),
            campo($item->nome),
            campo($item->tipo_movimento),
            campo($item->cst),
            campo($item->cfop),
            campo($item->csosn),
            campo($item->cst_pis_cofins),
            campo($item->cst_ipi),
            campo($item->enquadramento_ipi)
        ));
    }

    public function fetch()
    {
        global $conexao;

        // inicializa array
        $this->itens = array();

        // monta query de pesquisa
        $where = "WHERE 1 = 1\n";
        if (!empty($this->pesquisa["pesq_codigo"])) {
            $where .= "AND T.HANDLE = :handle\n";
        }

        // puxa dados
        $sql = "SELECT T.*
				FROM K_TIPOOPERACAO T
				{$where}
				ORDER BY T.CODIGO ASC";
        $stmt = $conexao->prepare($sql);

        if (!empty($this->pesquisa["pesq_codigo"])) {
            $stmt->bindValue(":handle", $this->pesquisa["pesq_codigo"]);
        }

        $stmt->execute();
        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        // insere no array
        $i = 0;

        if (!empty($f)) {
            foreach ($f as $r) {
                $item = new FaturamentoOperacaoETT();
                $item->cont = $i;

                $item->handle = $r->HANDLE;
                $item->codigo = $r->CODIGO;
                $item->nome = formataCase($r->NOME);
                $item->descricao = $r->DESCRICAO;
                $item->tipo_movimento = $r->TIPO; // literal
                $item->cod_tipo_movimento = $r->TIPO;
                $item->modalidade = $r->MODALIDADE; // literal
                $item->cod_modalidade = $r->MODALIDADE;
                // $item->destino = $item->getNomeDestino($r->DESTINO);
                // $item->cod_destino = $r->DESTINO;
                // $item->estado = $r->ESTADO;
                $item->cfop = $r->CFOP;
                $item->cst_origem = $r->CSTORIGEM;
                $item->cst_tributacao = $r->CSTTRIBUTACAO;
                $item->cst_ipi = empty($r->CSTIPI) ? $r->CSTTRIBUTACAO : $r->CSTIPI; // compatibilidade reversa!
                $item->enquadramento_ipi = empty($r->CENQIPI) ? "999" : $r->CENQIPI; // ||
                $item->csosn = $r->CSOSN;
                $item->cst = $item->cst_origem . $item->cst_tributacao;
                $item->gera_financeiro = $r->GERAFINANCEIRO;
                $item->tributado = $r->TRIBUTADO;
                $item->credita_icms = $r->CREDITAICMS;
                $item->credita_ipi = $r->CREDITAIPI;
                $item->substituicao_tributaria = $r->SUBSTITUICAOTRIB;
                $item->tipo_icms = $r->TIPOICMS; // literal
                $item->cod_tipo_icms = $r->TIPOICMS;
                $item->base_calculo_icms = $r->CALCULOBASE;
                $item->aliquota_icms = null; // inutilizado! veja objeto ICMS
                $item->aliquota_pis = $r->ALIQUOTAPIS;
                $item->aliquota_cofins = $r->ALIQUOTACOFINS;
                $item->aliquota_issqn = $r->ALIQUOTAISSQN;
                $item->cst_pis_cofins = $r->CSTPISCOFINS;

                array_push($this->itens, $item);

                $i++;
            }
        }
    }
}