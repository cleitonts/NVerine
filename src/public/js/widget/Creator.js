// exemplo de como chamar o loaded
$(document).on("loaded", function(){

    //setTimeout(function(){ Tools.refreshScroll(); }, 4000);
});

/**
 * renderiza o widgeto
 */
class Creator {
    create(obj, limpa_log) {
        var val = obj.render;
        var elemento = this;

        Creator.resetCachedFunction();

        //tem algum arquivo js para fazer include?
        if (val.includes.length > 0) {

            //itera lista de arquivos js
            for (var i = val.includes.length; i >= 0; i--) {
                if (i == 0) {
                    Tools.loadScript(val.includes[i], function () {
                        elemento.render(obj, limpa_log);
                    });
                }
                else {
                    Tools.loadScript(val.includes[i], function () {
                        //Stuff to do after someScript has loaded
                    });
                }
            }
        }
        else {
            elemento.render(obj, limpa_log);
        }
    }

    static getCachedFunction() {
        const temp = Object.keys(window).filter(function (x) {
            return window[x] instanceof Function && !/\[native code\]/.test(window[x].toString());
        });

        for (var i in temp) {
            cached_function[temp[i]] = temp[i];
        }
    }

    static resetCachedFunction() {
        const objs = Object.keys(window).filter(function (x) {
            return window[x] instanceof Function && !/\[native code\]/.test(window[x].toString());
        });

        for (var i in objs) {
            if (typeof cached_function[objs[i]] === "undefined") {
                window[objs[i]] = function () {
                    // função vazia, reseta
                }
            }
        }
    }

    render(obj, limpa_log) {
        var val = obj.render;

        // conserta o scroll
        document.getElementById("page-scroll").scrollTo(1, 0);

        this.html = document.createElement("div");
        //createTextNode
        this.html.className = "card mb-1";
        Body.create(this.html, val);
        Header.create(this.html, val.header);

        // renderiza os prints
        DevInfo.init(limpa_log);
        if (obj.dev_log.length != 0) {
            DevInfo.renderDump(obj.dev_log);
        }

        var row = document.createElement("div");
        row.className = "row";

        // ultima coisa a fazer
        $("#main-content").html("");    // limpa antes
        $(row).append(this.html);
        $("#main-content").append(row);
        Creator.reload();
    }

    static reload() {
        $('.money').mask('#.##0.00', {reverse: true}); // mascara causando erro dentro do cadastro de produto.
        //$('.percent').mask('##0.00%', {reverse: true});
        $('input[type="text"]').focus(function () {
            this.select()
        });

        // cancela submit para enviar por ajax
        $("form").submit(function (e) {
            e.preventDefault();

            if ($(this).attr("id").search('form_pesquisa') < 0) {
                Form.send(this);
            }
            else {
                tabelas.send();
            }

        });

        $(window).on('show.bs.dropdown', function () {
            $("[title]").tooltip('hide');
        });

        // carrega os tooltips
        $('[title]:not(".dropdown-toggle")').tooltip();

        //    Activate bootstrap-select
        if ($(".selectpicker").length != 0) {
            $(".selectpicker").not("[id*='templatex']").selectpicker();
        }

        // campo todos de checkbox
        $("[data-group][type='checkbox']").each(function () {
            var campo = $(this).attr("data-group");
            $(this).click(function () {
                let check = this.checked;
                $("[name*='" + campo + "']").each(function () {
                    this.checked = check;
                });
            });
        });

        $("textarea[name*='editor']").each(function () {
            var editor = CKEDITOR.instances[this.id];
            if (editor) {
                editor.destroy(true);
            }
            CKEDITOR.replace(this.id);
        });

        $(".datepicker-datetime").datetimepicker({
            format: 'DD-MM-YYYY LT',
            icons: dateicons,
            locale: 'pt-br'
        }).blur(function () {
            $(this).closest(".form-group").addClass("is-filled");
        });
        $(".datepicker-time").datetimepicker({
            format: 'LT',
            icons: dateicons,
            locale: 'pt-br'
        }).blur(function () {
            $(this).closest(".form-group").addClass("is-filled");
        });
        $(".datepicker-date").datetimepicker({
            format: 'DD-MM-YYYY',
            icons: dateicons,
            locale: 'pt-br'
        }).blur(function () {
            $(this).closest(".form-group").addClass("is-filled");
        });

        $("[acao]").each(function () {
            var acao = $(this).attr("acao");

            if (acao == "gera_calendario") {
                Calendario.lista_ano($(this));
            }

            if (acao == "gerar_calendario_wrapper") {
                Calendario.calendario_wrapper(true);
            }

            if (acao == "gera_galeria") {
                Gallery.create($(this));
            }
        });

        $(document).trigger("loaded");

        // esse nome deve vir padão dos arquivos externos
        if (typeof triggerUpdate === "function") {
            triggerUpdate();
        }

        // esse nome deve vir padão dos arquivos externos
        if (typeof pageUpdate === "function") {
            pageUpdate();
        }
    }
}
