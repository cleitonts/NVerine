<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 30/04/2019
 * Time: 10:12
 */

namespace src\entity;


class EducacionalAlunoGUI extends PessoaGUI implements InterfaceGUI
{
    public function __construct($handle = null)
    {
        // constrói PessoaGUI
        parent::__construct();

        // marca uso do objeto Aluno e campos extra no fetch()
        $this->educacional = true;

        // headers customizados
        $this->header = array("Código", "Nome", "Matrícula", "Unidade", "Turma", "Turno",
            "Idade", "Sexo", "Data nascimento", "Data adesão", "Situação",
            "Transtorno", "Tipo sangüíneo", "Profissão", "Escolaridade", "Especialização", "Responsável",
            "Cidade", "Estado", "E-mail", "Telefone", "CPF", "Ativo", "Benefício",
            "Necessidades esp.", "Avaliação esp.", "Foto", "Entregar para");
        $this->header_abrev = $this->header;
    }

    public function setFichaMedica()
    {
        $this->ficha_medica = true;
    }

    public function getCampo($linha, $coluna)
    {
        // indexa o item
        $item = $this->itens[$linha];

        // para a coluna, retorna um array com o valor e a classe a aplicar
        return $this->campos($coluna, array(
            campo($item->handle, "numerico"),
            campo(formataCase($item->nome, true)),
            campo($item->matricula),
            campo($item->filial),
            campo($item->turma),
            campo($item->turno),
            campo($item->idade, "numerico"),
            campo($item->sexo),
            campo(converteDataSqlOrdenada($item->data_nascimento)),
            campo(converteDataSqlOrdenada($item->data_adesao)),
            campo($item->dados_censo["motivoTransf"]),
            campo($item->tipo_transtorno),
            campo($item->tipo_sanguineo),
            campo($item->profissao),
            campo($item->escolaridade),
            campo($item->especializacao),
            campo($item->responsavel),
            campo($item->endereco->cidade),
            campo($item->endereco->estado),
            campo($item->email),
            campo($item->telefone),
            campo($item->cpf_cnpj),
            campo(formataLogico($item->ativo)),
            campo(formataLogico($item->bolsa)),
            campo($item->lista_transtornos),
            campo($item->lista_avaliacao),
            campo($item->foto_relatorio),
            campo($item->dados_censo["autorizaEntrega"])
        ));
    }

    public function fetch()
    {
        // mapeia filtros para PessoaGUI
        if (!empty($this->pesquisa["pesq_aluno"])) {
            $this->pesquisa["pesq_codigo"] = $this->pesquisa["pesq_aluno"];
        }

        // as propriedades ja estarão mapeadas, mas eu ainda precisarei das nao mapeadas
        parent::fetch();

        $i = 0;
        if (!empty($this->dados_educacional)) {
            foreach ($this->dados_educacional as $r) {
                $item = new EducacionalAlunoETT();
                $item->bolsa = $r->BOLSA;
                $item->data_adesao = $r->DATAADESAO;
                $item->sexo = $r->SEXO;
                $item->naturalidade = $r->NATURALIDADE;
                $item->profissao = $r->PROFISSAO;
                $item->etnia = $r->ETNIA;
                $item->escolaridade = $r->ESCOLARIDADE;
                $item->especializacao = $r->ESPECIALIZACAO;
                $item->religiao = $r->RELIGIAO;
                $item->tipo_sanguineo = $r->TIPOSANGUINEO;
                $item->matricula = left(converteDataSqlOrdenada($item->data_adesao), 4) . "/" . insereZeros($item->handle, 6);
                $item->idade = !empty($r->NASCIMENTO) ? intval(abs(diasAtraso($r->NASCIMENTO)) / 365.25) : ""; // isso não será day-accurate relacionado a anos bissextos
                $item->tipo_transtorno = $r->TIPOTRANSTORNO;

                /* buscas extra do educacional
                 * notar que os relatórios do educacional já ficarão super mal-otimizados por conta disso!
                 * pensar em paliativos depois
                 */
                $vinculo = new PessoaVinculoGUI();
                $vinculo->top = "TOP 10";
                $vinculo->pessoa = $item->handle;
                $vinculo->pesquisa["pesq_responsavel"] = 1;
                $vinculo->fetch();
                $vinculo = $vinculo->itens[0];

                $matricula = new EducacionalMatriculaGUI();
                $matricula->top = "TOP 10";
                $matricula->pesquisa["pesq_aluno"] = $item->handle;
                $matricula->pesquisa["pesq_vigente"] = "S";
                $matricula->pesquisa["pesq_ativo"] = 1;
                $matricula->fetch();
                $matricula = $matricula->itens[0];

                $item->responsavel = $vinculo->pai;
                $item->turma = $matricula->turma;
                $item->cod_turma = $matricula->cod_turma;
                $item->turno = $matricula->turno;

                // campos de especificação livre (JSON)
                $item->dados_censo = json_decode($r->DADOSCENSO, true); // array associativo
                if (is_array($item->dados_censo)) { // evita warning se dados estiverem vazios
                    $item->dados_censo = array_map("utf8_decode", $item->dados_censo);
                } else {
                    $item->dados_censo = array(); // just in case
                }

                // especificação livre da ficha médica
                if ($this->ficha_medica) {
                    $json_transtornos = json_decode($r->TRANSTORNOSJSON, true);
                    $item->lista_transtornos = "";

                    if (is_array($json_transtornos)) { // evita warning se dados estiverem vazios
                        foreach ($json_transtornos as $key => $value) {
                            $key = str_replace("_", " ", utf8_decode($key));

                            if ($value == "S") {
                                if (!empty($item->lista_transtornos)) $item->lista_transtornos .= ", ";
                                $item->lista_transtornos .= $key;
                            }
                        }
                    }

                    $json_avaliacao = json_decode($r->AVALIACAO, true);
                    $item->lista_avaliacao = "";

                    if (is_array($json_avaliacao)) { // evita warning se dados estiverem vazios
                        foreach ($json_avaliacao as $key => $value) {
                            $key = str_replace("_", " ", utf8_decode($key));

                            if ($value == "S") {
                                if (!empty($item->lista_avaliacao)) $item->lista_avaliacao .= ", ";
                                $item->lista_avaliacao .= $key;
                            }
                        }
                    }
                }

                $i++;
            }
        }
    }
}