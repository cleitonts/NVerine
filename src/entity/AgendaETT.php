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
        validaCampo($this->data_inicial, "Data inicial");
        validaCampo($this->data_final, "Data final");
        validaCampo($this->titulo, "Titulo do evento");
        validaCampo($this->evento_ativo, "Evento ativo");
        validaCampo($this->tipo_evento, "Tipo evento");
    }

    public function cadastra()
    {
        global $conexao;

        $this->validaForm();
        $filial = __FILIAL__;

        if(__EDUCACIONAL__){
            $filial = $this->cod_escola;
        }

        $this->handle = newHandle('K_AGENDA', $conexao);

        $stmt = $this->insertStatement("K_AGENDA",
            array(
                "HANDLE" =>  $this->handle,
                "FILIAL" => validaVazio($filial),
                "DATA" => $this->data_inicial,
                "DATAFINAL" => $this->data_final,
                "HORA" => $this->hora_inicial,
                "TITULO" => $this->titulo,
                "ATIVO" => $this->evento_ativo,
                "REGIAO" => validaVazio($this->cod_regiao),
                "CONTEUDO" => $this->conteudo,
                "TURMA" => validaVazio($this->cod_turma),
                "PESSOA" => validaVazio($this->cod_pessoa),
                "USUARIO" => validaVazio($this->responsavel),
                "HORAFINAL" => $this->hora_final,
                "TIPO" => $this->tipo_evento,
            ));

        $nome_evento = AgendaETT::getNomeEvento($this->tipo_evento);
        retornoPadrao($stmt, "Novo evento #{$nome_evento} cadastrado", "Não foi possível cadastrar novo evento #{$nome_evento}");
    }

    public function atualiza()
    {
        $this->validaForm();

        $filial = __FILIAL__;

        if(__EDUCACIONAL__){
            $filial = $this->cod_escola;
        }

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
                "FILIAL" => validaVazio($filial),
                "TURMA" => validaVazio($this->cod_turma),
                "PESSOA" => validaVazio($this->cod_pessoa),
                "USUARIO" => validaVazio($this->responsavel),
                "HORAFINAL" => $this->hora_final,
                "TIPO" => $this->tipo_evento,
            ));

        retornoPadrao($stmt, "O evento foi atualizado #{$this->handle}", "Não foi possível atualizar o evento #{$this->handle}");
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