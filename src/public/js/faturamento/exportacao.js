$(function() {
    // c�lculo do saldo de estoque
    $("[id^='campo_entrega_qtd']").change(function(){
        // n�mero da linha
        var num = parseFloat($(this).parents("tr").attr("num"));

        // puxa a quantidade or�ada da linha do produto
        var qtd_orcada = getValor("prod_quantidade_"+num);
        var qtd_entregue = getValor("entrega_qtd_entregue_"+num);
        var qtd_baixada = getValor("entrega_qtd_baixada_"+num);
        var qtd_saldo = qtd_orcada - qtd_entregue - qtd_baixada;

        $("#campo_entrega_qtd_saldo_"+num).val(qtd_saldo);

        // valida saldo
        if(qtd_saldo < 0 || qtd_saldo > qtd_orcada) {
            $("#campo_entrega_qtd_saldo_"+num).addClass("form-error");
        }
        else {
            $("#campo_entrega_qtd_saldo_"+num).removeClass("form-error");
        }
    });

    // atualiza saldo dispon�vel no endere�o/almoxarifado
    $("[id^='campo_entrega_endereco'], [id^='campo_entrega_qtd']").change(function(){
        // n�mero da linha
        var num = parseFloat($(this).parents("tr").attr("num"));

        // tipo da nota: entrada ou sa�da
        var tipo = $("#campo_tipo").val();

        // puxa valores
        $.ajax({
            url: _pasta+"json.php?pagina=estoque_saldos",
            dataType: "json",
            data: {
                produto: $("#campo_prod_cod_produto_"+num).val(),
                endereco: $("#campo_entrega_endereco_"+num).val()
            }
        })
            .done(function(valores){
                $("#campo_entrega_qtd_disponivel_"+num).val(valores.saldo);

                // valida saldo
                var qtd_entregue = getValor("entrega_qtd_entregue_"+num);

                /* CONSIDERAR:
                 * se movimento � entrada ou sa�da (soma ou subtrai) - ok
                 * se h� estoque reservado (tem que puxar e informar o dispon�vel sem o estoque de seguran�a?)
                 * se o produto movimenta ou n�o saldo
                 */
                if(tipo == "S") {
                    if(valores.saldo < qtd_entregue) {
                        $("#campo_entrega_qtd_disponivel_"+num).addClass("form-error");
                    }
                    else {
                        $("#campo_entrega_qtd_disponivel_"+num).removeClass("form-error");
                    }
                }
            });
    });
});