
$(document).ready(function(){
    $(".nova_matricula").click(function(){novaMatricula();});

    // para atualizar as linhas já existentes
    atualizaHooksMatricula();
});


// remove a linha
function removeMatricula(obj) {
    $(".tipsy").remove();
    $(obj).parents("tr").remove();
}

function atualizaHooksMatricula() {
    // botões
    $(".tipsy-w").tipsy({gravity: "w"});
    $(".remove_matricula").click(function(){removeMatricula(this);});

    // pesquisa aluno
    $("[id^='campo_aluno'], [id^='campo_aluno']").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "json.php?pagina=aluno",
                dataType: "json",
                data: {term: request.term},
                success: function(data) {response(data);}
            });

            console.log(data);
        },
        select: function(event, ui) {
            var num = $(this).parents("tr").attr("num");

            $("#campo_aluno_"+num).val(ui.item.value);
            $("#campo_cod_"+num).val(ui.item.id);
        }
    });
}



// linhas de matrícula
function novaMatricula() {

    // scripts
    atualizaHooksMatricula();


}