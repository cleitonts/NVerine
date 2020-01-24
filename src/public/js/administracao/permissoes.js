function triggerUpdate() {
    $("#dynamic_permissoes [num]").each(function () {
       listBtn(this);
    });

    $("#dynamic_permissoes [type='checkbox']").click(function(){
        blockBtn(this);
    });
}

function listBtn(row) {
    const num = $(row).attr("num");
    const editar = $("#wrapper_editar_"+num+" input");
    const total = $("#wrapper_total_"+num+" input");

    if(total[0].checked !== false){
        blockBtn(total[0]);
    }
    if(editar[0].checked !== false){
        blockBtn(editar[0]);
    }
}

function blockBtn(obj){
    const closest = $(obj).closest("[num]");
    const num = parseInt($(closest).attr("num"));
    const editar = $("#wrapper_editar_"+num+" input");
    const visualizar = $("#wrapper_vizualizar_"+num+" input");

    if(obj.checked){
        if(obj.id.includes("total") !== false){
            if(editar[0].checked !== true){
                $(editar).click();
                $(visualizar).click();
            }

            $(editar).attr("disabled", "disabled");
            $(visualizar).attr("disabled", "disabled");
            $("#wrapper_vizualizar_"+num+" .form-check").toggleClass("disabled");
            $("#wrapper_editar_"+num+" .form-check").toggleClass("disabled");
        }
        else if(obj.id.includes("editar") !== false){
            if(visualizar[0].checked !== true){
                $(visualizar).click();
            }

            $(visualizar).attr("disabled", "disabled");
            $("#wrapper_vizualizar_"+num+" .form-check").toggleClass("disabled");
        }
    }
    else {
        if(obj.id.includes("total") !== false){
            $(editar).removeAttr("disabled");
            $("#wrapper_editar_"+num+" .form-check").toggleClass("disabled");
        }
        else if(obj.id.includes("editar") !== false){
            $(visualizar).removeAttr("disabled");
            $("#wrapper_vizualizar_"+num+" .form-check").toggleClass("disabled");
        }
    }
}