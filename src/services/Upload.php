<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 03/07/2019
 * Time: 11:02
 */

namespace src\services;


class Upload
{
    public static function upload($fullpath, $nome_temp, $nome){
        // se chegou aqui, tudo esta certo
        if (move_uploaded_file($nome_temp, $fullpath)) {
            mensagem("O arquivo ". $nome. " foi subido com sucesso.");
        } else {
            mensagem("um erro foi encontrado ao subir o arquivo.", MSG_ERRO);
        }
    }
}