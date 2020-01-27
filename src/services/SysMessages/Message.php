<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 23/09/2019
 * Time: 15:45
 */

namespace src\services\SysMessages;


use src\services\Transact;

class Message {
    private $texto;
    private $tipo;
    private $timestamp;

    // -----------------------------------------------------------------------------------------
    // métodos públicos
    public function __construct($texto, $tipo) {
        // sanitiza mensagens para possível inserção em javascript
        $texto = str_replace("\"", "'", $texto);
        $texto = str_replace(array("\r\n", "\r", "\n"), "<br />", $texto);

        // padroniza pontuação
        $excecoes = array(".", "?", "!", "'", ")", ">", "-");
        $ultimo = substr($texto, -1);
        if($tipo != MSG_DEBUG && !in_array($ultimo, $excecoes)) $texto .= ".";

        // seta propriedades
        $this->texto = $texto;
        $this->tipo = $tipo;
        $this->timestamp = "<b>".date("H:i:s")."</b>".substr((string)microtime(), 1, 6);
    }

    public function getTexto() {
        return $this->texto;
    }

    public function getTipo() {
        return $this->tipo;
    }

    public function getTimestamp() {
        $ts = $this->timestamp;
        $ts = str_replace("<b>", "", $ts);
        $ts = str_replace("</b>", "", $ts);
        return $ts;
    }

    /* getString altera a codificação por compatibilidade (utf8->iso)
     * é melhor usar getString só no contexto da interface do glassoft.
     * para sistemas que conversem, monte uma string própria com as infos de getTexto e getTipo
     */
    public function getString() {
        $msg = $this->texto;

        // tag
        switch($this->tipo) {
            case MSG_ERRO: 		$msg = "<mark class='red'><i class='i icon-exclamation-sign'></i> Erro</mark> {$msg}"; break;
            case MSG_AVISO: 	$msg = "<mark class='orange'><i class='i icon-minus-sign'></i> Aviso</mark> {$msg}"; break;
            case MSG_SUCESSO:	$msg = "<mark class='green'><i class='i icon-ok-sign'></i> OK</mark> {$msg}"; break;
            case MSG_DEBUG:		$msg = "<mark><i class='i icon-bug'></i> Debug</mark> {$msg}"; break;
        }

        // exibe ou não timestamp?
        if(__DEBUG__) $msg = "<code>{$this->timestamp}</code>&ensp;{$msg}";

        // converte utf-8 para iso?
        // if(mb_detect_encoding($msg, "UTF-8", true)) $msg = utf8_decode($msg);

        return $msg;
    }
}