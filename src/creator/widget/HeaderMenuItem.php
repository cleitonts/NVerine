<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 07/01/2019
 * Time: 15:19
 */

namespace src\creator\widget;


class HeaderMenuItem
{
    public $icon;
    public $function;
    public $description;

    public function __construct($description = null, $function = null, $icon = null)
    {
        $this->icon = $icon;
        $this->function = $function;
        $this->description = $description;
    }
}