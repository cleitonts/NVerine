<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 23/09/2019
 * Time: 15:14
 */

namespace src\services\Notifier;


class Chat {
    public $indice;
    public $lido;
    public $remetente;
    public $data;
    public $texto;

    public function __construct($texto = null) {
        $this->texto = $texto;
        $this->lido = false;
        $this->data = agora();
        $this->remetente = "Notificação do sistema";
    }
}