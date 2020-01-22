function pageUpdate() {
    // correções da ficha
    if (1 == 1) {
        // corrige posicionamento
        let h2 = document.createElement("h2");
        h2.className = "col-6";

        $(h2).append(document.createTextNode($("#campo_nome").val()));

        let handle = $("#campo_handle").val();

        let ficha = document.createElement("a");
        ficha.className = "btn btn-info float-right";
        ficha.target = "_blank";
        ficha.href = "relatorio.php?pagina=educacional_aluno&pesq_codigo=" + handle;
        $(ficha).append(document.createTextNode("Ficha"));

        let financeiro = document.createElement("a");
        financeiro.className = "btn btn-success float-right";
        financeiro.target = "_blank";
        financeiro.href = "index.php?pagina=contabil_titulos&pesq_natureza=2&pesq_cod_pessoa=" + handle;
        $(financeiro).append(document.createTextNode("Financeiro"));

        let div = document.createElement("div");
        div.className = "col-6";

        let row = document.createElement("div");
        row.className = "row";
        $(div).append(financeiro);
        $(div).append(ficha);
        $(row).prepend(div);
        $(row).prepend(h2);
        $("#card_ficha > .card-body").prepend(row);
        $("#main-content > .row").prepend($("#card_ficha")[0]);
    }

    // máscaras
    $("[id^='campo_telefone']").mask("99-999999999");
    $("#campo_cnae").mask("9999-9/99"); // ??

    // máscara por tipo de pessoa
    $("#campo_tipo").change(function(){
        mascaraCpfCnpj()
    });

    // autocompleta CEP
    $("[id^='campo_cep']").mask("99999-999");

    // carrega lista de cidades
    $("[id^='campo_uf']").change();

    //atualizaHooks();
    mascaraCpfCnpj();
};

function triggerUpdate(){
    // menu de municípios dinâmico
    $("[id^='campo_uf']").on('change', function(){
        var num = $(this).closest("[num]").attr("num");
        var obj = $("#campo_cidade_"+num);

        if(num != "templatex") {
            getMunicipios(this, obj, "");
        }
    });

    $("[id^='campo_cep']").keyup(function(){
        var num = $(this).closest("[num]").attr("num");

        var cep = $(this).val();
        cep = cep.replace("_", "");

        if(cep.length == 9) {
            spinner();

            $.getJSON("https://viacep.com.br/ws/"+cep+"/json", "")
                .done(function(retorno) {
                    if(retorno.erro) {
                        var popup = new Alert();
                        popup.typo = "Error";
                        popup.texto = "CEP inexistente na base de dados";
                        popup.montaMensagem();
                        spinner(false);
                    }
                    else {
                        //if(retorno.localidade == "Brasília") retorno.localidade = "Brasilia";

                        if(num >= 0) {
                            if(retorno.logradouro.length > 0) {
                                $("#campo_logradouro_" + num).val(retorno.logradouro)
                                    .closest(".form-group").addClass("is-filled");
                            }
                            if(retorno.complemento.length > 0) {
                                $("#campo_complemento_" + num).val(retorno.complemento)
                                    .closest(".form-group").addClass("is-filled");
                            }
                            if(retorno.bairro.length > 0) {
                                $("#campo_bairro_" + num).val(retorno.bairro)
                                    .closest(".form-group").addClass("is-filled");
                            }
                            //pega o valor primeiro
                            var val = $("#campo_uf_"+num).find("[label='"+retorno.uf+"']").attr('value');

                            // seleciona estado pela uf
                            $("#campo_uf_"+num).selectpicker('val', val);

                            // sobrescreve cidade
                            $("#campo_cidade_"+num).html("<option value='"+retorno.localidade+"'>"+retorno.localidade+"</option>");
                            $("#campo_cidade_"+num).selectpicker('refresh');

                        }
                    }

                    spinner(false);
                })
                .fail(function(){
                    var popup = new Alert();
                    popup.typo = "Error";
                    popup.texto = "Não foi possível buscar o CEP no servidor";
                    popup.montaMensagem();
                    spinner(false);
                });
        };
    });
}

function mascaraCpfCnpj() {
    var tipo = $("#campo_tipo").val();
    var cpf_cnpj = $("#campo_cpfcnpj").val();

    if(tipo == "F")	 {
        $("#campo_cpfcnpj").mask("999.999.999-99").val(cpf_cnpj).change();
        $("#campo_cnae").addClass("form-disabled").attr("readonly", "readonly");
    }
    if(tipo == "J")	 {
        $("#campo_cpfcnpj").mask("99.999.999/9999-99").val(cpf_cnpj).change();
        $("#campo_cnae").removeClass("form-disabled").removeAttr("readonly");
    }
}

function getMunicipios(obj_estado, obj_cidade, cidade_default) {
    var estado = $(obj_estado).val();
    if(cidade_default == "") cidade_default = $(obj_cidade).val();

    if(estado.length > 0) {
        $.getJSON("json.php?pagina=municipios&term="+estado, "")
            .done(function(municipios){
                $(obj_cidade).empty();
                for(var i = 0; i < municipios.length; i++) {
                    var selected = "";
                    if(municipios[i].label.toLowerCase() == cidade_default.toLowerCase()) selected = "selected='selected'";

                    $(obj_cidade).append("<option value=\""+municipios[i].label+"\" "+selected+">"+municipios[i].label+"</option>");
                }

                $(obj_cidade).selectpicker('refresh');

                // disparar esse evento ajuda em algumas coisas
                $(obj_cidade).change();
            })
            .fail(function(jqxhr){
                alert("Conexão com banco de dados perdida");
            });
    }
}

function malaDireta() {
    let submit = $("#form_pesquisa").serializeArray();

    // convert o form em url sem usar get
    let url = "relatorio.php?pagina=mala_direta";
    for(key in submit){
        url = url + encodeURI("&"+submit[key].name+"="+submit[key].value);
    }

    window.open(url, '_blank');
}