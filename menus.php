<?php
$menu = array(
    "Cadastros" => array(
        "Pessoa" => "?pagina=pessoa",
        "Produto" => "?pagina=produto",
        "Empresa" => "?pagina=cadastro&tn=Empresa&tabela=".encrypt("K_FN_FILIAL"),
        "Forma de pagamento" => "?pagina=cadastro&tn=Forma de pagamento&tabela=".encrypt("FN_FORMASPAGAMENTO"),
        "Segmento de negócio" => "?pagina=cadastro&tn=Segmento&tabela=".encrypt("K_CRM_SEGMENTOS"),
        "Área" => "?pagina=cadastro&tn=Área&tabela=".encrypt("K_FN_AREA"),
        "Unidade" => "?pagina=cadastro&tn=Unidade&tabela=".encrypt("CM_UNIDADESMEDIDA"),
    ),

    "Compras" => array(
        "Notas de compra" => "?pagina=faturamento_notas&pesq_tipo=E",
        "Entrada de estoque" => "?pagina=faturamento_expedicao&pesq_tipo=E",
        "Devolução" => "?pagina=faturamento_devolucao&pesq_tipo=E"
    ),

    "Vendas|Faturamento" => array(
        "Notas de venda" => "?pagina=faturamento_notas&pesq_tipo=S",
        "Caixa" => "?pagina=faturamento_duplicatas",
        "Expedição" => "?pagina=faturamento_expedicao&pesq_tipo=S",
        "Devolução" => "?pagina=faturamento_devolucao&pesq_tipo=S",
        "Loja virtual" => "?pagina=config_loja",
        "Contratos" => "?pagina=contrato"
    ),

    "Estoque" => array(
        "Movimento de estoque" => "?pagina=estoque",
        "Saldo de produtos" => "?pagina=estoque_produto",
        "Inventário" => "?pagina=estoque_inventario",
        "Almoxarifado" => "?pagina=cadastro&tn=Almoxarifado&tabela=".encrypt("K_FN_ALMOXARIFADO"),
        "Endereço" => "?pagina=cadastro&tn=Endereço&tabela=".encrypt("K_FN_ENDERECO")
    ),

    "Suporte" => array(
        "Chamados" => "?pagina=suporte_chamados",
        "Dashboard" => "?pagina=suporte_dashboard",
        "Abrir chamado" => "?pagina=suporte_novo",
        "Kanban" => "?pagina=suportekanban",
        // "Clientes e sistemas" => "?pagina=suporte_clientes",
        // "Banco de horas" => "?pagina=rh"
    )
);

function getMenu()
{
    global $menu;
    global $permissoes;
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
                        echo "<li class='menu-list-item'><a href='{$link}' class='menu-link'>{$titulo}</a></li>";

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