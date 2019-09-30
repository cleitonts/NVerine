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
    public $header = array();        // lista com os nomes de campos (obrigat�ria)
    public $header_abrev = array();    // lista alternativa com nomes abreviados
    public $exibe = array();        // colunas a exibir por default nos relat�rios

    // ------------------------------------------------------------------------------------------------------------------
    // definir na inst�ncia
    public $top;                    // limite de registros no fetch -- deve ser definido nas pesquisas, mas n�o em relat�rios!
    // informar toda a string, n�o s� o valor. ex.: "TOP 500"

    // ------------------------------------------------------------------------------------------------------------------
    // filtros
    public $pesquisa = array();        // filtros em pesquisa (falta padronizar nas extens�es que t�m as vari�veis $pesq_stuff)

    public function __construct($handle = null)
    {

    }

    // ------------------------------------------------------------------------------------------------------------------
    /* FUN��ES COMPARTILHADAS
     * mapeia os par�metros de pesquisa da p�gina para este objeto
     */
    public function setPesquisa()
    {
        foreach ($_REQUEST as $key => $value) {
            if (strpos($key, "pesq_") !== false) {
                $this->pesquisa[$key] = $value;
            }
        }
    }

    /* exporta��o autom�tica do conte�do dos itens em JSON.
     * isso facilitar� um monte nossas integra��es entre sistemas
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
     * faz recurs�o de arrays e objetos (EXPERIMENTAL)
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