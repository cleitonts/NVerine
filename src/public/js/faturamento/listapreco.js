$(function() {
    triggerUpdate();
});

//atualiza linha do produto
function triggerUpdate() {
    // pesquisa produto
    $("[id^='campo_nome'], [id^='campo_prod_cod']").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: _pasta+"json.php?pagina=produtos_redux",
                dataType: "json",
                data: {term: request.term},
                success: function(data) {response(data);}
            });
        },
        select: function(event, ui) {
            var num = $(this).parents("tr").attr("num");

            $("[id^='label_prod_cod']").each( function(event){
                var val = parseInt($(this).text());

                if(val == ui.item.id) {
                    alert("Este produto ja existe nesta lista.");
                    ui.item.label = "";
                    ui.item.id = "";
                    ui.item.preco = "";
                }
            });

            $("#campo_nome_"+num).val(ui.item.label);
            $("#campo_prod_cod_"+num).val(ui.item.id);
            $("#label_cod_"+num).text(ui.item.id);
            $("#label_valor_original_"+num).text(ui.item.preco);
        }
    });

    // re-calcula totais da linha
    $("[id^='campo_perc_desconto'], [id^='campo_valor_promocao']").blur(function() {
        atualizaLinhaProduto(this);
    });
}

function atualizaLinhaProduto(obj) {
    // número da linha
    var num = parseFloat($(obj).parents("tr").attr("num"));

    // puxa valores base
    var valor_bruto = parseFloat($("#label_valor_original_"+num).text());
    var perc_desconto = parseFloat($("#campo_perc_desconto_"+num).val());
    var valor_liq;

    if($(obj).val() == $("#campo_valor_promocao_"+num).val()) {
        var valor_liq = parseFloat($("#campo_valor_promocao_"+num).val());
        var perc_desconto = 100 - (valor_liq / (valor_bruto / 100));
    }
    else {
        valor_liq = (valor_bruto / 100) * (100 - perc_desconto);
    }

    if(perc_desconto < 0 ){
        perc_desconto = 0;
    }

    $("#campo_perc_desconto_"+num).val(perc_desconto.toFixed(2));
    $("#campo_valor_promocao_"+num).val(valor_liq.toFixed(2));
}