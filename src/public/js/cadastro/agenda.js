function pageUpdate() {

    // carrega primeira vez quando entra na tela
    $.get("json.php?pagina=regioes").done(function (data) {
        spinner(true);
        var obj = $("#campo_regiao");
        geraOptions(JSON.parse(data), obj);
        campoEscola($(obj).val());
        spinner(false);
    });

    // trigger para recarregar campo de escolas
    $("#campo_regiao").change(function () {
        spinner(true);
        campoEscola($(this).val());
        spinner(false);
    });

    // trigger para recarregar campo de turmas
    $("#campo_escola").change(function(){
        spinner(true);
        campoTurma($(this).val());
        spinner(false);
    });

    // pesquisa aluno
    $("#campo_aluno").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "json.php?pagina=aluno",
                dataType: "json",
                data: {term: request.term},
                success: function(data) {response(data);}
            });
        },
        select: function(event, ui) {
            $("#campo_aluno").val(ui.item.value);
            $("#campo_cod_aluno").val(ui.item.id);
        }
    });

    // pesquisa pessoa
    $("#campo_pessoa").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "json.php?pagina=pessoa",
                dataType: "json",
                data: {term: request.term},
                success: function(data) {response(data);}
            });
        },
        select: function(event, ui) {
            $("#campo_pessoa").val(ui.item.value);
            $("#campo_cod_pessoa").val(ui.item.id);
        }
    });

    // pesquisa responsavel
    $("#campo_responsavel").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "json.php?pagina=pessoa",
                dataType: "json",
                data: {term: request.term},
                success: function(data) {response(data);}
            });
        },
        select: function(event, ui) {
            $("#campo_responsavel").val(ui.item.value);
            $("#campo_cod_responsavel").val(ui.item.id);
        }
    });
}

// recarrega campo de escolas
function campoEscola(regiao){
    if(regiao != "") {
        $.get("json.php?pagina=filiais", {regiao: regiao}).done(function (data) {
            var obj = $("#campo_escola");
            geraOptions(JSON.parse(data), obj);
        });
    }
    else{
        $("#campo_escola").html("");
    }
}

// recarrega campo de turmas
function campoTurma(escola){
    if(escola != "") {
        $.get("json.php?pagina=turmas", {escola: escola}).done(function (data) {
            var obj = $("#campo_turma");
            geraOptions(JSON.parse(data), obj);
        });
    }
    else{
        $("#campo_turma").html("");
    }
}

// gera todas as options de todos os campos dinamicos
function geraOptions(val, obj){
    var padrao = parseInt($(obj).val());
    $(obj).html("");

    // adiciona linha em branco
    $(obj).append('<option value=""></option>');

    for(var i = 0; i < val.length; i++){
        if(padrao == (i + 1)){
            $(obj).append('<option value="'+val[i].handle+'" selected="selected">'+val[i].name+'</option>');
        }
        else {
            $(obj).append('<option value="'+val[i].handle+'">'+val[i].name+'</option>');
        }
    }
    $(obj).selectpicker('refresh');
}