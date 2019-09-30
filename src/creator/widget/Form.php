<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 02/01/2019
 * Time: 15:35
 */

namespace src\creator\widget;


/**
 * Class Form
 * @package src\creator\widget
 * cada instancia significa um form dentro da tabs
 */
class Form {
    /**
     * @var string
     * POST OU GET
     */
    public $method;

    /**
     * @var string
     * nome do formulario, uma página pode conter vários formularios
     */
    public $name;

    /**
     * @var string
     * pagina para onde o formulario será submetido
     */
    public $action;

    /**
     * @var string ????
     * caso o formulario englobe todas as tabs
     */
    public $pointer;

    /**
     * @var ????
     */
    public $prefix;

    /**
     * @var array
     * caso o formulario tenha alguma tabela de campos.
     * isto é um array pois um formulario pode ter varias tabelas
     */
    public $table = array();            // jogar os campos dentro de um array

    /**
     * @var array
     * adiciona um componente, como canvas ou div por exemplo
     */
    public $children = array();

    /**
     * @var array
     * lista de campos no formulario (fora de tabelas)
     */
    public $field = array();
    //public $args = array();
}