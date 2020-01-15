var calendario_eventos = [];
var calendario_wrapper;
class Calendario {

    /*ativar e desativar slide lateral para o calendario*/
    static toggle(obj){
        $("#message-log").hide('slide', {}, 500, {});


        // troca ação entre mostrar e ocultar
        if($(obj).hasClass("active")){
            $("#calendario-log").hide('slide', {}, 500, {});
        }
        else {
            $("#calendario-log").show("slide", {}, 500, {});
        }

        // troca classes para efeito visual
        $(obj).toggleClass("active");
        $(obj).find("i").toggleClass("fa-exclamation-triangle fa-times");
        $("[id^='mes-div-']").hide().removeClass().addClass("col-md-12");

        this.caminhar_data('false', obj);
    }

    static calendario_wrapper(info){
        calendario_wrapper = info;
    }

    static caminhar_data(value, obj){
        $("[id^='mes-div-']").hide();
        var mes = $("#meses-ano").val();
        var ano = $("#data-ano").val();

        if(value == "proceguir"){
            if((mes * 1) + 1 == 13){
                mes = 0;
                ano = (ano * 1) + 1;

                $("#data-ano").val(ano);
                $('#wrapper_calendario_2').attr("ano", ano);
                Creator.reload();
            }
            $("#meses-ano").val((mes * 1) + 1);
        }else if(value == "retornar"){
            if((mes * 1) - 1 == 0){
                mes = 13;
                ano = (ano * 1) - 1;
                $("#data-ano").val(ano);
                $("#wrapper_calendario_2").attr("ano",ano);
                Creator.reload();
            }
            $("#meses-ano").val((mes * 1) - 1);
        }

        mes = $("#meses-ano").val();
        mes = mes.replace(/^0+/, '');
        $("#mes-div-"+mes).show();
    }

    // lista um ano completo
    static lista_ano(component) {
        var ano = $(component).attr("ano");
        if (ano == "") {
            ano = new Date();
            ano = ano.getFullYear();
        }
        var wrapper = document.createElement("div");
        wrapper.className = "row";
        Calendario.listar_meses(ano, wrapper);
        $(component).before(wrapper);

        // mapeia os eventos
        Calendario.getDados(ano);

        if(calendario_wrapper == true){
            $("[id^='mes-div-']").hide().removeClass().addClass("col-md-12");
        }

    }


    static getDados(ano) {

        $(document).on('tabela_carregada', function(e, valores){
            Calendario.informar_valor(valores);
        });

        // antes de mais nada printa o spinner
        spinner(true);

        var dados = [];
        dados.push(
            {name: "pesq_top", value: 0},
            {name: "pesq_pagina", value: 1},
            {name: "pesq_data_inicial", value: "01-01-"+ano},
            {name: "pesq_data_final", value: "31-12-"+ano},
            {name: "file", value: "Faturamento"},
            {name: "ordena_por", value: 0},
            {name: "class", value: "src\\entity\\AgendaGUI"},
            {name: "only_headers", value: false}
        );
        $.ajax({
            url: "tabela.php",
            dataType: "json",
            type: 'POST',
            data: dados
        }).done(function (valores) {
            Calendario.informar_valor(valores);
            spinner(false);
        }).fail(function () {
            var popup = new Alert();
            popup.typo = "danger";
            popup.texto = "Calendario não encontrado";
            popup.montaMensagem();
            spinner(false);
        });
    }

    static informar_valor(valores){
        calendario_eventos = [];
        $(".dia_mes.dia_evento").removeClass('dia_evento').attr('onclick', '').find('.calendar_contador').text('');

        if (valores.render.itens != null) {
            montaModal();
            for (var i = 0; i < valores.render.itens.length; i++) {
                // instancia como dates do javascript
                var inicio = new Date(valores.render.itens[i].colunas[0]+" 00:00:00");
                var fim = new Date(valores.render.itens[i].colunas[1]+" 00:00:00");

                // // descobre intervalo
                // var intervalo = Calendario.diasEntre(inicio, fim);
                //
                // // executa para cada dia do intervalo
                // for (var c = 0; c <= intervalo; c++) {
                //
                //     var atual = new Date(inicio);
                //     atual.setDate(inicio.getDate() + c);
                //
                //     // salva atalhos
                //     var mes = atual.getMonth() + 1;
                //     var dia = atual.getDate();
                //     var ano = atual.getFullYear();

                var mes = inicio.getMonth() + 1;
                var dia = inicio.getDate();
                var ano = inicio.getFullYear();

                // cria objeto
                if (typeof calendario_eventos[ano] == "undefined") {
                    calendario_eventos[ano] = {};
                }
                if (typeof calendario_eventos[ano][mes] == "undefined") {
                    calendario_eventos[ano][mes] = {};
                }
                if (typeof calendario_eventos[ano][mes][dia] == "undefined") {
                    calendario_eventos[ano][mes][dia] = [];
                }

                calendario_eventos[ano][mes][dia].push(valores.render.itens[i]);
                calendario_eventos["header"] = valores.render.header;

                var obj = $("[mes='" + mes + "'] [dia='" + dia + "'] .calendar_contador")[0];
                var num = parseInt($(obj).text());

                $("[mes='" + mes + "'] [dia='" + dia + "']").attr("onclick", "ModalCalendario(this)").addClass("dia_evento");

                if (isNaN(num)) {
                    num = 0;
                }
                num++;
                $(obj).text(num);
                //}
            }
            // por ultimo, remove o spinner
        }
    }

    // cria a caixa para jogar todos os meses
    static cria_wrapper() {
        var wrapper = document.createElement("div");
        wrapper.className = "calendar-wrapper";

        Calendario.listar_meses(ano, wrapper);

        $("#main-content > .widget-body").prepend(wrapper);
    }

    // cria loop de mes
    static listar_meses(ano, obj) {
        var contador_mes = 1;
        while (contador_mes <= 12) {
            Calendario.cria_mes(contador_mes, ano, obj);
            contador_mes++;
        }
    }

    static cria_mes(mes, ano, obj) {
        var wrapper = document.createElement("div");
        wrapper.className = "col-md-4";
        wrapper.id = `mes-div-${mes}`;
        var html = document.createElement("div");
        html.className = "card mt-1";
        html.setAttribute("mes", mes);

        // cria header com o nome do mes
        var mesDate = new Date();
        mesDate.setMonth(mes - 1);
        var nome_mes = mesDate.toLocaleString("pt-br", {month: "long"});

        var header = document.createElement("div");
        header.className = "card-header card-header-warning text-center text-capitalize";
        header.appendChild(document.createTextNode(nome_mes));
        $(html).append(header);

        // conta o wrapper dos dias
        var wrapper_dias = document.createElement("div");
        wrapper_dias.className = "calendar-dias";

        var contador = 1;

        // chama função que cria os dias da semana
        Calendario.nomeDias(wrapper_dias);

        var dias_mes = Calendario.diasNoMes(mes, ano); // executar somente 1x

        // insere dias em branco
        var primeiro_dia = new Date(ano, mes - 1, contador);

        for (var i = 0; i < primeiro_dia.getDay(); i++) {
            Calendario.cria_dia("", wrapper_dias);
        }

        // insere dias normais
        while (dias_mes >= contador) {
            Calendario.cria_dia(contador, wrapper_dias);
            contador++;
        }

        $(html).append(wrapper_dias);
        $(wrapper).append(html);
        $(obj).append(wrapper);
    }

    static nomeDias(obj) {
        var dia = document.createElement("div");
        dia.className = "day-name";
        dia.appendChild(document.createTextNode("DOM"));
        $(obj).append(dia);

        var dia = document.createElement("div");
        dia.className = "day-name";
        dia.appendChild(document.createTextNode("SEG"));
        $(obj).append(dia);

        var dia = document.createElement("div");
        dia.className = "day-name";
        dia.appendChild(document.createTextNode("TER"));
        $(obj).append(dia);

        var dia = document.createElement("div");
        dia.className = "day-name";
        dia.appendChild(document.createTextNode("QUA"));
        $(obj).append(dia);

        var dia = document.createElement("div");
        dia.className = "day-name";
        dia.appendChild(document.createTextNode("QUI"));
        $(obj).append(dia);

        var dia = document.createElement("div");
        dia.className = "day-name";
        dia.appendChild(document.createTextNode("SEX"));
        $(obj).append(dia);

        var dia = document.createElement("div");
        dia.className = "day-name";
        dia.appendChild(document.createTextNode("SAB"));
        $(obj).append(dia);

    }

    static diasNoMes(mes, ano) {
        return new Date(ano, mes, 0).getDate();
    }


    static cria_dia(dia, obj) {
        var prop = document.createAttribute("dia");
        var html = document.createElement("div");
        html.className = "dia_mes";
        html.setAttribute("dia", dia);

        if (dia == "") {
            html.className += " dia_branco";
        }

        var div = document.createElement("div");
        //div.appendChild(document.createTextNode("teste"));

        var html_dia = document.createElement("div");
        html_dia.className = "dia_calendario";
        html_dia.appendChild(document.createTextNode(dia));

        var html_contador = document.createElement("div");
        html_contador.className = "calendar_contador";

        $(div).append(html_dia);
        $(div).append(html_contador);
        $(html).append(div);
        $(obj).append(html);
    }

    static diasEntre(date1, date2) {
        //Get 1 day in milliseconds
        var one_day = 1000 * 60 * 60 * 24;

        // Convert both dates to milliseconds
        var date1_ms = date1.getTime();
        var date2_ms = date2.getTime();

        // Calculate the difference in milliseconds
        var difference_ms = date2_ms - date1_ms;

        // Convert back to days and return
        return Math.round(difference_ms / one_day);
    }
}

// monta modal para mostrar a lista de eventos
function montaModal(){
    var modal = new Modal();
    modal.name = "modal_dias";
    modal.size = "modal-lg";
    modal.title = "eventos";
    modal.no_footer = true;

    var trigger_modal = document.createElement("button");
    trigger_modal.type = "button";
    trigger_modal.id = "trigger_"+modal.name;
    trigger_modal.style = "display: none";
    trigger_modal.className = "btn btn-success";
    trigger_modal.setAttribute("data-toggle", "modal");
    trigger_modal.setAttribute("data-target", "#"+modal.name);

    $("#main-content").append(modal.render(), trigger_modal);
}

function ModalCalendario(obj){
    spinner(true);

    // pega a data e busca no array de itens os eventos do dia
    var ano = new Date();
    ano = ano.getFullYear();
    var dia = parseInt($(obj).attr("dia"));
    var mes = parseInt($(obj).closest("[mes]").attr("mes"));
    var atual = calendario_eventos[ano][mes][dia];

    // cria a wrapper da header
    let header = document.createElement("thead");
    $(header).append(document.createElement("tr"));
    Tabelas.montaHeader(calendario_eventos["header"], header);

    // cria a wrapper da body
    let body = document.createElement("tbody");

    for (let i = 0; i <= atual.length - 1; i++) {
        let linha = document.createElement("tr");
        linha.setAttribute("num", atual[i].handle);

        // montas as tds
        Tabelas.montaLinha(atual[i], linha);

        let handle = $(linha).attr("num");
        let retorno = window.location.href;

        let url = "?pagina=agenda&pesq_num="+handle+"&retorno=" + encodeURIComponent(retorno);

        $(linha).attr("onclick", "Tools.redirect('"+url+"');").addClass("editavel");

        // joga no objeto da tbody
        $(body).append(linha);
    }

    var separator = document.createElement("div");
    separator.className = "row";

    var table_wrapper = document.createElement("div");
    table_wrapper.className = "col-md-12";

    var table_scroll = document.createElement("div");
    table_scroll.className = "table-responsive perfect-scrollbar-container perfect-scrollbar-horizontal table-hover";

    var tabela = document.createElement("table");
    tabela.id = "tabela_dias";
    tabela.className = "table table-striped";

    $(tabela).append(header);
    $(tabela).append(body);
    $(table_scroll).append(tabela);
    $(table_wrapper).append(table_scroll);
    $(separator).append(table_wrapper);
    $("#modal_dias .modal-body").html("");
    $("#modal_dias .modal-body").append(separator);
    $("#trigger_modal_dias").click();

    /*
    //limpa dados antigos
    elemento.tabela.innerHTML = "";

    // salva novos dados
    $(elemento.tabela).append(header);
    $(elemento.tabela).append(body);

    elemento.acoes();
*/

    // por ultimo, remove o spinner
    spinner(false);
}
/*

class CalendarioWrapper {
    /!*ativar e desativar slide lateral para o calendario*!/
    static toggle(obj){
        $("#message-log").hide('slide', {}, 500, {});


        // troca ação entre mostrar e ocultar
        if($(obj).hasClass("active")){
            $("#calendario-log").hide('slide', {}, 500, {});
        }
        else {
            $("#calendario-log").show("slide", {}, 500, {});
        }

        // troca classes para efeito visual
        $(obj).toggleClass("active");
        $(obj).find("i").toggleClass("fa-exclamation-triangle fa-times");
        $("[id^='mes-div-']").hide().removeClass().addClass("col-md-12");

        this.caminhar_data('false', obj);


    }

    static caminhar_data(value, obj){
        $("[id^='mes-div-']").hide();
        var mes = $("#meses-ano").val();
        var ano = $("#data-ano").val();

        if(value == "proceguir"){
            if((mes * 1) + 1 == 13){
                mes = 0;
                ano = (ano * 1) + 1;

                $("#data-ano").val(ano);
                $('#wrapper_calendario_2').attr("ano", ano);
                Creator.reload();
            }
            $("#meses-ano").val((mes * 1) + 1);
        }else if(value == "retornar"){
            if((mes * 1) - 1 == 0){
                mes = 13;
                ano = (ano * 1) - 1;
                $("#data-ano").val(ano);
                $("#wrapper_calendario_2").attr("ano",ano);
                Creator.reload();
            }
            $("#meses-ano").val((mes * 1) - 1);
        }

        mes = $("#meses-ano").val();
        mes = mes.replace(/^0+/, '');
        $("#mes-div-"+mes).show();
    }

    // lista um ano completo
    static lista_ano(component) {
        var ano = $(component).attr("ano");
        if (ano == "") {
            ano = new Date();
            ano = ano.getFullYear();
        }
        var wrapper = document.createElement("div");
        wrapper.className = "row";
        CalendarioWrapper.listar_meses(ano, wrapper);

        $(component).before(wrapper);

        // mapeia os eventos
        CalendarioWrapper.getDados(ano);
        $("[id^='mes-div-']").hide().removeClass().addClass("col-md-12");

    }


    static getDados(ano) {

        $(document).on('tabela_carregada', function(e, valores){
            CalendarioWrapper.informar_valor(valores);
        });

        // antes de mais nada printa o spinner
        spinner(true);

        var dados = [];
        dados.push(
            {name: "pesq_top", value: 999999},
            {name: "pesq_pagina", value: 1},
            {name: "file", value: "Faturamento"},
            {name: "pesq_data_inicial", value: "01-01-"+ano},
            {name: "pesq_data_final", value: "31-12-"+ano},
            {name: "ordena_por", value: 0},
            {name: "class", value: "src\\entity\\AgendaGUI"},
            {name: "only_headers", value: false}
        );
        $.ajax({
            url: "tabela.php",
            dataType: "json",
            type: 'POST',
            data: dados
        }).done(function (valores) {
            CalendarioWrapper.informar_valor(valores);
            spinner(false);
        }).fail(function () {
            var popup = new Alert();
            popup.typo = "danger";
            popup.texto = "Calendario não encontrado";
            popup.montaMensagem();
            spinner(false);
        });
    }

    static informar_valor(valores){
        calendario_eventos = [];
        $(".dia_mes.dia_evento").removeClass('dia_evento').attr('onclick', '').find('.calendar_contador').text('');

        if (valores.render.itens != null) {
            montaModal();
            for (var i = 0; i < valores.render.itens.length; i++) {
                // instancia como dates do javascript
                var inicio = new Date(valores.render.itens[i].colunas[0]+" 00:00:00");
                var fim = new Date(valores.render.itens[i].colunas[1]+" 00:00:00");

                // // descobre intervalo
                // var intervalo = Calendario.diasEntre(inicio, fim);
                //
                // // executa para cada dia do intervalo
                // for (var c = 0; c <= intervalo; c++) {
                //
                //     var atual = new Date(inicio);
                //     atual.setDate(inicio.getDate() + c);
                //
                //     // salva atalhos
                //     var mes = atual.getMonth() + 1;
                //     var dia = atual.getDate();
                //     var ano = atual.getFullYear();

                var mes = inicio.getMonth() + 1;
                var dia = inicio.getDate();
                var ano = inicio.getFullYear();

                // cria objeto
                if (typeof calendario_eventos[ano] == "undefined") {
                    calendario_eventos[ano] = {};
                }
                if (typeof calendario_eventos[ano][mes] == "undefined") {
                    calendario_eventos[ano][mes] = {};
                }
                if (typeof calendario_eventos[ano][mes][dia] == "undefined") {
                    calendario_eventos[ano][mes][dia] = [];
                }

                calendario_eventos[ano][mes][dia].push(valores.render.itens[i]);
                calendario_eventos["header"] = valores.render.header;

                var obj = $("[mes='" + mes + "'] [dia='" + dia + "'] .calendar_contador")[0];
                var num = parseInt($(obj).text());

                $("[mes='" + mes + "'] [dia='" + dia + "']").attr("onclick", "ModalCalendario(this)").addClass("dia_evento");

                if (isNaN(num)) {
                    num = 0;
                }
                num++;
                $(obj).text(num);
                //}
            }
            // por ultimo, remove o spinner
        }
    }

    // cria a caixa para jogar todos os meses
    static cria_wrapper() {
        var wrapper = document.createElement("div");
        wrapper.className = "calendar-wrapper";

        CalendarioWrapper.listar_meses(ano, wrapper);

        $("#main-content > .widget-body").prepend(wrapper);
    }

    // cria loop de mes
    static listar_meses(ano, obj) {
        $("[id^='mes-div-']").hide();
        var contador_mes = 1;
        while (contador_mes <= 12) {
            CalendarioWrapper.cria_mes(contador_mes, ano, obj);
            contador_mes++;
        }
    }

    static cria_mes(mes, ano, obj) {
        var wrapper = document.createElement("div");
        wrapper.className = "col-md-4";
        wrapper.id = `mes-div-${mes}`;
        var html = document.createElement("div");
        html.className = "card mt-1";
        html.setAttribute("mes", mes);

        // cria header com o nome do mes
        var mesDate = new Date();
        mesDate.setMonth(mes - 1);
        var nome_mes = mesDate.toLocaleString("pt-br", {month: "long"});

        var header = document.createElement("div");
        header.className = "card-header card-header-warning text-center";
        header.appendChild(document.createTextNode(nome_mes));
        $(html).append(header);

        // conta o wrapper dos dias
        var wrapper_dias = document.createElement("div");
        wrapper_dias.className = "calendar-dias";

        var contador = 1;

        // chama função que cria os dias da semana
        CalendarioWrapper.nomeDias(wrapper_dias);

        var dias_mes = CalendarioWrapper.diasNoMes(mes, ano); // executar somente 1x

        // insere dias em branco
        var primeiro_dia = new Date(ano, mes - 1, contador);

        for (var i = 0; i < primeiro_dia.getDay(); i++) {
            CalendarioWrapper.cria_dia("", wrapper_dias);
        }

        // insere dias normais
        while (dias_mes >= contador) {
            CalendarioWrapper.cria_dia(contador, wrapper_dias);
            contador++;
        }

        $(html).append(wrapper_dias);
        $(wrapper).append(html);
        $(obj).append(wrapper);
    }

    static nomeDias(obj) {
        var dia = document.createElement("div");
        dia.className = "day-name";
        dia.appendChild(document.createTextNode("DOM"));
        $(obj).append(dia);

        var dia = document.createElement("div");
        dia.className = "day-name";
        dia.appendChild(document.createTextNode("SEG"));
        $(obj).append(dia);

        var dia = document.createElement("div");
        dia.className = "day-name";
        dia.appendChild(document.createTextNode("TER"));
        $(obj).append(dia);

        var dia = document.createElement("div");
        dia.className = "day-name";
        dia.appendChild(document.createTextNode("QUA"));
        $(obj).append(dia);

        var dia = document.createElement("div");
        dia.className = "day-name";
        dia.appendChild(document.createTextNode("QUI"));
        $(obj).append(dia);

        var dia = document.createElement("div");
        dia.className = "day-name";
        dia.appendChild(document.createTextNode("SEX"));
        $(obj).append(dia);

        var dia = document.createElement("div");
        dia.className = "day-name";
        dia.appendChild(document.createTextNode("SAB"));
        $(obj).append(dia);

    }

    static diasNoMes(mes, ano) {
        return new Date(ano, mes, 0).getDate();
    }


    static cria_dia(dia, obj) {
        var prop = document.createAttribute("dia");
        var html = document.createElement("div");
        html.className = "dia_mes";
        html.setAttribute("dia", dia);

        if (dia == "") {
            html.className += " dia_branco";
        }

        var div = document.createElement("div");
        //div.appendChild(document.createTextNode("teste"));

        var html_dia = document.createElement("div");
        html_dia.className = "dia_calendario";
        html_dia.appendChild(document.createTextNode(dia));

        var html_contador = document.createElement("div");
        html_contador.className = "calendar_contador";

        $(div).append(html_dia);
        $(div).append(html_contador);
        $(html).append(div);
        $(obj).append(html);
    }

    static diasEntre(date1, date2) {
        //Get 1 day in milliseconds
        var one_day = 1000 * 60 * 60 * 24;

        // Convert both dates to milliseconds
        var date1_ms = date1.getTime();
        var date2_ms = date2.getTime();

        // Calculate the difference in milliseconds
        var difference_ms = date2_ms - date1_ms;

        // Convert back to days and return
        return Math.round(difference_ms / one_day);
    }
}

// monta modal para mostrar a lista de eventos
function montaModal(){
    var modal = new Modal();
    modal.name = "modal_dias";
    modal.size = "modal-lg";
    modal.title = "eventos";
    modal.no_footer = true;

    var trigger_modal = document.createElement("button");
    trigger_modal.type = "button";
    trigger_modal.id = "trigger_"+modal.name;
    trigger_modal.style = "display: none";
    trigger_modal.className = "btn btn-success";
    trigger_modal.setAttribute("data-toggle", "modal");
    trigger_modal.setAttribute("data-target", "#"+modal.name);

    $("#main-content").append(modal.render(), trigger_modal);
}

function ModalCalendario(obj){
    spinner(true);

    // pega a data e busca no array de itens os eventos do dia
    var ano = new Date();
    ano = ano.getFullYear();
    var dia = parseInt($(obj).attr("dia"));
    var mes = parseInt($(obj).closest("[mes]").attr("mes"));
    var atual = calendario_eventos[ano][mes][dia];

    // cria a wrapper da header
    let header = document.createElement("thead");
    $(header).append(document.createElement("tr"));
    TabelasETT.montaHeader(calendario_eventos["header"], header);

    // cria a wrapper da body
    let body = document.createElement("tbody");

    for (let i = 0; i <= atual.length - 1; i++) {
        let linha = document.createElement("tr");
        linha.setAttribute("num", atual[i].handle);

        // montas as tds
        TabelasETT.montaLinha(atual[i], linha);

        let handle = $(linha).attr("num");
        let retorno = window.location.href;

        let url = "?pagina=agenda&pesq_num="+handle+"&retorno=" + encodeURIComponent(retorno);

        $(linha).attr("onclick", "Tools.redirect('"+url+"');").addClass("editavel");

        // joga no objeto da tbody
        $(body).append(linha);
    }

    var separator = document.createElement("div");
    separator.className = "row";

    var table_wrapper = document.createElement("div");
    table_wrapper.className = "col-md-12";

    var table_scroll = document.createElement("div");
    table_scroll.className = "table-responsive perfect-scrollbar-container perfect-scrollbar-horizontal table-hover";

    var tabela = document.createElement("table");
    tabela.id = "tabela_dias";
    tabela.className = "table table-striped";

    $(tabela).append(header);
    $(tabela).append(body);
    $(table_scroll).append(tabela);
    $(table_wrapper).append(table_scroll);
    $(separator).append(table_wrapper);
    $("#modal_dias .modal-body").html("");
    $("#modal_dias .modal-body").append(separator);
    $("#trigger_modal_dias").click();

    /!*
    //limpa dados antigos
    elemento.tabela.innerHTML = "";

    // salva novos dados
    $(elemento.tabela).append(header);
    $(elemento.tabela).append(body);

    elemento.acoes();
*!/

    // por ultimo, remove o spinner
    spinner(false);
}*/
