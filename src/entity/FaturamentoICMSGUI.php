<?php


namespace src\entity;

use src\services\Transact\ExtPDO as PDO;

    /* objeto de cálculo do ICMS
 * - puxa o cadastro completo da tabela "tarifas"
 * - calcula a alíquota de acordo com o tipo de pessoa e estado de origem
 *
 * nota 1: esta classe não possui um "cadastro" -- existe a página de cadastro
 * para a tabela de tarifas, mas é independente. ICMS não implementa ObjetoGUI.
 *
 * nota 2: este não é um cálculo global e extensivo do ICMS. as alíquotas são
 * diferentes para cada tipo de produto. cada empresa provavelmente terá seu
 * próprio ICMS. portanto, não é viável fazer um cadastro padrão
 *
 * nota 3: este elemento é chamado do contexto de JSON, então não pode
 * disparar mensagens!
 */
class FaturamentoICMSGUI extends ObjectGUI {
    // array de alíquotas por estado
    public $aliquota;

    // parâmetros para o cálculo
    public $uf_origem;
    public $uf_destino;
    public $tipo_cliente; // F/J

    // ---------------------------------------------------------
    // construtor
    public function __construct($handle = null) {
        $this->aliquota = array();
        $this->fetch();
    }

    // ---------------------------------------------------------
    // busca todas as alíquotas cadastradas
    public function fetch() {
        global $conexao;

        $sql = "SELECT E.SIGLA, T.ALIQUOTA
				FROM K_FN_TARIFAS T
				LEFT JOIN ESTADOS E ON T.ESTADO = E.HANDLE
				WHERE T.NOME LIKE 'ICMS%'";
        $stmt = $conexao->prepare($sql);
        $stmt->execute();
        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        if(!empty($f)) {
            foreach($f as $r) {
                $this->aliquota[$r->SIGLA] = $r->ALIQUOTA;
            }
        }
    }

    // ---------------------------------------------------------
    // puxa o valor correto para os parâmetros passados
    public function getAliquota() {
        // determina a UF do fato gerador
        if($this->tipo_cliente == "F") {
            // venda para PF: ICMS do estado da filial
            $uf = $this->uf_origem;
        }
        else {
            // venda para PJ: ICMS do estado do cliente
            $uf = $this->uf_destino;
        }

        // determina um estado fallback
        if(empty($uf)) $uf = "DF";

        // retorna o valor cadastrado se existir, senão fornece um fallback
        if(isset($this->aliquota[$uf])) {
            return $this->aliquota[$uf];
        }
        else {
            return 12;
        }
    }

    /**
     * @param $linha
     * @param $coluna
     * @return mixed
     * valor dos campos para exibir nos relatorio
     */
    public function getCampo($linha, $coluna)
    {
        // TODO: Implement getCampo() method.
    }
}