<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 02/05/2019
 * Time: 11:16
 */

namespace src\entity;

use src\services\Transact\ExtPDO as PDO;

class PessoaEnderecoGUI extends ObjectGUI implements InterfaceGUI
{
    /**
     * InterfaceGUI constructor.
     * @param null $handle
     * entre outras coisas no momento da inicialização,
     * monta o header com nomes de colunas para os relatorios
     */
    public function __construct($handle = null)
    {

    }

    /**
     * @param $linha
     * @param $coluna
     * @return mixed
     * valor dos campos para exibir nos relatorios
     */
    public function getCampo($linha, $coluna)
    {
        // TODO: Implement getCampo() method.
    }

    public function fetch()
    {
        global $conexao;

        $sql1 = "	SELECT P.*,
							E.NOME NOMEESTADO, E.SIGLA SIGLAESTADO, E.CODIGOIBGE CODESTADO,
							M.CODIGOIBGE CODCIDADE
							FROM K_FN_PESSOAENDERECO P
							LEFT JOIN ESTADOS E ON P.ESTADO = E.HANDLE
							LEFT JOIN MUNICIPIOS M ON (P.CIDADE = M.NOME AND P.ESTADO = M.ESTADO)
							WHERE P.PESSOA = :codigo
							ORDER BY P.ORDEM DESC";

        $stmt = $conexao->prepare($sql1);
        $stmt->bindValue(":codigo", $this->pesquisa["pesq_pessoa"]);
        $stmt->execute();
        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        // novos endereços
        if (!empty($f)) {
            foreach ($f as $r) {
                $item = new PessoaEnderecoETT();
                $item->handle = $r->HANDLE;
                $item->estado = formataCase($r->NOMEESTADO, true);
                $item->cod_estado = $r->ESTADO;
                $item->cod_estado_ibge = $r->CODESTADO;
                $item->sigla_estado = $r->SIGLAESTADO;
                $item->cidade = formataCase($r->CIDADE, true);
                $item->cod_cidade = $r->CODCIDADE;

                /* nos campos string inseridos pelo usuário (endereço, bairro, complemento)
                 * rodar limpaString() para evitar caracteres inválidos na nota fiscal!
                 */
                $item->logradouro = limpaString($r->LOGRADOURO);
                $item->bairro = limpaString($r->BAIRRO);
                $item->complemento = limpaString($r->COMPLEMENTO);
                $item->numero = limpaString($r->NUMERO);
                $item->cep = $r->CEP;
                $item->ordem = $r->ORDEM;
                $item->tipo = $r->TIPO;

                // tratamento do endereço no exterior
                if ($item->sigla_estado == "EX") {
                    $partes = explode("**", $item->cidade);
                    $item->pais = trim($partes[0]);
                    $item->cod_pais_bacen = trim($partes[1]);
                    $item->cidade = "EXTERIOR";
                    $item->cod_cidade = "9999999";
                    $item->cep = "";
                }

                $this->itens[] = $item;
            }
        }
    }
}