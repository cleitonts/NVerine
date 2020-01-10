<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 04/05/2019
 * Time: 20:51
 */

namespace src\services;

class Dumper
{
    /**
     * @var
     * lista de itens a serem printados
     */
    public $dumped;

    public static function dump($var, $trace = false){
        global $dumper;

        // monstra o caminho percorrido
        $backtrace = debug_backtrace();

        // caso não seja chamado tireto
        if($trace){
            $backtrace = $trace;
        }

        // guarda a linha e o arquivo na variavel a ser printada
        $arr = array();
        $arr["back_file"] = $backtrace[0]["file"];
        $arr["back_line"] = $backtrace[0]["line"] . " - ".date('H:m:s.') . round(microtime(true) / 1000000);
        $arr["dump"] = $var;

        $dumper->dumped[] = $arr;
    }
}