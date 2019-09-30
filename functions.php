<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 23/09/2019
 * Time: 14:05
 *
 * common functions for all the system
 */

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

// url de retorno para a mesma p�gina
function getUrlRetorno() {
    // monta url
    $url = _pasta."?";

    // filtra par�metros vazios
    $params = explode("&", $_SERVER["QUERY_STRING"]);

    foreach($params as $param) {
        $partes = explode("=", $param);
        if(!empty($partes[1])) {
            $url .= "{$partes[0]}={$partes[1]}&";
        }
    }

    // remove o �ltimo separador
    $url = trim($url, "&");
    return $url;
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