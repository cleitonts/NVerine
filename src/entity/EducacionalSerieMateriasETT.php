<?php

namespace src\entity;


class EducacionalSerieMateriasETT extends ObjectETT
{
    public $codigo;
    public $base_curricular;
    public $area_conhecimento;
    public $serie;
    public $mascara_nota;
    public $carga_horaria;
    public $componente_curricular;

    public function validaForm(){
        global $transact;

        // campos obrigatorios
        validaCampo($this->componente_curricular, "Componentes curriculares");
        validaCampo($this->mascara_nota, "M�scara de nota");
        validaCampo($this->codigo, "C�digo");
        validaCampo($this->base_curricular, "Base curricular");
        validaCampo($this->area_conhecimento, "�rea do conhecimento");
    }

    public function cadastra()
    {
        global $conexao;

        $this->validaForm();

        $this->handle = newHandle('K_SERIE_MATERIA', $conexao);

        // gera novas listas de pre�o
        $sql = 'INSERT INTO K_SERIE_MATERIA 
                (HANDLE, SERIE, COMPONENTE_CURRICULAR, BASE_CURRICULAR, AREA_CONHECIMENTO, CODIGO, MASCARA_NOTA, CARGA) VALUES 
                (:handle, :serie, :componente_curricular, :base_curricular, :area_conhecimento, :codigo, :mascara, :carga)';

        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(':handle', $this->handle);
        $stmt->bindValue(':serie', $this->serie);
        $stmt->bindValue(':mascara', $this->mascara_nota);
        $stmt->bindValue(':componente_curricular', $this->componente_curricular);
        $stmt->bindValue(':base_curricular', $this->base_curricular);
        $stmt->bindValue(':area_conhecimento', $this->area_conhecimento);
        $stmt->bindValue(':codigo', $this->codigo);
        $stmt->bindValue(':carga', $this->carga_horaria);

        $stmt->execute();

        retornoPadrao($stmt, "Cadastrada o educacional  #{$this->handle}", "N�o foi poss�vel cadastrar o educacional  {$this->handle}");
    }

//    public static function limpar($serie)
//    {
//        global $transact;
//        global $conexao;
//
//        // gera novas listas de pre�o
//        $sql = 'DELETE FROM K_SERIE_MATERIA WHERE SERIE = :serie;';
//
//        $stmt = $conexao->prepare($sql);
//
//        $stmt->bindValue(':serie', $serie);
//
//        $stmt->execute();
//
////        $transact->retornoPadrao($stmt, "Limpar a lista do educacional  #{$serie}", "N�o foi poss�vel limpar a lista educaciona  {$serie}");
//
//    }

    public function atualiza()
    {
        global $transact;
        global $conexao;

        $this->validaForm();

        $sql = "UPDATE K_SERIE_MATERIA SET
                SERIE = :serie, 
                COMPONENTE_CURRICULAR = :componente_curricular, 
                BASE_CURRICULAR = :base_curricular, 
                MASCARA_NOTA = :mascara, 
                AREA_CONHECIMENTO = :area_conhecimento, 
                CODIGO = :codigo,
                CARGA = :carga
                WHERE HANDLE = :handle";


        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(':handle', $this->handle);
        $stmt->bindValue(':serie', $this->serie);
        $stmt->bindValue(':mascara', $this->mascara_nota);
        $stmt->bindValue(':componente_curricular', $this->componente_curricular);
        $stmt->bindValue(':base_curricular', $this->base_curricular);
        $stmt->bindValue(':area_conhecimento', $this->area_conhecimento);
        $stmt->bindValue(':codigo', $this->codigo);
        $stmt->bindValue(':carga', $this->carga_horaria);

        $stmt->execute();

        retornoPadrao($stmt, "Atualizar a lista do Educacional #{$this->handle}", "N�o foi poss�vel atualizar a lista do educacional {$this->handle}");
    }

    public static function getBaseNacionalComum($ciclo_etapa = 0, $lista_completa = false)
    {
        $array_base = array(
            '',
            'Base nacional comum',
            'Parte diversificada',
            'Educa��o de tempo integral',
            'Referencial curricular nacional',
            'Par�metros Curriculares Nacional'
        );

        if ($lista_completa) {
            return $array_base;
        }

        return $array_base[$ciclo_etapa];
    }

    public static function getAreaConhecimento($conhecimento = 0, $lista_completa = false)
    {
        $array_base = array(
            '',
            'Linguagem',
            'Linguagem e suas tecnologias',
            'Matem�tica',
            'Matem�tica e suas tecnologias',
            'Ci�ncias da natureza',
            'Ci�ncias da natureza e suas tecnologias',
            'Ci�ncias humanas',
            'Ci�ncias humanas e sociais aplicadas',
            'Ensino religioso',
            'Forma��o t�cnica e profissional',
            'Forma��o pessoal e social',
            'Conhecimento do mundo',
            'Acompanhamento pedag�gico',
            'Esporte e lazer',
            'Temas sociais contempor�neos',
            'Cultura, arte e educa��o patrimonial',
            'Comunica��o e uso de m�dia'
        );

        if ($lista_completa) {
            return $array_base;
        }

        return $array_base[$conhecimento];
    }

    public static function getComponentesCurriculares($conhecimento = 0, $lista_completa = false)
    {
        // separado para componenete curricular
        $array_curricular = array(
            '',
        );

        if ($lista_completa) {
            return $array_curricular;
        }

        return $array_curricular[$conhecimento];
    }




}
