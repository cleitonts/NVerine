<?php


namespace src\entity;

use src\services\Transact\ExtPDO as PDO;

    /* objeto de c�lculo do ICMS
 * - puxa o cadastro completo da tabela "tarifas"
 * - calcula a al�quota de acordo com o tipo de pessoa e estado de origem
 *
 * nota 1: esta classe n�o possui um "cadastro" -- existe a p�gina de cadastro
 * para a tabela de tarifas, mas � independente. ICMS n�o implementa ObjetoGUI.
 *
 * nota 2: este n�o � um c�lculo global e extensivo do ICMS. as al�quotas s�o
 * diferentes para cada tipo de produto. cada empresa provavelmente ter� seu
 * pr�prio ICMS. portanto, n�o � vi�vel fazer um cadastro padr�o
 *
 * nota 3: este elemento � chamado do contexto de JSON, ent�o n�o pode
 * disparar mensagens!
 */
class FaturamentoICMSGUI extends ObjectGUI {
    // array de al�quotas por estado
    public $aliquota;

    // par�metros para o c�lculo
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
    // busca todas as al�quotas cadastradas
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
    // puxa o valor correto para os par�metros passados
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

        // retorna o valor cadastrado se existir, sen�o fornece um fallback
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