<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 04/06/2019
 * Time: 14:35
 */

namespace src\entity;

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

        // monta cláusula de busca por pessoa
        if (!empty($this->pessoa)) {
            $where = "WHERE P.HANDLE = {$this->pessoa}";
        } // se não é por pessoa, busca só pode ser por usuário
        else {
            // se não possuir usuário definido, puxar do usuário logado
            if (empty($this->handle)) {
                $this->handle = $_SESSION["ID"];
            }

            $where = "WHERE U.HANDLE = {$this->handle}";
        }

        if(!empty($this->pesquisa["pesq_vendedor"])){
            $where = "WHERE U.NIVEL = 1";
        }

        if (!empty($this->pesquisa["pesq_supervisor"])) {
            $where = "WHERE U.NIVEL IN (2, 3)";
        }
        // puxa dados de usuário
        $sql = "SELECT U.*,
				G.NOME AS NOMEGRUPO, G.PAGINAINICIAL,
				P.NOME AS NOMECLIENTE
				FROM K_PD_USUARIOS U
				LEFT JOIN K_FN_GRUPOUSUARIO G ON U.GRUPO = G.HANDLE
				LEFT JOIN K_FN_PESSOA P ON U.CLIENTE = P.HANDLE
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
            $item->cpf = $r->CNPJCPF;
            $item->grupo = formataCase($r->NOMEGRUPO, true);
            $item->cod_grupo = $r->GRUPO;
            $item->cod_regiao = $r->REGIAO;
            $item->avatar = $r->IMAGEM;
            $item->pagina_inicial = $r->PAGINAINICIAL;
            $item->cliente = formataCase($r->NOMECLIENTE, true);
            $item->cod_cliente = $r->CLIENTE;
            $item->dia_vencimento = $r->VENCIMENTO;
            $item->nivel = $r->NIVEL;

            // imagem fallback
            if (empty($item->avatar)) $item->avatar = "ui2/img/default-user.png";

            // monta string de último acesso
            /*
            $dias = diasAtraso($_SESSION["DATAULTIMOLOGIN"]);

            $item->ultimo_acesso = "última visita: ";
            if($dias < 0)
                $item->ultimo_acesso .= "viajante do tempo";
            elseif($dias == 0)
                $item->ultimo_acesso .= "hoje";
            elseif($dias == 1)
                $item->ultimo_acesso .= "ontem";
            else
                $item->ultimo_acesso .= $dias." dias atrás";
            */

            // nome parcial
            $partes = explode(" ", $item->nome, 2);
            $item->primeiro_nome = $partes[0];
            $this->itens[] = $item;
        }
    }

    public static function getVendedor(){
        $vendedor = new UsuarioGUI();
        $vendedor->pesquisa["pesq_vendedor"] = 'S';
        $vendedor->fetch();

        $arr = array();
        foreach ($vendedor->itens as $r){
            $arr["handle"][] = $r->handle;
            $arr["nome"][] = $r->nome;
        }
        return $arr;
    }

    public static function getSupervisor(){
        $supervisor = new UsuarioGUI();
        $supervisor->pesquisa["pesq_supervisor"] = 'S';
        $supervisor->fetch();

        $arr = array();
        foreach ($supervisor->itens as $r){
            $arr["handle"][] = $r->handle;
            $arr["nome"][] = $r->nome;
        }
        return $arr;
    }
}