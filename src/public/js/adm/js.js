var cached_function = [];

moment.updateLocale('pt-br', {
    weekdaysMin : ["D", "S", "T", "Q", "Q", "S", "S"],
    weekdaysShort: ["Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sab"]
});


var dateicons = {
    time: "fa fa-clock-o",
    date: "fa fa-calendar",
    up: "fa fa-chevron-up",
    down: "fa fa-chevron-down",
    previous: 'fa fa-chevron-left',
    next: 'fa fa-chevron-right',
    today: 'fa fa-screenshot',
    clear: 'fa fa-trash',
    close: 'fa fa-remove'
};

function toggleMenu(){
    $("#togglemenu").toggleClass("d-none");
    $("footer.sticky-bottom").toggleClass("d-none");
    $("nav.sticky-top").toggleClass("d-none");
    $("#page-scroll").toggleClass("full");
}

$(document).ready(function(){
    // inicia datepicker
    $(".datepicker-datetime").datetimepicker({
        format: 'DD-MM-YYYY LT',
        icons: dateicons,
        locale: 'pt-br'
    }).blur(function(){
        $(this).closest(".form-group").addClass("is-filled");
    });
    $(".datepicker-time").datetimepicker({
        format: 'LT',
        icons: dateicons,
        locale: 'pt-br'
    }).blur(function(){
        $(this).closest(".form-group").addClass("is-filled");
    });
    $(".datepicker-date").datetimepicker({
        format: 'DD-MM-YYYY',
        icons: dateicons,
        locale: 'pt-br'
    }).blur(function(){
        $(this).closest(".form-group").addClass("is-filled");
    });

    $("textarea[name*='editor']").each(function () {
        CKEDITOR.replace( this.id );
    });

    // monta as mascaras
    $("input[data-mask]").each(function(){
        var mascara = $(this).attr("data-mask");
        $(this).mask(mascara, {reverse: true});
    });

    Creator.getCachedFunction();

    // eventos de navegação
    window.addEventListener('popstate', function(e){
        if(e.state)
            Tools.redirect(e.state.href, true);
    });

    window.addEventListener('reload', function(e){
        //if(e.state)
        //    Tools.redirect(e.state.href, true);
    });

    //const ps_menu = new PerfectScrollbar('#perfect-sb-menu');

    $(".menu-header").click(function(){
        if($(this)[0] == $(".menu-body.active .menu-header")[0]){
            $(".menu-body.active").toggleClass("active").find(".menu-list").slideUp( "slow", function() {});
        }
        else{
            $(".menu-body.active").toggleClass("active").find(".menu-list").slideUp( "slow", function() {});
            $(this).closest(".menu-body").toggleClass("active").find(".menu-list").slideDown( "slow", function() {});
        }
    });

    // pesquisa global
    $("#live-search").autocomplete({
        source: "json.php?pagina=global",
        select: function(event, ui) {
            spinner();
            window.location = ui.item.url;
        }
    });

    // quebra url, o importante será somente os parametros
    var pagina = window.location.href.split("?");

    // abre a pagina requisitada
    Tools.redirect("?"+pagina[1]);
});

// abrir páginas sem âncora
var global_bloqueia_saida = false;

function limpaCampos(){
    $("[name^='pesq_']:not([type='HIDDEN'])").val("").closest(".is-filled").removeClass("is-filled");
}

function destinoMenu(pagina) {
    if(global_bloqueia_saida) {
        var result = confirm("Você fez alterações a um ou mais campos desta página. Deseja sair sem salvar?");

        if(!result) {
            // retorna à primeira aba se usou aba "retornar"
            $("#sd1").removeAttr("style").show();

            // cancela o destino
            return;
        }
    }

    spinner();
    window.location = "index.php?pagina="+pagina;
}

function destinoMenu2(pagina) {
    if(global_bloqueia_saida) {
        var result = confirm("Você fez alterações a um ou mais campos desta página. Deseja sair sem salvar?");

        if(!result) {
            // retorna à primeira aba se usou aba "retornar"
            $("#sd1").removeAttr("style").show();

            // cancela o destino
            return;
        }
    }

    spinner();
    window.location = "index.php?pagina="+pagina;
}

// exibe ou esconde o overlay de carregamento de paginas via ajax
function spinner(tipo = true) {
    //console.trace();
    var val = parseInt($("#cont_spinner").val());
    if(tipo == true) {
        val++;
        if(val > 0){
            $("#spinner, .dark-overlay.overlay-spinner").fadeIn();
        }
        $("#cont_spinner").val(val);

    }
    else {
        val--;
        if(val < 0){
            console.log("spinner não foi aberto mais foi fechado");
        }
        if(val == 0){
            $("#spinner, .dark-overlay.overlay-spinner").fadeOut();
        }
        $("#cont_spinner").val(val);
    }
}

// para campos de data
function zeros(i) {
    var str = String(i);

    if(str.length == 1)
        return "0" + str;
    else
        return str;
}

// retorna o campo com o valor mascarado
function maskMoney(campo, val){
    if(typeof val === "undefined"){
        console.log(campo, "com valor indefinido");
        console.trace();
        val = 0;
    }
    $(campo).text(val.toFixed(__CASAS_DECIMAIS__)).mask("#.##0,00", {reverse: true}).trigger('input');
    $(campo).val(val.toFixed(__CASAS_DECIMAIS__)).mask("#.##0,00", {reverse: true}).trigger('input');
}

// recupera o valor do campo mascarado de uma maneira q o js entenda
function parseMoney(valor){
    if(typeof valor == "undefined"){
        return 0;
    }

    if(valor.includes(",")) {
        valor = valor.replace(".", "");
    }
    valor = valor.replace(",", ".");
    valor = parseFloat(valor);
    if(isNaN(valor)) valor = 0;

    return valor;
}

// para ler campos numéricos e atribuir zero se vazio
function getValor(id) {
    var valor = $("#campo_"+id).val();

    // testa se valor é válido
    if(typeof(valor) == "undefined") return 0;

    // troca vírgulas por pontos
    valor = valor.replace(",", ".");
    $("#campo_"+id).val(valor);

    // retorna float ou zero
    valor = parseFloat(valor);
    if(isNaN(valor)) valor = 0;

    return valor;
}

// template js
$(document).ready(function(){

    $("input[id^='campo_data'],input[id^='campo_pesq_data'],input[id^='campo_alu_data']");

    // monitora alteração de campos
    $("form input[type='text']").change(function(){
        global_bloqueia_saida = true;
        // alert($(this).attr("id"));
    });

    // evita envio do formulário ao pressionar enter.
    $("form").bind("keypress", function (e) {
        if (e.keyCode == 13) {
            if($(key_atual).hasClass('keydown_free') == false && $(key_atual).hasClass('ui-autocomplete-input') == false){
                e.preventDefault();
                return false;
            }
        }
    });

    // widget tabs
    $(".widget-tab-group> li.on").each(function(){
        var obj = $(this);
        var s= obj.attr("data-section");
        obj.parents().eq(3).find("#"+s).show();
    });

    $(".widget-tab-group> li").on("click", function(){
        var obj= $(this);
        obj.parent().find(">li").removeClass("on");
        obj.addClass("on");
        var s = obj.attr("data-section");
        obj.parents().eq(3).find(".tab-section").hide();
        obj.parents().eq(3).find("#"+s).fadeIn("fast");
    });

    // dropdowns
    $(".btn-menu").on("click", function(){
        var m= $(this).find(".menu");
        if($(m).css("display")== "none"){
            $(".menu").hide("fast");
            $("#main-menu").slideUp();
            $("#sidepane").removeClass("pulled");
            $(m).show("fast");

            // dropdown fix
            $(this).css("opacity", 1);

            if($(this).hasClass("tipsy-n")) {
                $(this).addClass("tipsy-disabled").tipsy("disable");
            }
        }else{
            $(m).hide("fast");

            // dropdown fix
            $(this).removeAttr("style");

            if($(this).hasClass("tipsy-disabled")) {
                $(this).removeClass("tipsy-disabled").addClass("tipsy-n").tipsy("enable");
            }
        }
    });

    // accordions
    $(".widget-accordion").on("click", function(){
        var m= $(this).parents().eq(3).find(".widget-body");
        if($(m).css("display")== "none"){
            $(m).show("fast");
        }else{
            $(m).hide("fast");
        }
    });

    // altera ícones de accordion
    $(".widget-accordion").click(function(){
        var btn = $(this).find(".fa");

        if($(btn).hasClass("fa-plus")) {
            $(btn).removeClass("fa-plus").addClass("fa-minus");
        }
        else {
            $(btn).removeClass("fa-minus").addClass("fa-plus");
        }
    });

    // close
    $(".widget-close").on("click", function(){
        var m= $(this).parents().eq(3);
        $(m).fadeOut("fast");
    });

    // vertical inline tabs
    $(".v-tabs").tabs();
});

