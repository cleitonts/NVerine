<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 04/06/2019
 * Time: 14:35
 */

namespace src\services\UAC;

use src\entity\GaleriaETT;
use src\entity\GaleriaGUI;
use src\entity\ObjectGUI;
use src\services\Transact\ExtPDO as PDO;

class UsuarioGUI extends ObjectGUI
{
    public function getCampo($linha, $coluna)
    {
        // TODO: Implement getCampo() method.
    }

    public function fetch()
    {
        global $conexao;

        // monta cl�usula de busca por pessoa
        if (!empty($this->pessoa)) {
            $where = "WHERE P.HANDLE = {$this->pessoa}";
        }
        elseif(!empty($this->pesquisa["pesq_vendedor"])){
            $where = "WHERE U.NIVEL = 1";
        }
        elseif (!empty($this->pesquisa["pesq_supervisor"])) {
            $where = "WHERE U.NIVEL IN (2, 3)";
        }
        else {
            // se n�o possuir usu�rio definido, puxar do usu�rio logado
            if (empty($this->handle)) {
                $this->handle = $_SESSION["ID"];
            }

            $where = "WHERE U.HANDLE = {$this->handle}";
        }
        // puxa dados de usu�rio
        $sql = "SELECT U.*, F.FILIAL,
				G.NOME AS NOMEGRUPO, G.PAGINAINICIAL,
				P.NOME AS NOMECLIENTE
				FROM K_PD_USUARIOS U
				LEFT JOIN K_FN_GRUPOUSUARIO G ON U.GRUPO = G.HANDLE
				LEFT JOIN K_FN_PESSOA P ON U.CLIENTE = P.HANDLE
				LEFT JOIN K_FN_USUARIOFILIAL F ON F.USUARIO = U.HANDLE
				{$where}";
        $stmt = $conexao->prepare($sql);
        $stmt->execute();

        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        foreach ($f as $r) {
            $item = new UsuarioETT();
            $item->handle = $r->HANDLE;
            $item->nome = formataCase($r->NOME, true);
            $item->login = $r->APELIDO;
            $item->senha = $r->SENHA;
            $item->email = $r->EMAIL;
            $item->terminal = $r->TERMINAL;
            $item->filial = $r->FILIAL;
            $item->cpf = $r->CNPJCPF;
            $item->grupo = formataCase($r->NOMEGRUPO, true);
            $item->cod_grupo = $r->GRUPO;
            $item->cod_regiao = $r->REGIAO;
            $item->pagina_inicial = $r->PAGINAINICIAL;
            $item->cliente = formataCase($r->NOMECLIENTE, true);
            $item->cod_cliente = $r->CLIENTE;
            $item->dia_vencimento = $r->VENCIMENTO;
            $item->nivel = $r->NIVEL;

            // imagem fallback
            $galeria = new GaleriaGUI();
            $galeria->pesquisa["pesq_target"] = GaleriaETT::TARGET_USUARIO;
            $galeria->pesquisa["pesq_referencia"] = $item->handle;
            $galeria->fetch();

            $item->avatar = $galeria->itens[0]->url;
            if (empty($item->avatar)) $item->avatar = "ui2/img/default-user.png";

            // monta string de �ltimo acesso
            /*
            $dias = diasAtraso($_SESSION["DATAULTIMOLOGIN"]);

            $item->ultimo_acesso = "�ltima visita: ";
            if($dias < 0)
                $item->ultimo_acesso .= "viajante do tempo";
            elseif($dias == 0)
                $item->ultimo_acesso .= "hoje";
            elseif($dias == 1)
                $item->ultimo_acesso .= "ontem";
            else
                $item->ultimo_acesso .= $dias." dias atr�s";
            */

            // nome parcial
            $partes = explode(" ", $item->nome, 2);
            $item->primeiro_nome = $partes[0];
            $this->itens[] = $item;
        }
    }
}