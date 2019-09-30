<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 05/06/2019
 * Time: 14:05
 */

namespace src\entity;

use ExtPDO as PDO;

class FilialGUI extends ObjectGUI
{
    // por enquanto, puxa apenas uma filial por vez.
    public $filial;

    // -------------------------------------------------------------------
    // construtor
    public function __construct($handle = null)
    {
        $this->header = array("Não implementado");
    }

    // -------------------------------------------------------------------
    // métodos públicos
    public function getCampo($linha, $coluna)
    {
        return campo("Não implementado");
    }

    public function fetch()
    {
        global $conexao;

        if (!empty($this->filial)) {
            $where = "WHERE F.HANDLE = :filial";
        }
        $sql = "SELECT F.*,
				E.NOME NOMEESTADO, E.SIGLA SIGLAESTADO, E.CODIGOIBGE CODESTADO,
				M.CODIGOIBGE AS CODCIDADE, M.NOME NOMECIDADE,
				P.NOME NOMEEMPRESA
				FROM K_FN_FILIAL F
				LEFT JOIN ESTADOS E ON F.ESTADO = E.HANDLE
				LEFT JOIN MUNICIPIOS M ON F.CIDADE = M.HANDLE
				LEFT JOIN K_FN_PESSOA P ON F.EMPRESA = P.HANDLE
				{$where}";
        $stmt = $conexao->prepare($sql);

        if (!empty($this->filial)) $stmt->bindValue(":filial", $this->filial);

        $stmt->execute();
        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        foreach ($f as $r) {
            $item = new FilialETT();
            $item->handle = $r->HANDLE;
            $item->nome = formataCase(limpaString($r->NOME), true); // manter formataCase ou usar strtoupper
            $item->razao_social = strtoupper(limpaString($r->RAZAOSOCIAL));
            $item->empresa = formataCase($r->NOMEEMPRESA, true);
            $item->cod_empresa = $r->EMPRESA;
            $item->cnpj = apenasNumeros($r->CNPJ);
            $item->cnae = apenasNumeros($r->CNAE);
            $item->regime_tributario = $r->CRT;
            $item->inscricao_estadual = $r->INSCRICAOESTADUAL;
            $item->inscricao_municipal = $r->INSCRICAOMUNICIPAL;
            $item->telefone = apenasNumeros($r->TELEFONE);
            $item->logotipo = validaImagem($r->LOGOTIPO);
            $item->sequencia_nota = intval($r->SEQUENCIANOTA);
            $item->informacoes_fisco = $r->TEXTOPADRAONFE;
            $item->timezone = empty($r->TIMEZONE) ? "-03:00" : $r->TIMEZONE;
            $item->csc = $r->CSC;
            $item->id_csc = insereZeros($r->ID_CSC, 6);
            $item->cod_inep = $r->CODIGO;
            $item->logo_contrato = $r->LOGO_CONTRATO;

            $item->endereco->estado = $r->NOMEESTADO;
            $item->endereco->sigla_estado = $r->SIGLAESTADO;
            $item->endereco->cod_estado = $r->ESTADO;
            $item->endereco->cod_estado_ibge = $r->CODESTADO;
            $item->endereco->cidade = $r->NOMECIDADE;
            $item->endereco->cod_cidade = $r->CODCIDADE;


            /* nos campos string inseridos pelo usuário (endereço, bairro, complemento)
             * rodar limpaString() para evitar caracteres inválidos na nota fiscal!
             */
            $item->endereco->logradouro = limpaString($r->ENDERECO);
            $item->endereco->bairro = limpaString($r->BAIRRO);
            $item->endereco->complemento = limpaString($r->COMPLEMENTO);
            $item->endereco->numero = $r->NUMERO;
            $item->endereco->cep = $r->CEP;

            array_push($this->itens, $item);
        }
    }
}