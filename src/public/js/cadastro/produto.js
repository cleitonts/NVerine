//criar variaveis globais
var produto;
var options_grupo;
var options_subgrupo;

$(document).on('trigger_dynamic_tabela', function(e, p1, p2){
    atualizaTabela($(p1).attr("num"));
});

$(document).on('trigger_dynamic_tabela_estruturada', function(e, p1, p2){
    atualizaEstrutura($(p1).attr("num"));
});

$(document).on('trigger_dynamic_fornecedores', function(e, p1, p2){
    $(p1).find("[id^='campo_fornecedor']").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "json.php?pagina=pessoas",
                dataType: "json",
                data: {term: request.term, pesq_nome: request.term, filial: __FILIAL__, pesq_pagina: "F"},
                success: (data) => {response(data)}
            });
        },
        select: function(event, ui) {
            // primeiro salva nas propriedades            
            $("#campo_fornecedor_"+p2).val(ui.item.label);
            $("#label_cod_"+p2).text(ui.item.id);
            $("#campo_cod_fornecedor_"+p2).val(ui.item.id);
            
        }
    });
});


function triggerUpdate() {
    atualizaCustoPE();
    
    $("[id^='campo_perc_tab'], [id^='campo_qtd_tab']").change(function () {
        atualizaTabela($(this).closest("[num]").attr("num"));
    });

    $("[id^='campo_pe_quantidade']").change(function () {
        atualizaEstrutura($(this).closest("[num]").attr("num"));
    });    
}

function pageUpdate(){
    // correções da ficha
    if (1 == 1) {
        // corrige posicionamento
        let h2 = document.createElement("h2");
        h2.className = "col-12";

        $(h2).append(document.createTextNode($("#campo_nome").val()));

        let row = document.createElement("div");
        row.className = "row";

        $(row).prepend(h2);
        $("#card_ficha > .card-body").prepend(row);
        $("#main-content > .row").prepend($("#card_ficha")[0]);
    }

    $("#perc_valor_markup").click(function(){
        const modal = new Modal();
        modal.title = "Calcular porcentagem";
        modal.btnSuccess = "Calcular";
        modal.btnCancel = "Cancelar";
        modal.size = "modal-sm"; // "modal-lg, modal-sm";
        modal.btnSuccessAct = function() {
            $("#modal_perc").modal("hide");
            valorFinal(parseValor($("#campo_perc_markup").val()))
        };
        modal.name = "modal_perc";
        modal.no_footer = false;

        var rendered = modal.render();

        let perc = {
            "type": "TEXT",
            "value": "",
            "name": "perc_markup",
            "description": "% Markup",
            "size": "12",
            "icon": "",
            "property": "",
            "function": "",
            "class": "percent"
        };
        Field.input("text", $(rendered).find(".modal-body"), perc);

        $("#main-content").append(rendered);

        $('#campo_perc_markup').mask("000,00%", {reverse: true});

        $("#modal_perc").modal("show");
    });

    /*ativar função valor_final() a cada change;*/
    $("#perc_valor_markup, #campo_valor_compra, #campo_valor_custos_fixos, #campo_perc_ipi, #campo_valor_markup, #campo_valor_comissao").change(function () {
        valorFinal($(this).closest("[num]").attr("num"));
    });

    $("#campo_valor_custo_pe, #campo_valor_markup_pe").change(function () {
        atualizaCustoPE();
    });
    
    $(".novo_item").click(function () {
        autocompleteProduto();
    });

    //recuperar valores dos campos abaixo
    options_grupo = $("#campo_grupo option");
    options_subgrupo = $("#campo_cod_familia option");

    hideProdutoEstruturado($('#campo_estruturado').val());
    $('#campo_estruturado').change(function(){
        hideProdutoEstruturado(this.value);
    });

    // pesquisa produto
    $("#campo_nome").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "json.php?pagina=produtos",
                dataType: "json",
                data: {term: request.term},
                success: function(data) {response(data);}
            });
        },
        select: function(event, ui) {
            $("#campo_nome").val(ui.item.value);
            $("#campo_codigo").val(ui.item.id);
        }
    });
}

function atualizaEstrutura(num){
    if(num === "templatex"){
        return;
    }
    
    var quantidade = $("#campo_pe_quantidade_"+num).val();
    var unitario = parseValor($("#label_pe_valor_unitario_"+num).text());
    $("#label_pe_valor_total_"+num).text((quantidade * unitario).toFixed(2)).trigger('input');
    atualizaCustoPE();
}

function atualizaCustoPE() {
    let valor_total = 0;
    $("[id^='label_pe_valor_total_']").each(function () {
        let num = $(this).closest("[num]").attr("num");
        if (num !== "templatex" && $("#dynamic_tabela_estruturada #campo_existe_"+num).val() == 1) {
            valor_total += parseValor($(this).text());
        }
    });

    $(`#campo_valor_venda_pe`).val(valor_total.toFixed(2)).trigger('input');
    $(`#campo_valor_compra`).val(valor_total.toFixed(2)).trigger('input').change();
    //$(`#campo_valor_custo`).val(valor_total.toFixed(2)).trigger('input');
}

function getAliquota(obj){
    $.ajax({
        url: "json.php?pagina=aliquota",
        dataType: "json",
        data: {ncm: $(obj).val()},
        success: function(data) {
            $("#campo_perc_ipi").val(data.toFixed(2)).closest(".form-group").addClass("is-filled");
            valorFinal();
        }
    });
}

function autocompleteProduto(){
    // pesquisa produto
    $(`[id^="campo_pe_filho"]`).autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "json.php?pagina=produtos_redux",
                dataType: "json",
                data: {term: request.term, filial: __FILIAL__},
                success: function(data) {response(data);}
            });
        },
        select: function(event, ui) {
            var num = $(this).parents("tr").attr("num");
            
            if (ui.item.id === $("#campo_handle").val()) {
                var popup = new Alert();
                popup.typo = "danger";
                popup.texto = "Selecione um produto diferente do que você esta editando.";
                popup.montaMensagem();                
                return;
            }
            
            
            $(`#campo_pe_cod_filho_${num}`).val(ui.item.id);
            $(`#label_pe_cod_filho_${num}`).text(ui.item.id);
            $(`#campo_pe_filho_${num}`).val(ui.item.label);
            $(`#label_pe_unidade_${num}`).text(ui.item.unidade);
            $(`#campo_pe_quantidade_${num}`).val(1);
            $(`#label_pe_valor_unitario_${num}`).text(ui.item.preco).trigger('input');
            $(`#label_pe_valor_total_${num}`).text(ui.item.preco).trigger('input');
            atualizaEstrutura(num);
        }
    });
}

// esconde as informacoes dos campos de produto estruturado.
function hideProdutoEstruturado(value){
    const campos = $('#dynamic_tabela_estruturada,#wrapper_valor_custo_pe,#wrapper_valor_venda_pe,#wrapper_valor_markup_pe');
    $(campos).hide();
    if(value === 'S'){
        $(campos).show();
    }
}

// traduzir campos com virgulas em number.
function parseValor(valor){
    if(valor.includes(",")) {
        valor = valor.replace(".", "");
    }
    valor = valor.replace(",", ".");
    valor = parseFloat(valor);
    if(isNaN(valor)) valor = 0;

    return valor;
}

function getGrupo(){
    /*
        Recupera os valores dos campos e realiza a separação dos grupos
     */
    var valor_familia = getSelect('familia-2', 'text');
    var valor_grupo = getSelect('grupo', 'text');

    valor_familia = valor_familia.split(")");
    valor_grupo = valor_grupo.split(")");

    /*
        atualiza o select picker
     */
    $("#campo_grupo").html("").selectpicker('refresh');
    $("#campo_cod_familia").html("").selectpicker('refresh');

    const grupo_familia = valor_familia;
    const grupo_grupo = valor_grupo;

    options_grupo.each(function (itens) {
        var valor_grupo_familia = $(this).text().substring(0, 2);
        if (grupo_familia[0] * 1 == valor_grupo_familia * 1) {
            $("#campo_grupo").append('<option label="' + $(this).text() + '" value="' + $(this).val() + '">' + $(this).text() + '</option>').selectpicker('refresh');
        }
    });

   /*
        mudar o grupo conforme o grupo
    */
    options_subgrupo.each(function () {
        var valor_grupo_grupo = $(this).text().split(".");
        valor_grupo_grupo = `${valor_grupo_grupo[0] * 1}.${valor_grupo_grupo[1] * 1}`;
        select_grupo =  getSelect('grupo', 'text').split(")");
        select_grupo = select_grupo[0].split(".");
        select_grupo = `${select_grupo[0] * 1}.${select_grupo[1] * 1}`;
        if (select_grupo == valor_grupo_grupo * 1) {
            $("#campo_cod_familia").append('<option label="' + $(this).text() + '" value="' + $(this).val() + '">' + $(this).text() + '</option>').selectpicker('refresh');
        }
    });
}

/**
 * Receber valores dos select no qual for passado a id e a itenção.
 */

function getSelect(id, type){
    if(type == 'text'){
        return $(`#campo_${id} :selected`).text();
    }
    if(type == 'value'){
        return $(`#campo_${id}`).selectpicker('val');
    }
}

/**
 * Realizar calcular da formação de preço.
 * Preencher cada campo determinado com seus respectivos valores.
 */
function valorFinal(perc = false){
    let valor_compra = parseValor($("#campo_valor_compra").val());
    let valor_frete = parseValor($('#campo_valor_preco_fob').val());
    let valor_comissao = parseValor($("#campo_valor_comissao").val());
    let valor_fixo = parseValor($("#campo_valor_custos_fixos").val());
    let perc_ipi = parseValor($("#campo_perc_ipi").val());
    
    const parcial = valor_compra + valor_frete + valor_comissao + valor_fixo;
    const imposto = parcial * (perc_ipi / 100);
    let markup;
    
    if(perc !== false){
        markup = ((parcial + imposto) * perc) / 100;
        $("#campo_valor_markup").val(markup.toFixed(2));
    }

    markup = parseValor($("#campo_valor_markup").val());
    
    let preco_venda = parcial + imposto + markup;      // preço total da venda
    
    //descobre o valor do preco da venda e preenche com os valores
    $('#campo_valor_venda').val(preco_venda.toFixed(2)).trigger("input");
    $("#dynamic_tabela [num]").each(function(){
        atualizaTabela($(this).closest("[num]").attr("num"), preco_venda.toFixed(2));
    });
}

function atualizaTabela(num = "templatex", valor = false) {
    if (num === "templatex") {
        return -1;
    }
    
    if(valor === false){
        valor = $('#campo_valor_venda').val();
    }
    
    $("#label_tab_valor_"+num).text(valor).trigger("input");

    // se acontecer isso esta no momento da criação da página, logo não precisa executar
    if(typeof valor === "undefined"){
        return -1;
    }
    
    const perc = ($("#campo_perc_tab_"+num).cleanVal() / 10000);
    $("#label_tab_valor_total_"+num).text((parseValor(valor) * perc).toFixed(2)).trigger("input");
}

function duplicarProduto(){
    const loc = window.location.href;
    window.location.href = loc+ "&duplicar=1";
}
