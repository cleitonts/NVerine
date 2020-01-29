<?php
$menu = array(
    "Cadastros" => array(
        "Pessoa" => "?pagina=pessoa",
        "Produto" => "?pagina=produto",
        "Família de produto" => "?pagina=cadastro_familia",
        "Lista de preço" => "?pagina=cadastro_lista_preco",
        "Empresa" => "?pagina=cadastro&tn=Empresa&tabela=".encrypt("K_FN_FILIAL"),
        "Forma de pagamento" => "?pagina=cadastro&tn=Forma de pagamento&tabela=".encrypt("FN_FORMASPAGAMENTO"),
        "Condição de pagamento" => "?pagina=cadastro_condicao_pagto",
        "Boleto bancário" => "?pagina=config_boleto",
        "Segmento de negócio" => "?pagina=cadastro&tn=Segmento&tabela=".encrypt("K_CRM_SEGMENTOS"),
        "Área" => "?pagina=cadastro&tn=Área&tabela=".encrypt("K_FN_AREA"),
        "Unidade" => "?pagina=cadastro&tn=Unidade&tabela=".encrypt("CM_UNIDADESMEDIDA"),
        "Prefixo" => "?pagina=cadastro&tn=Prefixo&tabela=".encrypt("K_PREFIXO"),
        "Níveis" => "?pagina=cadastro&tn=Níveis+usuarios&tabela=".encrypt("K_USUARIOSNIVEIS"),
        "Modelo" => "?pagina=cadastro&tn=Modelo&tabela=".encrypt("K_MODELO"),
        "Fabricante" => "?pagina=cadastro&tn=Fabricante&tabela=".encrypt("K_FABRICANTE"),
        "Termos" => "?pagina=cadastro&tn=Termos&tabela=".encrypt("K_DICIONARIO"),
    ),

    "Fiscal" => array(
        "Importar certificado" => "?pagina=fiscal_certificado",
        "Tipo de operação" => "?pagina=cadastro_tipo_operacao",
        "Histórico" => "?pagina=cadastro&tn=Históricos&tabela=".encrypt("K_HISTORICO"),
        "Alíquota" => "?pagina=cadastro&tn=Alíquotas&tabela=".encrypt("K_FN_TARIFAS"),
        "CFOP" => "?pagina=cadastro&tn=CFOP&tabela=".encrypt("K_FN_CFOP"),
        "CST - Origem" => "?pagina=cadastro&tn=CST - Origem&tabela=".encrypt("K_FN_CST_ORIGEM"),
        "CST - Tributação" => "?pagina=cadastro&tn=CST - Tributação&tabela=".encrypt("K_FN_CST_TRIBUTACAO"),
        "NCM" => "?pagina=cadastro&tn=NCM&tabela=".encrypt("TR_TIPIS")
    ),

    "Educacional" => array(
        //"Dashboard" => "?pagina=educacional_dashboard",
        "Aluno" => "?pagina=educacional_aluno&pesq_pagina=A",
        "Ficha médica" => "?pagina=educacional_ficha_medica&pesq_pagina=A",
        "Turma" => "?pagina=educacionalturma",
        "Série" => "?pagina=educacionalserie",
        "Professor" => "?pagina=educacional_professor",
        "Diário de classe" => "?pagina=educacionaldiario",
        "Avaliações" => "?pagina=educacionalavaliacao",
        "Disciplina" => "?pagina=cadastro&tn=Disciplina&tabela=".encrypt("K_DISCIPLINA"),
        "Agenda" => "?pagina=agenda",
        //"Ocorrências" => "?pagina=educacional_ocorrencia",
        //"Tipo de ocorrência" => "?pagina=cadastro&tn=Tipo de ocorrência&tabela=".encrypt("K_TIPOOCORRENCIA"),
        "Tipo de situação" => "?pagina=cadastro&tn=Tipo de situação&tabela=".encrypt("K_ALUNOSITUACAO"),
        "Tipo de vínculo" => "?pagina=cadastro&tn=Tipo de vínculo&tabela=".encrypt("K_FN_TIPOVINCULO"),
    ),

    "Compras" => array(
        "Notas de compra" => "?pagina=faturamento_notas&pesq_tipo=E&pesq_data_inicial=".ontem()."&pesq_data_final=".date('d-m-Y'),
        "Entrada de estoque" => "?pagina=faturamento_expedicao&pesq_tipo=E",
        "Devolução" => "?pagina=faturamento_devolucao&pesq_tipo=E"
    ),

    "Vendas|Faturamento" => array(
        "Notas de venda" => "?pagina=faturamento_notas&pesq_tipo=S&pesq_data_inicial=".ontem()."&pesq_data_final=".date('d-m-Y'),
        "Caixa" => "?pagina=faturamento_duplicatas",
        "Expedição" => "?pagina=faturamento_expedicao&pesq_tipo=S",
        "Devolução" => "?pagina=faturamento_devolucao&pesq_tipo=S",
        "Loja virtual" => "?pagina=config_loja",
        "Contratos" => "?pagina=contrato"
    ),
//
//    "Financeiro|Contábil" => array(
//        "Contas a pagar" => "?pagina=contabil_titulos&pesq_natureza=1",
//        "Contas a receber" => "?pagina=contabil_titulos&pesq_natureza=2",
//        "Plano de contas" => "?pagina=contabil",
//        "Movimento bancário" => "?pagina=contabil_movimento",
//        "Planejamento financeiro" => "?pagina=contabil_planejamento",
//        "Controle de cheque" => "?pagina=contabil_cheque",
//        "Tipo de documento" => "?pagina=cadastro&tn=Tipo de documento&tabela=".encrypt("K_TIPODOCUMENTO"),
//        "Centro de custo" => "?pagina=cadastro&tn=Centro de custo&tabela=".encrypt("K_FN_CENTROCUSTO")
//    ),
//
//    "Estoque" => array(
//        "Movimento de estoque" => "?pagina=estoque",
//        "Saldo de produtos" => "?pagina=estoque_produto",
//        "Inventário" => "?pagina=estoque_inventario",
//        "Almoxarifado" => "?pagina=cadastro&tn=Almoxarifado&tabela=".encrypt("K_FN_ALMOXARIFADO"),
//        "Endereço" => "?pagina=cadastro&tn=Endereço&tabela=".encrypt("K_FN_ENDERECO")
//    ),

    "Suporte" => array(
        "Chamados" => "?pagina=suporte_chamados",
        "Dashboard" => "?pagina=suporte_dashboard",
        "Abrir chamado" => "?pagina=suporte_novo",
        "Kanban" => "?pagina=suportekanban",
        "Git" => "?pagina=suportegit",
        "Diag" => "?pagina=suportediag",
        // "Clientes e sistemas" => "?pagina=suporte_clientes",
        // "Banco de horas" => "?pagina=rh"
    ),

//    "CRM" => array(
//        "Negociação" => "?pagina=negociacao",
//        "Projeto" => "?pagina=cadastro&tn=Projeto&tabela=".encrypt("K_CRM_PROJETOS"),
//        "Fonte" => "?pagina=cadastro&tn=Fonte&tabela=".encrypt("K_CRM_FONTES"),
//        // "Campanha" => "?pagina=cadastro&tn=Campanha&tabela=".encrypt("K_CRM_CAMPANHAS"),
//        // "Motivo de perda" => "?pagina=cadastro&tn=Motivo de perda&tabela=".encrypt("K_CRM_MOTIVOSPERDA"),
//        "Status" => "?pagina=cadastro&tn=Status&tabela=".encrypt("K_CRM_ETAPAS"),
//        "Processo" => "?pagina=cadastro&tn=Processo&tabela=".encrypt("K_CRM_PROCESSOS"),
//        "Tarefa" => "?pagina=cadastro&tn=Tarefa&tabela=".encrypt("K_CRM_PROCESSOSTAREFAS")
//    ),
//    "Produção" => array(
//        "Fila" => "?pagina=producao_itens",
//        "Etapas" => "?pagina=cadastro&tn=Etapas&tabela=".encrypt("K_PRODUCAOETAPAS"),
//        "Terminal" => "?pagina=cadastro&tn=Terminal&tabela=".encrypt("K_PRODUCAOTERMINAL")
//    ),
//    "Requisição" => array(
//        "Requisição de estoque" => "?pagina=requisicao&novo=1&u=".urlencode(getUrlRetorno())
//    )
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
        $modulo = $termos[0];

        $header = true;
        $footer = false;

        if (!empty($itens)) {
            foreach ($itens as $titulo => $link) {
                if ($permissoes->libera($modulo, $titulo)) {
                    if($header){
                        $header = false;
                        $footer = true;
                        echo "<div class='menu-body'><h5 class='menu-header'>{$modulo}</h5><ul class='menu-list'>";
                    }
                    echo "<li class='menu-list-item'><a onclick='Tools.redirect(\"{$link}\", false, false)' class='menu-link'>{$titulo}</a></li>";

                    // guarda um registro de todos os itens gerados para pesquisa
                    $reg = array(
                        "titulo" => strip_tags($titulo),
                        "icone" => "",
                        "link" => $link,
                        "modulo" => $modulo);
                    array_push($_SESSION["menu_itens"], $reg);
                }
            }
        }

        if($footer){
            echo "</ul></div>";
        }
    }
}