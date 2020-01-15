<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 02/01/2019
 * Time: 15:34
 */

namespace src\creator\widget;

/**
 * Class Tools
 * @package src\creator\widget
 * Ferramentas usadas SOMENTE para criação de interface
 */
class Tools {

    /**
     * @param $var
     * @return mixed
     * insere um valor vazio no inicio do array, observar que os indices precisam ser exatos
     */
    public static function emptyOption($var){
        array_unshift($var["handle"], "");
        array_unshift($var["nome"], "");
        return $var;
    }

    /**
     * @param $input
     * @return array|string
     * Converte um array / objeto para UTF-8
     */
    public static function toUTF8($input, $sinal = false) {
        if (!(is_array($input) || is_object($input))) {
            return utf8_encode($input);
        }
        $output = array();
        foreach ($input as $key => $value) {
            if($sinal){
                if(is_object($value)){
                    $output["".utf8_encode($key)] = self::toUTF8($value, $sinal);
                }
                else{
                    $output[utf8_encode($key)] = self::toUTF8($value, $sinal);
                }
            }
            else{
                $output[utf8_encode($key)] = self::toUTF8($value, $sinal);
            }
        }
        return $output;
    }

    /**
     * @param $obj
     * printa um json
     */
    public static function render($obj){
        global $dumper;

        // faz o push dos prints com o retorno padrão
        if(!__DEVELOPER__) $dumper->dumped = "";
        $printer["dev_log"] = self::toUTF8($dumper->dumped, true);
        $printer["render"] = self::toUTF8($obj);
        print_r(json_encode($printer, JSON_PRETTY_PRINT));
    }

    /**
     * @param $input
     * @return string
     * Converte um array / objeto para JSON
     */
    public static function toJson($input){
        $utf = self::toUTF8($input);
        return json_encode($utf, JSON_PRETTY_PRINT);
    }

    /**
     * @param Form $obj
     * Adiciona os botões de ação nos forms de pesquisa
     */
    public static function footerSearch(Form $obj, $size){

        // ajusta tamanhos caso seja numero impar
        $tam = intval($size / 2);
        $div = $tam;

        if($size % 2 > 0){
            $div += $size % 2;
        }

        // botão de limpar
        $field = new Fields();
        $field->type = $field::BUTTON;
        $field->name = "Limpar";
        $field->size = $tam;
        $field->function = "limpaCampos()";
        $field->class = "btn-warning float-right";
        $obj->field[] = $field;

        // botão de pesquisa
        $field = new Fields();
        $field->type = $field::SUBMIT;
        $field->name = "Pesquisar";
        $field->size = $div;
        $field->class = "btn-success float-right";
        $obj->field[] = $field;
    }

    /**
     * @param String $msg
     * @param bool $link_retorno
     * @return Widget
     * retorna uma mensagem de erro para renderizar na tela
     */
    public static function returnError( $msg, $link_retorno = false){
        $widget = new Widget();
        $widget->header->title = "Erro";
        $widget->header->icon = "fa fa-exclamation-triangle";
        $widget->header->class = "card-header-danger";

        $tabs = new Tabs();
        $tabs->icon = "fa fa-undo";
        $tabs->function = "Tools.retornar('pagina={$link_retorno}')";
        if(!$link_retorno){
            $tabs->function = "Tools.retornar()";
        }

        $component = new Component();
        $component->text = $msg;
        $component->tag = "div";
        $component->attr = array("class" => "col-md-12");

        $tabs->children[] = $component;

        $widget->body->tabs["Retornar"] = $tabs;

        return $widget;
    }
}