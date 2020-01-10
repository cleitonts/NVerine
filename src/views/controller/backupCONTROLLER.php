<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 08/08/2019
 * Time: 23:46
 */

namespace src\views\controller;

use src\creator\widget\Fields;
use src\creator\widget\Tabs;
use src\creator\widget\Tools;
use src\creator\widget\Widget;
use src\entity\BackupETT;
use src\views\ControladoraCONTROLLER;

class backupCONTROLLER implements ControladoraCONTROLLER
{

    public function pesquisaGUI()
    {
        // garante que seja processado
        set_time_limit(500);

        $backup = new BackupETT();
        $backup->getStructure();

        $widget = new Widget();
        //$widget->includes[] = "src/public/js/suporte/kanban.js";
        $widget->header->title = "Backup";
        $widget->header->icon = "fa fa-backup";

        // cria body e tabs
        $tabs = new Tabs();
        $tabs->icon = "fa fa-search";

        $field = new Fields();
        $field->type = $field::BUTTON;
        $field->name = "backup";
        $field->description = "Gerar backup";

        $tabs->form->field[] = $field;
        $widget->body->tabs["Editar"] = $tabs;

        $widget->setDefaults();
        Tools::render($widget);
    }

    public function singleGUI()
    {
        // TODO: Implement singleGUI() method.
    }

    public function persist($obj)
    {
        // TODO: Implement persist() method.
    }
}