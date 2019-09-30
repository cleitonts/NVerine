<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 01/08/2019
 * Time: 08:42
 */

namespace src\entity;

use ExtPDO as PDO;

class SuporteHistoricoGUI extends ObjectGUI
{
    // pesquisa por um único chamado
    public $chamado;

    // construtor
    public function __construct($handle = null) {
        // cabeçalho padrão
        $this->header = array("Usuário", "Data", "Hora", "Status", "Assunto", "#");
    }

    // métodos públicos
    public function getCampo($linha, $coluna) {
        // indexa o item
        $item = $this->itens[$linha];

        // para a coluna, retorna um array com o valor e a classe a aplicar
        return $this->campos($coluna, array(
            campo($item->usuario),
            campo(converteDataSqlOrdenada($item->data)),
            campo($item->hora),
            campo($item->status_chamado, "bg-color bg{$item->cod_status_chamado}"),
            campo($item->assunto_chamado),
            campo($item->chamado, "numerico")
        ));
    }

    public function fetch() {
        global $conexao;
        global $permissoes;
        global $perfil;

        $chamado = new SuporteChamadoETT();

        // monta query de pesquisa
        $where = "WHERE 1 = 1 \n";

        if(isset($this->chamado)) {
            $where .= "AND H.CHAMADO = {$this->chamado} \n";
            $order_by = "H.HANDLE DESC";
        }
        elseif(isset($this->pesquisa["pesq_chamados"])) {
            $where .= "AND H.CHAMADO IN (".$this->pesquisa["pesq_chamados"].") \n";
            $order_by = "H.CHAMADO ASC, H.DATA ASC";
        }
        else {
            // se usuário não é suporte, só pode ver cliente
            if(!$permissoes->libera("Equipe de suporte")) {
                $sql = "SELECT CLIENTE FROM K_PD_USUARIOS WHERE HANDLE = '{$_SESSION["ID"]}'";
                $stmt = $conexao->prepare($sql);
                $stmt->execute();
                $f = $stmt->fetch(PDO::FETCH_OBJ);

                $where .= "AND C.CLIENTE = '{$f->CLIENTE}' \n";
            }

            // inverte a ordem -- mais recentes primeiro
            $order_by = "H.HANDLE DESC";
        }

        // filtra mensagens vazias ou comuns
        if(isset($this->pesquisa["filtra_vazias"])) {
            $where .= " AND H.COMENTARIOS IS NOT NULL AND H.COMENTARIOS NOT LIKE '' AND H.COMENTARIOS NOT LIKE 'OK' \n";
        }

        // puxa dados
        $sql = "SELECT {$this->top}
				H.*, C.ASSUNTO, U.NOME AS NOMEUSUARIO, U.IMAGEM AS AVATAR
				FROM K_CHAMADOHISTORICO H
				LEFT JOIN K_CHAMADOS C ON H.CHAMADO = C.HANDLE
				LEFT JOIN K_PD_USUARIOS U ON H.USUARIO = U.HANDLE
				{$where}
				ORDER BY {$order_by}";
        $stmt = $conexao->prepare($sql);
        $stmt->execute();
        $f = $stmt->fetchAll(PDO::FETCH_OBJ);

        // insere no array
        $i = 0;

        foreach($f as $r) {
            $item = new SuporteHistoricoETT();
            $item->cont = $i;

            $item->handle = $r->HANDLE;
            $item->usuario = formataCase($r->NOMEUSUARIO, true);
            $item->cod_usuario = $r->USUARIO;
            $item->chamado = $r->CHAMADO;
            $item->data = $r->DATA;
            $item->hora = empty($r->HORA) ? "00:00:00" : $r->HORA;
            $item->comentarios = trim($r->COMENTARIOS); // sem trim não consigo tratar com empty()?
            $item->status_chamado = $chamado->getNomeStatus($r->STATUS);
            $item->cod_status_chamado = $r->STATUS;
            $item->revisao = trim($r->REVISAO);
            $item->anexo = empty(trim($r->ANEXO)) ? "" : "<a href='{$r->ANEXO}' target='_blank'>...".right($r->ANEXO, 20)."</a>";
            $item->avatar = validaImagem($r->AVATAR);
            //$item->assunto_chamado = utf8_encode("<a href='?pagina=suporte_chamados&pesq_chamado={$item->chamado}&retorno=".urlencode(getUrlRetorno())."'>".left($r->ASSUNTO, 50)."</a>");

            // comentário reduzido
            //$item->sumario = utf8_encode($item->comentarios);
            //if(strlen($item->sumario) > 300) $item->sumario = left($item->sumario, 300)."...";

            // highlight do nome
            if(!empty($perfil->primeiro_nome)) {
                $item->comentarios = str_ireplace($perfil->primeiro_nome, "<span style='font-size: 13px' class='tag tabc bg-color bg13'>{$perfil->primeiro_nome}</span>", $item->comentarios);
            }

            array_push($this->itens, $item);
            $i++;
        }
    }
}