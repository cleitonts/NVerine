<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 07/01/2019
 * Time: 16:00
 */

namespace src\creator\widget;


/**
 * Class Tabs
 * @package src\creator\widget
 * cada instancia significa uma tab que fica dentro da body
 */
class Tabs
{
    /**
     * @var string
     * icone que sera exibida ao lado do nome da tab
     */
    public $icon;

    /**
     * @var Form
     * instancia de Form
     */
    public $form;

    /**
     * @var Table
     * instancia de Table
     */
    public $table;

    /**
     * @var array
     * guarda arrays de graficos
     */
    public $charts = array();

    /**
     * @var bool
     * controla se a tabela que está sendo gerada é um relatório
     */
    public $relatorio = false;

    /**
     * @var string
     * caso o nome dentro da header seja apenas um botão
     * ex: Retornar
     */
    public $function;

    /**
     * @var array
     * adiciona um componente, como canvas ou div por exemplo
     */
    public $children = array();

    /**
     * Tabs constructor.
     * cria nova instancia de form e table
     */
    public function __construct()
    {
        $this->form = new Form();
        $this->table = new Table();
    }
}