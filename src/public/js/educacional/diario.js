// executa ao incorporar este script

function pageUpdate(){
    // $("#dynamic_turma_aluno tr").not("[num='templatex']").find(".btn-danger").each(function(){
    //     this.innerHTML = "";
    //     var handle = $(this).closest("tr").find("[id^='campo_cod_aluno']").val();
    //     $(this).click(function(){   verAluno(handle)    });
    //
    //     this.className = "fa fa-user btn btn-default btn-round btn-fab btn-sm";
    //     this.title = "Ver aluno";
    // });

    $('#campo_data_diario').on('dp.change', function(e){
        if(e.date){
            spinner(true);
            var data = e.date.format("DD-MM-YYYY");
            var turma = $("#campo_handle_turma").val();

            $.get("json.php?pagina=turma_horario", {pesq_turma: turma, pesq_data: data}).done(function (data) {
                montaGrade(JSON.parse(data));

            });
        }else{

        }
    });

    $("#dynamic_conteudo .novo_item").css("display", "none");
}

function montaGrade(valores){
    // limpa lixo
    $(".grade_limpar").remove();
    $("[id^='campo_observacoes']").val("");

    // itera as linhas
    for(item in valores){

        // monta conteudo
        montaConteudo(valores[item]);
        // monta a th primeiro
        var th = document.createElement("th");
        th.className = "grade_limpar rotate-300";

        var div = document.createElement("div");

        var span = document.createElement("span");
        span.style = "padding: 5px 10px; font-size: 0.8em;";
        $(span).append(document.createTextNode(valores[item].horario_inicio+" - "+valores[item].disciplina));

        $(div).append(span);
        $(th).append(div);

        $("#dynamic_turma_aluno thead tr th").last().before(th);

        $("#dynamic_turma_aluno tbody tr").each(function () {
            var num = parseInt($(this).attr("num"));


            if(typeof valores[0] != "undefined"){

                $("#campo_cod_horario_"+num).val(valores[0].handle);

                var td = document.createElement("td");
                td.className = "grade_limpar";

                var form_check = document.createElement("div");
                form_check.className = "form-check form-check-inline";

                var label = document.createElement("label");
                label.className = "form-check-label";

                var input = document.createElement("input");
                input.checked = true;                   // por padrão todos estão marcados
                // primeiro check se não é undefined
                if(typeof valores[item].diario[num] != "undefined"){
                    $("#campo_observacoes_"+num).val(valores[0].diario[num].historico);

                    // depois checa de estava presente
                    if(valores[item].diario[num].presenca == "N"){
                        input.checked = false;
                    }
                }
                input.type = "checkbox";
                input.name = "turma_aluno["+num+"][falta]["+item+"]";
                input.id = "campo_falta_"+num+"_"+item;
                input.className = "form-check-input";

                var sign = document.createElement("span");
                sign.className = "form-check-sign";

                var check = document.createElement("span");
                check.className = "check";

                $(sign).append(check);
                $(label).append(input);
                $(label).append(sign);
                $(form_check).append(label);
                $(td).append(form_check);
                $(this).find("td").last().before(td);
            }

        });
    }

    // amarra ação de selecionar todos clicando no nome
    $("#dynamic_turma_aluno tbody tr").each(function(){
        var tr = this;
        var td = $(tr).find("td").first();

       $(td).addClass("btn btn-transparent").click(function () {
           $(tr).find("[type='checkbox']").attr("checked", false);
       });
    });

    spinner(false);
}

function montaConteudo(val){
    $("#dynamic_conteudo .novo_item").click();

    var row = $("#dynamic_conteudo [num]").last();
    $(row).addClass("grade_limpar");

    $(row).find("[id^='campo_horario']").val(val.horario_inicio);
    $(row).find("[id^='campo_disciplina']").val(val.disciplina);
    $(row).find("[id^='campo_professor']").val(val.professor);
    $(row).find("[id^='campo_cod_professor']").val(val.cod_professor);
    if(val.diario.length > 0){
        $(row).find("[id^='campo_conteudo']").val(val.diario[0].conteudo);
    }
}