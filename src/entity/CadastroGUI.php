<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 27/09/2019
 * Time: 12:53
 */

namespace src\entity;

use src\services\Transact\ExtPDO as PDO;

class CadastroGUI extends ObjectGUI {
    // objeto Cadastro
    public $tabela;

    /* instância deve passar o nome da tabela para extrair os campos
     */
    public function __construct($handle = null) {
        $nome_tabela = $handle;
        $this->tabela = new CadastroETT($nome_tabela);

        // salva a tabela em session para maior segurança (passagem para controller)
        $_SESSION["tabela"] = $nome_tabela;

        // definições dos headers
        if(isset($this->tabela->campos)) {
            $this->header = array();

            foreach($this->tabela->campos as $r) {
                $nome = formataCase($r);
                if($nome == "Handle") $nome = "Cod. registro";
                if($nome == "Codigo" && $nome_tabela == "K_FN_FILIAL") $nome = "Nº Inep";

                $this->header[] = $nome;
            }
        }
        else {
            $this->header = array("Não definido");
        }
    }

    public function getCampo($linha, $coluna) {
        // indexa o item
        $item = $this->itens[$linha];

        if(!empty($this->tabela->campos)) {
            // monta a sequência de campos
            $campos = array();

            foreach($this->tabela->campos as $r) {
                $prop = $r;
                $valor = $item->{$prop};

                // formatação dos diferentes campos
                if(is_numeric($valor)) {
                    $campos[] = campo($valor, "numerico");
                }
                elseif($valor == "S" || $valor == "N") {
                    $campos[] = campo(formataLogico($valor));
                }
                elseif($prop == "SENHA") {
                    $campos[] = campo("********");
                }
                else {
                    $campos[] = campo($valor);
                }
            }
            return $this->campos($coluna, $campos);
        }
        else {
            return campo("Não definido");
        }


    }

    public function fetch() {

        global $conexao;
        // busca simples (valores para edição)
        if(isset($this->pesquisa["pesq_num"])) {
            $sql = "SELECT * FROM {$this->tabela->nome_tabela} WHERE HANDLE = :handle";
            $stmt = $conexao->prepare($sql);
            $stmt->bindValue(":handle", $this->pesquisa["pesq_num"]);
            $stmt->execute();
            $f = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // monta query completa
        else {
            $select = "SELECT {$this->top} T.*";
            $from = "\nFROM {$this->tabela->nome_tabela} T";

            foreach($this->tabela->campos as $r) {
                if($r == "PAI") {
                    $select .= ", P.NOME AS PAI";
                    $from .= "\nLEFT JOIN {$this->tabela->nome_tabela} P ON T.PAI = P.HANDLE";
                }
                elseif($r == "ESTADO") {
                    $select .= ", E.NOME AS ESTADO";
                    $from .= "\nLEFT JOIN ESTADOS E ON T.ESTADO = E.HANDLE";
                }
                elseif($r == "USUARIO") {
                    $select .= ", U.NOME AS USUARIO";
                    $from .= "\nLEFT JOIN K_PD_USUARIOS U ON T.USUARIO = U.HANDLE";
                }
                elseif($r == "ALCADA") {
                    $select .= ", A.NOME AS ALCADA";
                    $from .= "\nLEFT JOIN K_PD_ALCADAS A ON T.ALCADA = A.HANDLE";
                }
                elseif($r == "GRUPO") {
                    $select .= ", G.NOME AS GRUPO";
                    $from .= "\nLEFT JOIN K_FN_GRUPOUSUARIO G ON T.GRUPO = G.HANDLE";
                }
                elseif($r == "FILIAL") {
                    $select .= ", F.NOME AS FILIAL";
                    $from .= "\nLEFT JOIN K_FN_FILIAL F ON T.FILIAL = F.HANDLE";
                }
                elseif($r == "K_FILIAL") {
                    $select .= ", F.NOME AS K_FILIAL";
                    $from .= "\nLEFT JOIN K_FN_FILIAL F ON T.K_FILIAL = F.HANDLE";
                }
                elseif($r == "REGIAO") {
                    $select .= ", R.NOME AS REGIAO";
                    $from .= "\nLEFT JOIN K_REGIAO R ON T.REGIAO = R.HANDLE";
                }
                elseif($r == "AREA") {
                    $select .= ", AR.NOME AS AREA";
                    $from .= "\nLEFT JOIN K_FN_AREA AR ON T.AREA = AR.HANDLE";
                }
                elseif($r == "ALMOXARIFADO") {
                    $select .= ", AL.NOME AS ALMOXARIFADO, AL.FILIAL";
                    $from .= "\nLEFT JOIN K_FN_ALMOXARIFADO AL ON T.ALMOXARIFADO = AL.HANDLE";
                }
                elseif($r == "EMPRESA") {
                    $select .= ", EM.NOME AS EMPRESA";
                    $from .= "\nLEFT JOIN K_FN_PESSOA EM ON T.EMPRESA = EM.HANDLE";
                }
                elseif($r == "FORMAPAGAMENTO") {
                    $select .= ", FP.NOME AS FORMAPAGAMENTO";
                    $from .= "\nLEFT JOIN FN_FORMASPAGAMENTO FP ON T.FORMAPAGAMENTO = FP.HANDLE";
                }
                elseif($r == "CONDICAOPAGAMENTO") {
                    $select .= ", CP.DESCRICAO AS CONDICAOPAGAMENTO";
                    $from .= "\nLEFT JOIN CP_CONDICOESPAGAMENTO CP ON T.CONDICAOPAGAMENTO = CP.HANDLE";
                }
                elseif($r == "CLIENTE") {
                    $select .= ", C.NOME AS CLIENTE";
                    $from .= "\nLEFT JOIN K_FN_PESSOA C ON T.CLIENTE = C.HANDLE";
                }
                elseif($r == "CIDADE") {
                    $select .= ", MUN.NOME AS CIDADE";
                    $from .= "\nLEFT JOIN MUNICIPIOS MUN ON T.CIDADE = MUN.HANDLE";
                }
                elseif($r == "PRODUTO") {
                    $select .= ", PROD.NOME AS PRODUTO, PROD.K_KFILIAL";
                    $from .= "\nLEFT JOIN PD_PRODUTOS PROD ON T.PRODUTO = PROD.CODIGO";
                }
                elseif($r == "PESSOA") {
                    $select .= ", C.NOME AS PESSOA";
                    $from .= "\nLEFT JOIN K_FN_PESSOA C ON T.PESSOA = C.HANDLE";
                }
                elseif($r == "CONTAORIGEM") {
                    $select .= ", CB.NOME AS CONTAORIGEM";
                    $from .= "\nLEFT JOIN K_CONTAS CB ON T.CONTAORIGEM = CB.HANDLE";
                }
                elseif($r == "DISCIPLINA") {
                    $select .= ", DS.NOME AS DISCIPLINA";
                    $from .= "\nLEFT JOIN K_DISCIPLINA DS ON T.DISCIPLINA = DS.HANDLE";
                }
                elseif($r == "FABRICANTE") {
                    $select .= ", FAB.NOME AS FABRICANTE";
                    $from .= "\nLEFT JOIN K_FABRICANTE FAB ON T.FABRICANTE = FAB.HANDLE";
                }
            }
            $sql = $select.$from;

            // ordena?
            // if($_REQUEST["ordem"] == "desc")		$sql .= "\nORDER BY T.HANDLE DESC";

            $stmt = $conexao->prepare($sql);
            $stmt->execute();

            $f = $stmt->fetchAll(PDO::FETCH_ASSOC); // usando arrays para referência dinâmica
        }

        // mapeia itens
        $this->itens = array();
        $i = 0;

        foreach($f as $campos) {
            $item = new \stdClass();
            $item->cont = $i;

            foreach($campos as $prop => $valor) {
                $item->{$prop} = $valor;
            }

            $this->itens[] = $item;
            $i++;
        }
    }
}
