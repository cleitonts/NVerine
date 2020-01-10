<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 30/04/2019
 * Time: 08:35
 */

namespace src\entity;

use src\services\Transact\ExtPDO as PDO;

/**
 * Class PessoaETT
 * @package src\entity
 * essa classe pode ser uma abstra��o de:
 * Aluno
 */
class PessoaETT extends ObjectETT
{
    // propriedades
    public $tipo;                // F/J
    public $nome;
    public $nome_fantasia;
    public $cpf_cnpj;
    public $rg;                    // ou inscri��o PJ
    public $cnae;                // PJ apenas
    public $observacoes;        // �rea de texto
    public $segmento;            // segmento de neg�cio
    public $vendedor;            // usu�rio do sistema - amarra��o para vendas
    public $conta_pagamento;    // chave estrangeira - cadastro de contas da empresa [INUTILIZADO]
    public $tabela_preco;        // �ndice num�rico da tabela amarrada ao cliente
    public $lista_preco;        // �ndice num�rico da lista de pre�os amarrada ao cliente
    public $cod_area;
    public $area;
    public $data_nascimento;

    // booleanos (S/N)
    public $ativo;
    public $cliente;
    public $fornecedor;
    public $funcionario;
    public $empresa;
    public $transportador;
    public $agrupa_boleto;
    public $contribuinte_icms;    // afeta o getIndIEDest() -- sobrescreve regra anterior
    public $aluno;
    public $professor;

    // configura financeiro na venda. chave __PESSOA_CAMPOS_EXTRA__
    public $forma_pagamento;
    public $condicao_pagamento;

    // subclasses
    public $enderecos = array();            // array de endere�os
    public $contatos = array();            // array de contatos
    public $credito = array();
    public $vinculos = array();

    // gui
    public $filial;
    public $cod_filial;
    public $cod_segmento;
    public $cod_vendedor;
    public $cod_forma_pagamento;
    public $cod_condicao_pagamento;
    public $foto_relatorio;

    // objetos antigos para compatibilidade reversa. eventualmente remover
    public $endereco;
    public $email;
    public $telefone;
    public $contato;
    public $area_contato;

    // flags para o cadastro
    public $pre_cadastro;

    // ---------------------------------------------------------------------------------------------------
    // m�todos p�blicos
    public function __construct()
    {
        $this->credito = new \stdClass();

//        $this->credito = new AnaliseCredito();
        $this->pre_cadastro = false;
        $this->descontos = array();

        // aliases por compatibilidade
        $this->cod_condicao_pagamento = &$this->condicao_pagamento;
        $this->cod_forma_pagamento = &$this->forma_pagamento;
    }

    public function cadastra()
    {
        global $conexao;

        // valida coisas
        $this->checaDuplicidade();
        // $this->validaCpfCnpj();

        // gera handle
        $this->handle = newHandle("K_FN_PESSOA", $conexao);

        // insere
        $stmt = $this->insertStatement("K_FN_PESSOA",
            array(
                "HANDLE" => $this->handle,
                "FILIAL" => __FILIAL__,
                "NOME" => trim($this->nome),
                "CLIENTE" => left($this->cliente, 1),
                "FORNECEDOR" => left($this->fornecedor, 1),
                "BLOQUEIO" => "N",
                "ATIVO" => "S",
                "TIPO" => "F"
            ));

        retornoPadrao($stmt, "Pessoa cadastrada com sucesso.");

        if (!$this->pre_cadastro) $this->atualiza();
    }

    public function atualiza()
    {
        // valida
        $this->checaDuplicidade();
        $this->validaCpfCnpj();

        // corrige padr�o errado de nomenclatura
        if (empty($this->filial)) $this->filial = $this->cod_filial;

        // trata constraint
        if (empty($this->cod_segmento)) $this->cod_segmento = null;
        if (empty($this->filial)) $this->filial = __FILIAL__;

        // atualiza
        $stmt = $this->updateStatement("K_FN_PESSOA",
            array(
                "HANDLE" => $this->handle,
                "FILIAL" => validaVazio($this->filial),
                "ATIVO" => left($this->ativo, 1),
                "TIPO" => left($this->tipo, 1),
                "CLIENTE" => left($this->cliente, 1),
                "FORNECEDOR" => left($this->fornecedor, 1),
                "FUNCIONARIO" => left($this->funcionario, 1),
                "EMPRESA" => left($this->empresa, 1),
                "TRANSPORTADOR" => left($this->transportador, 1),
                "AGRUPABOLETO" => left($this->agrupa_boleto, 1),
                "ALUNO" => left($this->aluno, 1),
                "PROFESSOR" => left($this->professor, 1),
                "AREA" => validaVazio($this->cod_area),
                "BLOQUEIO" => left($this->credito->bloqueio, 1),
                "RESTRICAO" => left($this->credito->restricao, 1),
                "CONTRIBUINTEICMS" => left($this->contribuinte_icms, 1),
                "NOME" => left(trim($this->nome), 250),
                "NOMEFANTASIA" => left(trim($this->nome_fantasia), 250),
                "SEGMENTO" => validaVazio($this->cod_segmento),
                "VENDEDOR" => validaVazio($this->cod_vendedor),
                "CPFCNPJ" => left(trim($this->cpf_cnpj), 20),
                "RG" => left(trim($this->rg), 20),
                "CNAE" => left(trim($this->cnae), 20),
                "CONTAPAGAMENTO" => validaVazio($this->conta_pagamento),
                "FORMAPAGAMENTO" => validaVazio($this->forma_pagamento),
                "CONDICAOPAGAMENTO" => validaVazio($this->condicao_pagamento),
                "LISTAPRECO" => validaVazio($this->tabela_preco),
                "LISTA" => validaVazio($this->lista_preco),
                "NASCIMENTO" => $this->data_nascimento,
                "OBSERVACOES" => $this->observacoes
            ));

        retornoPadrao($stmt, "Cabe�alho da pessoa atualizada com sucesso.",
            "N�o foi poss�vel atualizar o cabe�alho da pessoa. Por favor, confira se h� campos em branco ou valores de tipo inconsistente.");

    }

    public static function getListaPreco()
    {
        $lista = new ListaPrecoGUI();
        $lista->pesquisa["pesq_global"] = 'N';
        $lista->fetch();

        // cria op��o vazia
        $arr["handle"][] = "";
        $arr["nome"][] = "";
        foreach ($lista->itens as $r) {
            $arr["handle"][] = $r->handle;
            $arr["nome"][] = $r->nome;
        }
        return $arr;
    }

    // r�pida atualiza��o de nome
    public function atualizaNome2($nome)
    {
        $stmt = $this->updateStatement("K_FN_PESSOA", array(
            "HANDLE" => $this->handle,
            "NOME" => left($nome, 250)
        ));

        retornoPadrao($stmt, "Nome foi atualizado para '{$nome}'", "N�o foi poss�vel atualizar o nome da pessoa");
    }

    private function checaDuplicidade()
    {
        global $conexao;

        // compatibilidade da rotina na atualiza��o
        $unico = !empty($this->handle) ? "AND HANDLE <> " . intval($this->handle) . " " : "";

        // duplicidade de nome
        $sql = "SELECT * FROM K_FN_PESSOA WHERE NOME = :nome {$unico} AND " . filtraFilial("FILIAL", "Pessoas", false);
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":nome", $this->nome);
        $stmt->execute();
        $r = $stmt->fetch(PDO::FETCH_OBJ);

        if (!empty($r)) {
            // se n�o existir duplicidade por CPF, precisa bloquear por nome!
            if (__LIBERA_CPF__) {
                mensagem("Duplicidade de nome da pessoa ou aluno ({$r->HANDLE} - {$r->NOME})", MSG_ERRO);
                finaliza();
            } else {
                mensagem("J� existe um cadastro com este mesmo nome na sua filial!", MSG_AVISO);
            }
        }

        // deixa pr�-cadastrar sem cpf/cnpj
        if (empty($this->cpf_cnpj) || __LIBERA_CPF__) return 0;

        // duplicidade de cpf/cnpj
        $sql = "SELECT * FROM K_FN_PESSOA WHERE CPFCNPJ = :cpfcnpj {$unico} AND " . filtraFilial("FILIAL", "Pessoas", false);
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(":cpfcnpj", $this->cpf_cnpj);
        $stmt->execute();
        $r = $stmt->fetch(PDO::FETCH_OBJ);

        if (!empty($r)) {
            mensagem("Duplicidade de CPF ou CNPJ ({$r->HANDLE} - {$r->NOME})", MSG_ERRO);
            finaliza();
        }
    }

    private function validaCpfCnpj()
    {
        global $permissoes;

        // m�dulo educacional sobrescreve valida��o de cpf
        if ($this->aluno == "S" || __LIBERA_CPF__) {
            mensagem("Override de valida��o de CPF/CNPJ", MSG_AVISO);
            return;
        }

        // separa c�digo de d�gito verificador
        $partes = explode("-", $this->cpf_cnpj);

        // remove pontos, tra�os, barras...
        $numeros = str_replace(".", "", $partes[0]);
        $numeros = str_replace("/", "", $numeros);

        $digito = array();

        // � cpf ou cnpj?
        if (strlen($numeros) == 9) {
            // bloqueia sequ�ncias que validam
            if ($numeros == "111111111" || $numeros == "222222222" || $numeros == "333333333"
                || $numeros == "444444444" || $numeros == "555555555" || $numeros == "666666666"
                || $numeros == "777777777" || $numeros == "888888888" || $numeros == "999999999"
                || $numeros == "000000000inativo") {
                mensagem("Voc� precisa inserir um CPF v�lido.", MSG_ERRO);
                finaliza();
            }

            // calcula d�gito cpf
            for ($i = 0; $i < 2; $i++) {
                $mult = 10 + $i;
                $soma = 0;

                for ($j = 0; $j < 9; $j++) {
                    $soma += $mult * intval(substr($numeros, $j, 1));
                    $mult--;
                }

                if ($i) {
                    $soma += $digito[0] * 2;
                }

                $valint = intval($soma / 11) * 11;
                $res = $soma - $valint;

                if ($res <= 1) {
                    $digito[$i] = 0;
                } else {
                    $digito[$i] = 11 - $res;
                }
            }

            // testa valores
            $dig = $digito[0] . $digito[1];

            mensagem("Verificador CPF: " . $dig . " ?= " . $partes[1], MSG_DEBUG);

            if ($dig != $partes[1]) {
                mensagem("D�gito verificador de CPF incorreto!", MSG_ERRO);
                finaliza();
            }
        }
        elseif (strlen($numeros) == 12) {
            // calcula d�gito cnpj
            for ($i = 0; $i < 2; $i++) {
                $mult = 5 + $i;
                $soma = 0;

                for ($j = 0; $j < 12; $j++) {
                    $soma += $mult * intval(substr($numeros, $j, 1));
                    $mult--;
                    if ($mult == 1) $mult = 9;
                }

                if ($i) {
                    $soma += $digito[0] * 2;
                }

                $valint = intval($soma / 11) * 11;
                $res = $soma - $valint;

                if ($res <= 1) {
                    $digito[$i] = 0;
                } else {
                    $digito[$i] = 11 - $res;
                }
            }

            // testa valores
            $dig = $digito[0] . $digito[1];

            mensagem("Verificador CNPJ: " . $dig . " ?= " . $partes[1], MSG_DEBUG);

            if ($dig != $partes[1]) {
                mensagem("D�gito verificador de CNPJ incorreto!", MSG_ERRO);
                finaliza();
            }
        }
        else {
            mensagem($numeros, MSG_DEBUG);
            mensagem("Por favor, preencha corretamente o campo CPF/CNPJ", MSG_ERRO);
            finaliza();
        }
    }

    /* ================================================
     * rotinas para formata��o de dados para exporta��o
     * ================================================
     */
    // retorna nome do campo para CPF ou CNPJ (N�O � o valor!)
    public function getDocumentoBase()
    {
        if ($this->endereco->sigla_estado == "EX")
            return "idEstrangeiro";
        elseif ($this->tipo == "F")
            return "CPF";
        else
            return "CNPJ";
    }

    // retorna o valor de CPF/CNPJ com formata��o de zeros
    public function getCpfCnpj()
    {
        $num = apenasNumeros($this->cpf_cnpj);

        if ($this->endereco->sigla_estado == "EX")
            return "";
        elseif ($this->tipo == "F")
            return insereZeros($num, 11); // CPF
        else
            return insereZeros($num, 14); // CNPJ
    }

    // retorna o valor da inscri��o estadual se cliente for CNPJ (compartilha o campo RG)
    public function getIE()
    {
        return $this->tipo == "J" ? $this->rg : null;
    }

    // retorna o indicador de isen��o de inscri��o estadual
    public function getIndIEDest()
    {
        $ie = $this->getIE();

        if (empty($ie))
            return 9; // n�o contribuinte
        elseif ($ie == "ISENTO")
            return 2;
        else
            return 1;
    }

    // insere m�scara de CPF/CNPJ para compatibilidade com filtros de pesquisa
    public static function maskify($cpf_cnpj)
    {
        // tenta descobrir se � um CPF ou CNPJ pelo tamanho da string
        $cpf_cnpj = apenasNumeros($cpf_cnpj);
        $tam = strlen($cpf_cnpj);

        if ($tam == 11) {
            $mask =
                substr($cpf_cnpj, 0, 3) . "." .
                substr($cpf_cnpj, 3, 3) . "." .
                substr($cpf_cnpj, 6, 3) . "-" .
                substr($cpf_cnpj, 9, 2);
        } elseif ($tam == 14) {
            $mask =
                substr($cpf_cnpj, 0, 2) . "." .
                substr($cpf_cnpj, 2, 3) . "." .
                substr($cpf_cnpj, 5, 3) . "/" .
                substr($cpf_cnpj, 8, 4) . "-" .
                substr($cpf_cnpj, 12, 2);
        } else {
            // sistema n�o sabe o que � isso; n�o alterar
            $mask = $cpf_cnpj;
        }

        return $mask;
    }

    // descri��o para contratos e outros documentos legais
    public function geraDescricao($verbose = true)
    {
        // nome
        $str = "<span class='alta'>{$this->nome}</span>";

        if ($verbose) {
            // cpf/cnpj
            if ($this->tipo == "F") $str .= ", pessoa f�sica de direito privado inscrita no CPF ";
            if ($this->tipo == "J") $str .= ", pessoa jur�dica de direito privado inscrita no CNPJ ";
            $str .= "sob o n�; {$this->cpf_cnpj}";

            // identidade
            if (!empty($this->rg) && $this->tipo == "F") $str .= ", doc. de identidade {$this->rg}";
            if (!empty($this->rg) && $this->tipo == "J") $str .= ", Inscri��o Estadual sob o n� {$this->rg}";

            // endere�o completo
            $complemento = "{$this->endereco->logradouro} {$this->endereco->complemento} {$this->endereco->numero} {$this->endereco->bairro}";
            $complemento = trim($complemento);
            if (!empty($complemento)) $complemento .= ", ";

            $str .= ", com sede em {$complemento}{$this->endereco->cidade}-{$this->endereco->sigla_estado}";

            if (!empty($this->endereco->cep)) $str .= ", CEP {$this->endereco->cep}";

            // profiss�o
            if (!empty($this->profissao)) $str .= ", profiss�o {$this->profissao}";
        }

        // data de nascimento
        if (!empty($this->data_nascimento)) {
            $data = converteDataSql($this->data_nascimento);
            $data = str_replace("-", "/", $data);
            $str .= ", nascido em {$data}";
        }

        // telefone de contato
        if (!empty($this->telefone)) {
            $str .= ", telefone {$this->telefone}";
        }

        return $str;
    }
}