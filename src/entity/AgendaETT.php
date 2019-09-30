<?php
/**
 * Created by PhpStorm.
 * User: rafael
 * Date: 02/04/2019
 * Time: 10:02
 */

namespace src\entity;

use src\services\Transact\ExtPDO as PDO;

class AgendaETT extends ObjectETT
{
    // tipos de eventos
    const DIA_LETIVO = 1;
    const FERIAS = 2;
    const FERIADO = 3;
    const RECESSO = 4;
    const ATIVIDADES_PEDAGOGICAS = 5;
    const AVALIACAO = 6;
    const EXCURSAO = 7;
    const DIV_BIMESTRE = 8;
    const DIV_TRIMESTRE = 9;
    const PERIODO_ANO = 10;
    const PERIODO_SEMESTRE = 11;

    public $handle;
    public $data_inicial;
    public $hora_inicial;
    public $data_final;
    public $hora_final;
    public $titulo;
    public $evento_ativo;
    public $tipo_evento;
    public $conteudo;

    public $cod_regiao;
    public $regiao;
    public $cod_escola;
    public $escola;
    public $cod_turma;
    public $turma;
    public $cod_aluno;
    public $aluno;
    public $cod_pessoa;
    public $pessoa;
    public $status;
    public $responsavel;
    public $nome_responsavel;

    public function validaForm(){
        global $transact;
        // campos obrigatorios
        $transact->validaCampo($this->data_inicial, "Data inicial");
        $transact->validaCampo($this->data_final, "Data final");
        $transact->validaCampo($this->titulo, "Titulo do evento");
        $transact->validaCampo($this->evento_ativo, "Evento ativo");
        //$transact->validaCampo($this->tipo_evento, "Tipo evento");
    }

    public function cadastra()
    {
        global $transact;
        global $conexao;

        $this->validaForm();

        $this->handle = newHandle('K_AGENDA', $conexao);

        $stmt = $this->insertStatement("K_AGENDA",
            array(
                "HANDLE" =>  $this->handle,
                "FILIAL" => __FILIAL__,
                "DATA" => $this->data_inicial,
                "DATAFINAL" => $this->data_final,
                "HORA" => $this->hora_inicial,
                "TITULO" => $this->titulo,
                "ATIVO" => $this->evento_ativo,
                "REGIAO" => validaVazio($this->cod_regiao),
                "CONTEUDO" => $this->conteudo,
                "FILIAL" => validaVazio($this->cod_escola),
                "TURMA" => validaVazio($this->cod_turma),
                "PESSOA" => validaVazio($this->cod_pessoa),
                "USUARIO" => validaVazio($this->responsavel),
                "HORAFINAL" => $this->hora_final,
            ));

        $nome_evento = AgendaETT::getNomeEvento($this->tipo_evento);
        $transact->retornoPadrao($stmt, "Novo evento #{$nome_evento} cadastrado", "Não foi possível cadastrar novo evento #{$nome_evento}");
    }

    public function atualiza()
    {
        global $transact;
        global $conexao;

        $this->validaForm();

        $stmt = $this->updateStatement("K_AGENDA",
            array(
                "HANDLE" =>  $this->handle,
                "DATA" => $this->data_inicial,
                "DATAFINAL" => $this->data_final,
                "HORA" => $this->hora_inicial,
                "TITULO" => $this->titulo,
                "ATIVO" => $this->evento_ativo,
                "REGIAO" => validaVazio($this->cod_regiao),
                "CONTEUDO" => $this->conteudo,
                "FILIAL" => validaVazio($this->cod_escola),
                "TURMA" => validaVazio($this->cod_turma),
                "PESSOA" => validaVazio($this->cod_pessoa),
                "USUARIO" => validaVazio($this->responsavel),
                "HORAFINAL" => $this->hora_final,
            ));

        $transact->retornoPadrao($stmt, "O evento foi atualizado #{$this->handle}", "Não foi possível atualizar o evento #{$this->handle}");
    }

    static function getResponsavel(){
        global $conexao;

        $sql = "select * from K_PD_USUARIOS";
        $stmt = $conexao->prepare($sql);

        $stmt->execute();
        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        $arr = array();
        $arr["handle"][] = "";
        $arr["nome"][] = "";
        foreach ($f as $r) {
            $arr["handle"][] = $r->HANDLE;
            $arr["nome"][] = $r->NOME;
        }
        return $arr;
    }

    // retorna nomes dos eventos
    public static function getNomeEvento($evento = self::DIA_LETIVO, $lista_completa = false){
        $arr = array("", "Dia letivo", "Férias", "Feriado", "Recesso", "Atividades pedagógicas",
            "Avaliação", "Excursão", "Bimestre", "Trimestre", "Periodo letivo(anual)", "Periodo letivo(semestral)");

        if($lista_completa){
            return $arr;
        }
        return $arr[$evento];
    }

    public static function getTipoAssunto(){
        $arr = array("", "Ligar", "Email", "Almoço", "Reunião", "Tarefa", "Visita");
        return $arr;
    }

    static function getCRMStatus(){

        global $conexao;

        $sql = "select * from K_CRM_ETAPAS";
        $stmt = $conexao->prepare($sql);

        $stmt->execute();
        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        $arr = array();
        foreach ($f as $r) {
            $arr["handle"][] = $r->HANDLE;
            $arr["nome"][] = $r->NOME;
        }
        return $arr;
    }

    static function getRegiao(){

        global $conexao;

        $sql = "select * from K_REGIAO";
        $stmt = $conexao->prepare($sql);

        $stmt->execute();
        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        $arr = array();
        $arr["handle"][] = "";
        $arr["nome"][] = "";
        foreach ($f as $r) {
            $arr["handle"][] = $r->HANDLE;
            $arr["nome"][] = $r->NOME;
        }
        return $arr;
    }

    static function getFilial(){

        global $conexao;

        $sql = "SELECT * FROM K_FN_FILIAL";
        $stmt = $conexao->prepare($sql);

        $stmt->execute();
        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        $arr = array();
        $arr["handle"][] = "";
        $arr["nome"][] = "";
        foreach ($f as $r) {
            $arr["handle"][] = $r->HANDLE;
            $arr["nome"][] = $r->NOME;
        }
        return $arr;
    }

    public static function getNomeCiclo($ciclo_etapa = 0, $lista_completa = false)
    {
        $array_nome_ciclo = array(
            '',
        );

        if ($lista_completa) {
            return $array_nome_ciclo;
        }

        return $array_nome_ciclo[$ciclo_etapa];
    }
}