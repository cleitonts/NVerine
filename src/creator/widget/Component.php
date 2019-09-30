<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 10/04/2019
 * Time: 13:40
 */

namespace src\creator\widget;
use src\entity\GaleriaETT;


/**
 * Class Component
 * @package src\creator\widget
 * cria componente para inserir novos elementos dentro
 */
class Component
{
    /**
     * @var
     * nome do componente
     */
    public $tag;

    /**
     * @var array
     * atributos ex: onclick
     */
    public $attr = array();

    /**
     * @var array
     * lista de campos no formulario
     */
    public $field = array();

    /**
     * @var Component
     * para fazer component dentro de component
     */
    public $children;

    /**
     * @var string
     * texto renderizado dentro do component
     */
    public $text;

    /**
     * cria padrão de galeria
     */
    public function setGaleria($galeria, $referencia, $target)
    {
        $images = new Component();
        $images->tag = "div";
        $images->attr = array("class" => "d-flex");

        foreach ($galeria->itens as $r){
            $path = $r::getDir($r->target);
            $wrapper = new Component();
            $wrapper->tag = "div";
            $wrapper->attr = array("class" => "galeria-overlay", "onclick" => "Gallery.abreModal(this, false)");
                $img = new Component();
                $img->setImage($r->url, $r->legenda, $path);
            $wrapper->children[] = $img;

            $images->children[] = $wrapper;
        }

        $this->children[] = $images;

        $this->tag = "div";
        $this->attr = array(
            "id" => "wrapper_galeria",
            "acao" => "gera_galeria",
            "target" => $target,
            "referencia" => $referencia
        );
    }

    public function setImage($src, $alt, $path){
        $nome = end(explode("/", $src));

        $imageFileType = strtolower(pathinfo($path.$nome,PATHINFO_EXTENSION));

        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
            $src2 = "imagens/download.png";

            $this->tag = "img";
            $this->attr = array(
                "src" => $src2,
                "data-name" => $nome,
                "alt" => $alt,
                "file" => $src
            );

            return;
        }

        if(!file_exists($path.$nome)){
            $src = "imagens/imagem-nao-cadastrada.jpg";
        }

        $this->tag = "img";
        $this->attr = array(
            "src" => $src,
            "data-name" => $nome,
            "alt" => $alt,
        );
    }

    public function setAgenda()
    {
        $this->tag = "div";
        $this->attr = array(
            "id" => "wrapper_calendario",
            "acao" => "gera_calendario",
            //"mes" => date("M"),
            "ano" => date("Y"),
        );
    }
}