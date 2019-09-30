// executa quando ciar uma nova linha na tabela dinamica
function triggerUpdate(obj){
    // deixa pre selecionado o turno de acordo com a turma
    $(obj).find("[id^='campo_turno']").val($("#campo_cod_turno").val());
    $(obj).find("[id^='campo_data_entrada']").val($("#campo_data_inicio").val());
    alunoAutocomplete();
}

function formUpdate(){
    var i = 1;
    $("[name='table_grade_horaria'] tr").each(function(){
        if($(this).is("[num]")){
            if($(this).attr("num") != "templatex"){
                var num = $(this).attr("num");
                $(this).find("input, select").each(function(){
                    $(this).attr("name", $(this).attr("name").replace(new RegExp(num, 'g'), i))
                });
                i++;
            }
        }
    })
}

// executa ao incorporar este script
function pageUpdate(){
    $("#dynamic_turma_aluno tr").not("[num='templatex']").find(".btn-danger").each(function(){
        this.innerHTML = "";
        var handle = $(this).closest("tr").find("[id^='campo_cod_aluno']").val();
        $(this).click(function(){   verAluno(handle)    });

        this.className = "fa fa-user btn btn-default btn-round btn-fab btn-sm";
        this.title = "Ver aluno";
    });

    $("#table_turma_aluno tr").not("[num='templatex']").find("[id*='data']").datepicker();
    alunoAutocomplete();
};

function alunoAutocomplete(){
    // pesquisa aluno
    $("[id^='campo_aluno']").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "json.php?pagina=aluno",
                dataType: "json",
                data: {term: request.term},
                success: function(data) {response(data);}
            });
        },
        select: function(event, ui) {
            var num = $(this).parents("tr").attr("num");

            $("#campo_aluno_"+num).val(ui.item.value);
            $("#label_cod_"+num).text(ui.item.id);
            $("#campo_cod_aluno_"+num).val(ui.item.id);
        }
    });
}

// duplica o cadastro da turma (remove todos os handles)
function duplica() {
    var certeza = confirm("Deseja copiar todos os dados desta turma em uma NOVA TURMA?");

    if(!certeza) return 0;

    $("#campo_nome").val("Cópia de "+($("#campo_nome").val()));
    $("[id^='campo_handle']").remove();                         // remove todos os handles

    $("#campo_enviar").click();
}

function verAluno(num){
    window.open("index.php?pagina=educacional_aluno&pesq_codigo="+num, "_blank");;
}