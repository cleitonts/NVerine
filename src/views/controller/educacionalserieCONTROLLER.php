<?php
/**
 * Created by PhpStorm.
 * User: rafael
 * Date: 27/03/19
 * Time: 11:54.
 */

namespace src\views\controller;

global $__MODULO__;
global $__PAGINA__;

use src\creator\widget\Tools;
use src\entity\EducacionalSerieETT;
use src\entity\EducacionalSerieMateriasETT;
use src\views\ControladoraCONTROLLER;
use src\views\forms\educacionalserieFORM;

$__MODULO__ = 'Educacional';
$__PAGINA__ = 'Série';

class educacionalserieCONTROLLER implements ControladoraCONTROLLER
{
    public function pesquisaGUI()
    {
        $pesquisa = new educacionalserieFORM();
        Tools::render($pesquisa->createSearch());
    }

    public function singleGUI()
    {
        $form = new educacionalserieFORM();
        Tools::render($form->createForm(intval($_REQUEST["pesq_num"])));
    }

    public function persist($obj)
    {
        global $mensagens;

        $mensagens->retorno = '?pagina=educacionalSerie';

        if(empty($obj->handle)){
            $obj->cadastra();
        }else{
            $obj->atualizar();
        }

        if(!empty($obj->materias)){
            foreach($obj->materias as $d){

                // salva a serie como referencia
                $d->serie = $obj->handle;
                if(empty($d->handle)) {
                    $d->cadastra();
                }
                else{
                    $d->atualiza();
                }
            }
        }
    }
}
