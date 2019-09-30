<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 02/01/2019
 * Time: 15:33
 */

namespace src\creator\widget;


/**
 * Class Header
 * @package src\creator\widget
 * header do widget. instanciado somente uma vez
 */
class Header {
    /**
     * @var string
     * titulo que sera exibido na header
     */
    public $title;

    /**
     * @var string
     * icone que sera exibido ao lado do titulo
     */
    public $icon;

    /**
     * @var array
     * menu que sera exibido na esquera da header
     */
    public $menu = array();

    /**
     * @var string
     * classe para complementar a formaчуo do HTML
     */
    public $class;

    /**
     * @var array
     * tabs que щ populada automaticamente pelo sistema
     */
    public $tabs = array();
}