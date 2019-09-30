<?php
$menu = array(
    "Cadastros" => array(
        "Pessoa" => "index.php?pagina=cadastro_pessoa",
        "Produto" => "index.php?pagina=cadastro_produto",
        "Família de produto" => "index.php?pagina=cadastro_familia",
        "Lista de preço" => "index.php?pagina=cadastro_lista_preco",
        "Empresa" => "index.php?pagina=cadastro&tn=Empresa&tabela=".encrypt("K_FN_FILIAL"),
        "Forma de pagamento" => "index.php?pagina=cadastro&tn=Forma de pagamento&tabela=".encrypt("FN_FORMASPAGAMENTO"),
        "Condição de pagamento" => "index.php?pagina=cadastro_condicao_pagto",
        "Segmento de negócio" => "index.php?pagina=cadastro&tn=Segmento&tabela=".encrypt("K_CRM_SEGMENTOS"),
        "Área" => "index.php?pagina=cadastro&tn=Área&tabela=".encrypt("K_FN_AREA"),
        "Unidade" => "index.php?pagina=cadastro&tn=Unidade&tabela=".encrypt("CM_UNIDADESMEDIDA"),
    ),

    "Compras" => array(
        "Notas de compra" => "index.php?pagina=faturamento_notas&pesq_tipo=E",
        "Entrada de estoque" => "index.php?pagina=faturamento_expedicao&pesq_tipo=E",
        "Devolução" => "index.php?pagina=faturamento_devolucao&pesq_tipo=E"
    ),

    "Vendas|Faturamento" => array(
        "Notas de venda" => "index.php?pagina=faturamento_notas&pesq_tipo=S",
        "Caixa" => "index.php?pagina=faturamento_duplicatas",
        "Expedição" => "index.php?pagina=faturamento_expedicao&pesq_tipo=S",
        "Devolução" => "index.php?pagina=faturamento_devolucao&pesq_tipo=S",
        "Loja virtual" => "index.php?pagina=config_loja",
        "Contratos" => "index.php?pagina=contrato"
    ),

    "Estoque" => array(
        "Movimento de estoque" => "index.php?pagina=estoque",
        "Saldo de produtos" => "index.php?pagina=estoque_produto",
        "Inventário" => "index.php?pagina=estoque_inventario",
        "Almoxarifado" => "index.php?pagina=cadastro&tn=Almoxarifado&tabela=".encrypt("K_FN_ALMOXARIFADO"),
        "Endereço" => "index.php?pagina=cadastro&tn=Endereço&tabela=".encrypt("K_FN_ENDERECO")
    ),

    "Suporte" => array(
        "Chamados" => "index.php?pagina=suporte_chamados",
        "Dashboard" => "index.php?pagina=suporte_dashboard",
        "Abrir chamado" => "index.php?pagina=suporte_novo",
        "Kanban" => "index.php?pagina=suportekanban",
        // "Clientes e sistemas" => "index.php?pagina=suporte_clientes",
        // "Banco de horas" => "index.php?pagina=rh"
    )
);

function getMenu()
{
    global $menu;
    global $permissoes;
    global $dicionario;
    // montagem do menu em HTML, SESSION
    $_SESSION["menu_itens"] = array();

    foreach ($menu as $modulo => $itens) {

        // trata nomes alternativos
        $termos = explode("|", $modulo);

        if (count($termos) == 1) {
            if (!$permissoes->libera($termos[0])) continue;
        } elseif (count($termos) == 2) {
            if (!$permissoes->libera($termos[0]) && !$permissoes->libera($termos[1])) continue;
        }

        $modulo = $termos[0];

        ?>
        <div class="menu-body">
            <h5 class="menu-header"><?= $modulo ?></h5>
            <ul class="menu-list">
                <?php
                if (!empty($itens)) foreach ($itens as $titulo => $link) {
                    if (!$permissoes->bloqueia($titulo)) {
                        $titulo = $titulo;

                        ?>
                        <li class="menu-list-item"><a href="<?= $link ?>" class="menu-link"><?= $titulo ?></a></li>
                        <?php

                        // guarda um registro de todos os itens gerados para pesquisa
                        $reg = array(
                            "titulo" => strip_tags($titulo),
                            "icone" => "",
                            "link" => $link,
                            "modulo" => $modulo);
                        array_push($_SESSION["menu_itens"], $reg);
                    }
                }
                ?>
            </ul>
        </div>
        <?php
    }
}