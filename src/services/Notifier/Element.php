<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 23/09/2019
 * Time: 15:15
 */

namespace src\services\Notifier;


class Element {
    // propriedades
    public $texto;
    public $data;
    public $icone;
    public $link;

    // métodos públicos
    public function __construct($texto) {
        $this->texto = $texto;
    }

    public function render() {
        // defaults
        if(empty($this->link)) {
            $this->link = "#";
        }
        else { // insere parâmetro de retorno
            $this->link .= "&retorno=".urlencode(getUrlRetorno());
        }

        if(empty($this->data)) $this->data = agora();
        if(empty($this->icone)) $this->icone = "warning";

        // monta html
        $str = "
		<a href='{$this->link}' class='u-single-mesg'>
		<i class='fa fa-{$this->icone}'></i><p>{$this->texto}</p>
		<span><i class='fa fa-calendar'></i>&ensp;{$this->data}</span>
		</a>
		";

        return $str;
    }
}
