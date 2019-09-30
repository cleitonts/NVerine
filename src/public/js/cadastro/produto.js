//criar variaveis globais
var produto;
var options_grupo;
var options_subgrupo;

function pageUpdate(){
    /*ativar função valor_final() a cada blur;*/
    $('#campo_valor_custo, #campo_valor_cred_ipi, #campo_valor_cred_icms, #campo_valor_frete, #campo_valor_preco_fob, #campo_valor_markup, #campo_valor_venda, #campo_valor_icms, #campo_valor_descontos, #campo_valor_acrescimos').blur(function () {
        valor_final();
    });

    //recuperar valores dos campos abaixo
    options_grupo = $("#campo_grupo option");
    options_subgrupo = $("#campo_cod_familia option");

    hideProdutoEstruturado(2);
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

// esconde as informacoes dos campos de produto estruturado.
function hideProdutoEstruturado(value){
    $('#dynamic_tabela_produto_estruturado,#wrapper_valor_custo_pe,#wrapper_valor_venda_pe,#wrapper_valor_markup_pe').hide();
    if(value == 1){
        $('#dynamic_tabela_produto_estruturado,#wrapper_valor_custo_pe,#wrapper_valor_venda_pe,#wrapper_valor_markup_pe').show();
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
    var valor_familia = getSelect('familia-2', 'text');
    var valor_grupo = getSelect('grupo', 'text');

    valor_familia = valor_familia.split(")");
    valor_grupo = valor_grupo.split(")");

    $("#campo_grupo").html("").selectpicker('refresh');
    $("#campo_cod_familia").html("").selectpicker('refresh');

    const grupo_familia = valor_familia;
    const grupo_grupo = valor_grupo;

    options_grupo.each(function () {
        var valor_grupo_familia = $(this).text().substring(0, 2);
        if (grupo_familia[0] == valor_grupo_familia) {
            $("#campo_grupo").append('<option label="' + $(this).text() + '" value="' + $(this).val() + '">' + $(this).text() + '</option>').selectpicker('refresh');
        }
    });

    //mudar o grupo conforme o grupo
    options_subgrupo.each(function () {
        var valor_grupo_grupo = $(this).text().substring(0, 5);
        if (grupo_grupo[0] == valor_grupo_grupo) {
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
function valor_final(){
    let valor_custo = $("#campo_valor_custo").val();
    let campo_valor_cred_ipi = $('#campo_valor_cred_ipi').val();
    let campo_valor_cred_icms = $('#campo_valor_cred_icms').val();
    let campo_valor_frete = $('#campo_valor_frete').val();
    
    // Preenche campo_valor_preco_fob com valores idicados até descobrir o valor do preço fob
    $('#campo_valor_preco_fob').val((valor_custo - campo_valor_cred_icms - campo_valor_cred_ipi) + (campo_valor_frete * 1));
    
    let campo_valor_preco_fob = $('#campo_valor_preco_fob').val();
    let campo_valor_markup = $('#campo_valor_markup').val();
    let campo_valor_venda = $('#campo_valor_venda').val($('#campo_valor_preco_fob').val());
    let campo_valor_icms = $('#campo_valor_icms').val();
    let campo_valor_descontos = $('#campo_valor_descontos').val();
    let campo_valor_acrescimos = $('#campo_valor_acrescimos').val();
    let perc_margem = 0;

    //descobre o valor do perc margem e preenche o campo
    $('#campo_perc_margem').val((campo_valor_icms * 1) + (campo_valor_descontos * 1) + (campo_valor_acrescimos * 1) - perc_margem);
    
    //descobre o valor do preco da venda e preenche com os valores
    $('#campo_valor_venda').val(($('#campo_valor_preco_fob').val() * 1) + (campo_valor_markup * 1));

    campo_valor_venda = $('#campo_valor_venda').val()
    let campo_perc_margem = (((campo_valor_venda * 1) - (campo_valor_preco_fob * 1) - (campo_valor_icms * 1) - (campo_valor_descontos * 1) - (campo_valor_acrescimos * 1)) / campo_valor_preco_fob) * 100;
    
    // Verificar se o valor campo_perc_margem foi calculado de forma indevida
    if(isNaN(campo_perc_margem)){
        campo_perc_margem = "0";
    }
    
    $('#campo_perc_margem').val(campo_perc_margem * 1);
}
