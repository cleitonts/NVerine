<?php


namespace src\views\forms;


use src\creator\widget\Body;
use src\creator\widget\Component;
use src\creator\widget\Fields;
use src\creator\widget\Form;
use src\creator\widget\Header;
use src\creator\widget\Options;
use src\creator\widget\Tabs;
use src\creator\widget\Tools;
use src\creator\widget\Widget;
use src\entity\GaleriaETT;
use src\entity\GaleriaGUI;
use src\services\UAC\UsuarioETT;
use src\services\UAC\UsuarioGUI;
use src\views\ControladoraFORM;

class perfilFORM implements ControladoraFORM
{

    public function createSearch()
    {
        // TODO: Implement createSearch() method.
    }

    public function createForm($handle = null)
    {
        // sempre puxa o usuario logado
        $gui = new UsuarioGUI();
        $gui->fetch();
        $gui = $gui->itens[0];
        $handle = $gui->handle;

        // by the way
        if($handle != $_SESSION["ID"]){
            return Tools::returnError("Método não instanciado!");
        }

        $widget = new Widget();
        //$widget->includes[] = "src/public/js/cadastro/agenda.js";

        $widget->header = new Header();
        $widget->entity = $gui;
        $widget->header->title = "Edição de perfil";

        // cria body e tabs
        $widget->body = new Body();
        $tabs = new Tabs();
        $tabs->icon = "fa fa-pencil";

        // cria form
        $tabs->form = new Form();
        $tabs->form->method = "POST";
        $tabs->form->name = "perfil";
        $tabs->form->action = _pasta . "actions.php?pagina=perfil";

        // ficha
        if (1 == 1) {
            $galeria = new GaleriaGUI();
            $galeria->pesquisa["pesq_target"] = GaleriaETT::TARGET_USUARIO;
            $galeria->pesquisa["pesq_referencia"] = $gui->handle;
            $galeria->fetch();

            $div = new Component();
            $div->setGaleria($galeria, $gui->handle, GaleriaETT::TARGET_USUARIO, true);
            $div->attr["class"] = "col-md-4 p-0";
            $tabs->form->children[] = $div;
        }

        $div2 = new Component();
        $div2->tag = "div";
        $div2->attr["class"] = "col-md-8 p-0";

        $field = new Fields();
        $field->name = "handle";
        $field->type = $field::HIDDEN;
        $field->property = "handle";
        $div2->field[] = $field;

        $field = new Fields();
        $field->name = "retorno";
        $field->type = $field::HIDDEN;
        $field->value = $_REQUEST["retorno"];
        $div2->field[] = $field;

        $field = new Fields();
        $field->name = "handle";
        $field->type = $field::LABEL;
        $field->property = "handle";
        $field->size = 3;
        $div2->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::TEXT;
        $field->name = "E-mail";
        $field->property = "email";
        $field->size = 9;
        $div2->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::PASSWORD;
        $field->name = "senha_antiga";
        $field->description = "Alteração de senha(antiga)";
        $field->size = 6;
        $div2->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::PASSWORD;
        $field->name = "senha_nova";
        $field->description = "Alteração de senha(nova)";
        $field->size = 6;
        $div2->field[] = $field;

        // cria novo campo
        $field = new Fields();
        $field->type = $field::PASSWORD;
        $field->name = "senha_nova2";
        $field->description = "Alteração de senha(nova)";
        $field->size = 6;
        $div2->field[] = $field;

        $filiais = UsuarioETT::listaFiliais($gui);

        // cria novo campo
        $field = new Fields();
        $field->type = $field::SELECT;
        $field->name = "Altere sua filial";
        $field->property = "filial";
        $field->options = Options::byArray($filiais["handle"], $filiais["nome"]);
        $field->size = 6;
        $div2->field[] = $field;

        $field = new Fields();
        $field->type = $field::SUBMIT;
        $field->name = "enviar";
        $field->size = 12;
        $div2->field[] = $field;

        $tabs->form->children[] = $div2;


        $widget->body->tabs["Editar"] = $tabs; // colocar o nome da tab

        $widget->setDefaults();                 // pega todos os valores das entidades e popula

        return $widget;
    }
}