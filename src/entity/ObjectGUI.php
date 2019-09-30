<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 25/01/2019
 * Time: 11:01
 */

namespace src\entity;

abstract class ObjectGUI implements InterfaceGUI
{
    public $handle;

    // ------------------------------------------------------------------------------------------------------------------
    // propriedades que fetch() alimenta
    public $itens = array();        // um array de objetos
    public $saldo;                    // para classes que implementam conta de saldo

    // ------------------------------------------------------------------------------------------------------------------
    // definir no construtor
    public $header = array();        // lista com os nomes de campos (obrigatória)
    public $header_abrev = array();    // lista alternativa com nomes abreviados
    public $exibe = array();        // colunas a exibir por default nos relatórios

    // ------------------------------------------------------------------------------------------------------------------
    // definir na instância
    public $top;                    // limite de registros no fetch -- deve ser definido nas pesquisas, mas não em relatórios!
    // informar toda a string, não só o valor. ex.: "TOP 500"

    // ------------------------------------------------------------------------------------------------------------------
    // filtros
    public $pesquisa = array();        // filtros em pesquisa (falta padronizar nas extensões que têm as variáveis $pesq_stuff)

    public function __construct($handle = null)
    {

    }

    // ------------------------------------------------------------------------------------------------------------------
    /* FUNÇÕES COMPARTILHADAS
     * mapeia os parâmetros de pesquisa da página para este objeto
     */
    public function setPesquisa()
    {
        foreach ($_REQUEST as $key => $value) {
            if (strpos($key, "pesq_") !== false) {
                $this->pesquisa[$key] = $value;
            }
        }
    }

    /* exportação automática do conteúdo dos itens em JSON.
     * isso facilitará um monte nossas integrações entre sistemas
     */
    public function exportaJSON()
    {
        $json = array();

        if (!empty($this->itens)) {
            foreach ($this->itens as $item) {
                $json_row = array();

                foreach ($item as $key => $value) {
                    $json_row[$key] = $this->trataJSON($value);
                }

                array_push($json, $json_row);
            }
        }

        return json_encode($json);
    }

    /* sanitiza valores para gerar JSON correto
     * faz recursão de arrays e objetos (EXPERIMENTAL)
     */
    private function trataJSON($var)
    {
        if (is_array($var) || is_object($var)) {
            $json_row = array();

            foreach ($var as $key => $value) {
                $json_row[$key] = $this->trataJSON($value);
            }

            return $json_row;
        } else {
            $var = strip_tags($var, "<p><a><br><ul><li>");
            $var = utf8_encode($var);

            return $var;
        }
    }

    /* wrapper para o retorno de getCampo que permite definir sem enumerar um switch
     * (isso pode ser mais lento?)
     */
    protected function campos($coluna, $itens)
    {
        return $itens[$coluna];
    }
}