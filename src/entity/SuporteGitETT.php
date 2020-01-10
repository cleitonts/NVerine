<?php


namespace src\entity;


use src\creator\widget\Tools;

class SuporteGitETT extends ObjectETT
{
    public $nome;
    public $hash;
    public $hash_abreviado;
    public $data;

    public function cadastra()
    {
        Tools::returnError("Metodo não implementado", "index2.php?pagina=suportegit");
    }

    public function atualiza()
    {
        $this->nome = strtolower($this->nome);
        $atual = "../".$this->nome;

        
        exec("git -C {$atual} pull https://sistemas-as:7La54gm6BftMercFmkYQ@bitbucket.org/cleiton-as/gestao.git master");
        exec("git -C {$atual} reset --hard {$this->hash_abreviado}");
    }
}