<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 23/09/2019
 * Time: 14:05
 *
 * common functions for all the system
 */

include_once("src/services/Transact/Transact.php");
$transact = new \src\services\Transact();

define("MSG_PADRAO", $transact::MSG_PADRAO);
define("MSG_ERRO", $transact::MSG_ERRO);
define("MSG_AVISO", $transact::MSG_AVISO);
define("MSG_SUCESSO", $transact::MSG_SUCESSO);
define("MSG_DEBUG", $transact::MSG_DEBUG);

// compatibilidade com c�digo herdado
function finaliza(){
    global $transact;
    $transact->finaliza();
}
function filtraFilial($campo, $modulo, $inclui_newline = true){
    global $transact;
    return $transact->filtraFilial($campo, $modulo, $inclui_newline);
}

function mensagem($msg, $tag = MSG_PADRAO){
    global $transact;
    $transact->mensagem($msg, $tag);
}
function doCommit($con){
    global $transact;
    $transact->doCommit($con);
}
function doRollback($con){
    global $transact;
    $transact->doRollback($con);
}
function getUrlRetorno(){
    global $transact;
    return $transact->getUrlRetorno();
}

// retorna somente a pagina e parametros
function getPaginaRetorno(){
    global $transact;
    $temp =  $transact->getUrlRetorno();
    return str_replace(_pasta . "index.php", "", $temp);
}

function iniciaTransacao($con){
    global $transact;
    $transact->iniciaTransacao($con);
}

function validaCampo($campo, $nome){
    global $transact;
    $transact->validaCampo($campo, $nome);
}

function retornoPadrao($stmt, $sucesso = "Sucesso!", $erro = "Ser� que voc� deixou algum campo em branco?"){
    global $transact;
    return $transact->retornoPadrao($stmt, $sucesso, $erro);
}

function newHandle($tabela, $con){
    global $transact;
    return $transact->newHandle($tabela);
}

function redir($con){
    global $transact;
    $transact->redir($con);
}

/**
 * @param $string
 * @return null|string|string[]
 * clear a string
 */
function sanitize($string){
    $string = preg_replace("/[�����]/", "a", $string);
    $string = preg_replace("/[�����]/", "A", $string);
    $string = preg_replace("/[���]/", "e", $string);
    $string = preg_replace("/[���]/", "E", $string);
    $string = preg_replace("/[��]/", "i", $string);
    $string = preg_replace("/[��]/", "I", $string);
    $string = preg_replace("/[�����]/", "o", $string);
    $string = preg_replace("/[�����]/", "O", $string);
    $string = preg_replace("/[���]/", "u", $string);
    $string = preg_replace("/[���]/", "U", $string);
    $string = preg_replace("/�/", "c", $string);
    $string = preg_replace("/�/", "C", $string);
    $string = preg_replace("/[][><}{)(:;,.!?*%~^`@]/", "", $string);
    $string = preg_replace("/ /", "_", $string);
    return $string;
}

function safercrypt($string, $salt = null) {
    /*
     * gera um salt default.
     * isso vai gerar uma string relativamente longa e �nica para cada entrada
     * ainda assim, duas entradas id�nticas ter�o o mesmo resultado.
     * o ideal seria, para o caso das senhas, combinar com o handle do usu�rio
     */
    if(!$salt) $salt = encrypt($string);

    // salga
    $string = $string.$salt;

    // criptografa
    $string = sha1($string);

    // prefixo para compatibilidade
    $string = "!enc{$string}";

    return $string;
}

/*
 * FAIR WARNING: encrypt/decrypt n�o � seguro, s� � obscuro.
 * estou deprecando o $retorno que n�o � usado em lugar nenhum
 * para mais seguran�a, use a rotina nova
 */
function encrypt($string, $param_deprecado = null) {
    return base64_encode(base64_encode(base64_encode($string)));
}

function decrypt($string, $param_deprecado = null) {
    return base64_decode(base64_decode(base64_decode($string)));
}

/**
 * @param $texto
 * @param bool $nome_proprio
 * @return mixed|string
 *
 * make Capitalize on names
 * Brazilian default
 */
function formataCase($texto, $nome_proprio = false) {
    // usar essa fun��o como wrapper de ucwords/ucfirst para corrigir problemas de codifica��o
    $texto = strtolower($texto);
    $texto = str_replace("�", "�", $texto);
    $texto = str_replace("�", "�", $texto);
    $texto = str_replace("�", "�", $texto);
    $texto = str_replace("�", "�", $texto);
    $texto = str_replace("�", "�", $texto);
    $texto = str_replace("�", "�", $texto);
    $texto = str_replace("�", "�", $texto);
    $texto = str_replace("�", "�", $texto);
    $texto = str_replace("�", "�", $texto);
    $texto = str_replace("�", "�", $texto);
    $texto = str_replace("�", "�", $texto);

    // formata todas as partes separadas por '--'
    $partes = explode("--", $texto);
    $texto = "";

    foreach($partes as $p) {
        if(strlen($texto) > 0) $texto .= ") "; // posso trocar o separador se quiser

        if($nome_proprio)
            $texto .= ucwords(trim($p));
        else
            $texto .= ucfirst(trim($p));
    }

    // mant�m case de preoposi��es, conju��es, etc.
    $excecoes = array("O", "E", "A", "Os", "As", "Do", "De", "Da", "Dos", "Das", "Em", "No", "Na", "Nos", "Nas", "Para");
    foreach($excecoes as $excecao) {
        $texto = str_replace(" {$excecao} ", " ".strtolower($excecao)." ", $texto);
    }

    // mant�m uppercase de numerais romanos
    $excecoes = array("Ii", "Iii");
    foreach($excecoes as $excecao) {
        $texto = str_replace(" {$excecao} ", " ".strtoupper($excecao)." ", $texto);
    }

    return $texto;
}

function bubblesort($gui, $coluna) {
    // descobre o n�mero de itens
    $max = count($gui->itens);
    if($max < 2) return $gui;

    // gera uma tabela lookup de getCampo
    $campos = array();

    for($i = 0; $i < $max; $i++) {
        $campos[] = $gui->getCampo($i, $coluna);
    }

    // ordena -- isso � INSERT sort, n�o mais bubble
    for($i = 1; $i < $max; $i++) {
        for($k = $i; $k > 0; $k--) {
            $atual = $campos[$k - 1][0];
            $prox = $campos[$k][0];

            // qual tipo de ordena��o usar?
            if(is_numeric($atual))
                $resultado = ($atual > $prox);
            else
                $resultado = (strcasecmp($atual, $prox) > 0);

            // troca
            if($resultado) {
                $temp = $gui->itens[$k];
                $gui->itens[$k] = $gui->itens[$k - 1];
                $gui->itens[$k - 1] = $temp;

                // temos que trocar a tabela lookup de getCampo tamb�m, mas a otimiza��o compensa!
                $temp = $campos[$k];
                $campos[$k] = $campos[$k - 1];
                $campos[$k - 1] = $temp;
            }
            else {
                break;
            }
        }

        // testa o tempo de execu��o at� aqui!
        global $ts_inicio;
        $delta = time() - $ts_inicio;

        if($delta > 60) {
            mensagemErro("
				Este relat�rio est� demorando demais para ser gerado.
				Por gentileza, especifique uma janela de tempo mais curta,
				use filtros para reduzir o n�mero de registros
				ou remova um agrupamento/ordena��o");
            die();
        }
    }

    // refaz conts
    for($linha = 0; $linha < $max; $linha++) {
        $gui->itens[$linha]->cont = $linha;
    }

    return $gui;
}

function campo($valor, $classe = "") {
    return array($valor, $classe);
}

function formataLogico($val) {
    if(is_null($val)) return "<span class='misterio'><i class='i icon-remove'></i></span><span class='escondido'>N</span>";
    if($val == "N") return "<span class='vermelho'><i class='i icon-remove'></i></span><span class='escondido'>N</span>";
    if($val == "S") return "<span class='verde'><i class='i icon-ok'></i></span><span class='escondido'>S</span>";
    // return "<span class='misterio'><i class='i icon-question'></i></span><span class='escondido'>{$val}</span>";
    return $val;
}

// substrings como no basic
function left($str, $length) {
    return substr($str, 0, $length);
}

function right($str, $length) {
    return substr($str, -$length);
}

// preenche string de tamanho fixo com zeros
function insereZeros($str, $qtd, $lado = STR_PAD_LEFT) {
    // faz pad de n�mero negativo?
    if(strpos($str, "-") !== false) {
        $str = str_replace("-", "", $str);
        $str = str_pad($str, $qtd, "0", $lado);
        $str = "-{$str}";
    }
    else {
        $str = str_pad($str, $qtd, "0", $lado);
    }

    return $str;
}

// preenche string de tamanho fixo com brancos
function insereBrancos($str, $qtd, $lado = STR_PAD_LEFT) {
    $str = str_pad($str, $qtd, ' ', $lado);
    $str = substr($str, 0, $qtd);
    return $str;
}

// converte array de valores em uma lista separada por v�rgulas
function arrayToString($array, $aspas = true) {
    $i = 0;
    $out = '';
    if(isset($array)){
        foreach($array as $string){
            if($i > 0)
                $out .= ',';
            if($aspas)
                $out .= "'" . $string . "'";
            else
                $out .= $string;
            $i++;
        }
    }
    return $out;
}

function validaVazio($campo) {
    if(empty($campo))
        return null;
    else
        return $campo;
}

// inclui sql para pesquisar string de acordo com a regra de pesquisa parametrizada em config
function stringPesquisa($str) {
    // s� fazer %pesquisa% se o n�mero de caracteres for maior que o determinado aqui!
    if(__PESQ_PORCENTO__ && strlen($str) >= 4) {
        return "%".$str."%";
    }
    else {
        return $str."%";
    }
}

function anti_injection($sql)
{
    $sql = trim($sql);
    $sql = strip_tags($sql);
    $sql = addslashes($sql);
    $sql = str_replace("--","",$sql);
    $sql = str_replace("*","",$sql);
    return $sql;
}

// trata sql de labels concatenadas para compatibilidade com mysql
function sqlConcat($str) {
    if(__DB_DRIVER__ == "mysql") {
        $partes = explode("+", $str);
        $lista = "";

        foreach($partes as $parte) {
            $lista .= "{$parte}, ";
        }

        $lista = trim($lista, ", ");

        return "CONCAT({$lista})";
    }

    return $str;
}

// mostra campos de valor zero como vazios
function noZeroes($campo) {
    if($campo == 0 || $campo == "0.00")
        return null;
    else
        return $campo;
}

// valores monet�rios
function formataValor($val) {
    return number_format((float)$val, __CASAS_DECIMAIS__, '.', '');
}

// filtra caracteres n�o-num�ricos
function apenasNumeros($string) {
    $string = preg_replace('/[^0-9]/', '', $string);
    return $string;
}

// converte checkbox em valor l�gico do benner
function logico($campo) {
    if(isset($_REQUEST[$campo]))
        return "S";
    else
        return "N";
}

// converte true/false em valor l�gico do benner
function boolToLogico($valor) {
    if($valor)
        return "S";
    else
        return "N";
}

/**
 * limpaString
 * Remove todos dos caracteres especiais do texto e os acentos
 * preservando apenas letras de A-Z numeros de 0-9 e os caracteres @ , - ; : / _
 *
 * @name limpaString
 * @param string $texto String a ser limpa
 * @return  string Texto sem caractere especiais
 */
function limpaString($texto, $preg = true){
    $aFind = array( '&', '�', '�', '�', '�', '�', '�',
        '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�',
        '�', '�', '�', '�', '�', '�', '�', '�', '�');

    $aSubs = array( 'e', 'a', 'a', 'a', 'a', 'e', 'e',
        'i', 'o', 'o', 'o', 'u', 'u', 'c', 'A', 'A', 'A', 'A',
        'E', 'E', 'I', 'O', 'O', 'O', 'U', 'U', 'C');

    $novoTexto = str_replace($aFind, $aSubs, $texto);
    if($preg) $novoTexto = preg_replace("/[^a-zA-Z0-9 @,-.;:\/_]/", "", $novoTexto);
    return $novoTexto;
}