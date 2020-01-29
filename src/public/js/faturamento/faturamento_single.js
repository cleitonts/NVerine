// var locais
var produtos = [];
var nota;
var duplicatas;

$(document).on('trigger_dynamic_produtos', function(e, p1, p2){
    produtos.push(new Produto());
});

function pageUpdate() {
    nota = new Nota();
    duplicatas = Duplicatas;

    // corrige posição
    $("#card_total")[0].closest(".card-body").className = "";
    $("#main-content > .row").prepend($("#card_total")[0]);
    $("#card_total input").attr("readonly", "readonly").css("background-image", "none");

    $("#dynamic_duplicatas [num]").each(function(){
        $(this).find("td:first").css("display", "none");
    });
    $("#dynamic_duplicatas #ultima_linha").css("display", "none");

    $("#dynamic_duplicatas .thead").each(function(){
        $(this).css("display", "none")
    });

    Nota.criaModalNFE();

    // instancia produtos
    $("#dynamic_produtos [num]:not([num='templatex'])").each(function(){
        produtos.push(new Produto($(this).attr("num")));
    });

    $("#campo_pessoa").autocomplete({
        source: "json.php?pagina=pessoas",
        select: function(event, ui) {
            $("#campo_cod_pessoa").val(ui.item.id).closest(".form-group").addClass("is-filled");
            $("#campo_tabela_padrao").val(ui.item.tabelaPreco);
            $("#campo_forma_pagamento").val(ui.item.formaPagamento).change();
            $("#campo_condicao_pagamento").val(ui.item.condicaoPagamento).change(); // propaga atualização dos outros campos
            if(ui.item.novo) alert("Função não disponível nesta página");
        }
    });

    Nota.notas();
}

class Duplicatas{
    static processa(){
        spinner(true);
        // limpa tabela antes de atualizar
        $("#dynamic_duplicatas [num]").not("[num='templatex']").remove();
        $("#dynamic_duplicatas #campo_dynamic_rows").val(0);

        var forma_pagamento = parseInt($("#campo_forma_pagamento").val());
        var condicao_pagamento = parseInt($("#campo_condicao_pagamento").val());

        // validações
        if (isNaN(forma_pagamento) || isNaN(condicao_pagamento)) {
            var popup = new Alert();
            popup.typo = "danger";
            popup.texto = "Por favor, preencha a forma/condição de pagamento";
            popup.montaMensagem();
            spinner(false);
            return;
        }

        $.getJSON("json.php?pagina=condicao_pagamento&term=" + condicao_pagamento, "").done(function (mapa) {
            Duplicatas.update(mapa, forma_pagamento);
            spinner(false);
        });
    }

    static update(mapa, forma) {
        spinner(true);
        // remove datepickers de templates (causa problemas com a instância de cópias)
        //$("[id^='campo_dup_data_']").datepicker("destroy");

        var valor_titulo = nota.total;
        var data_emissao = $("#campo_data_emissao").val();
        var dias = 0;
        var dias_anterior = 0;
        var total = 0;
        var valor = 0;

        if (mapa.length > 0) {
            // cuidado para otimizar
            var adianta_dias = 0;
            if ($("#campo_propriedade_0").is(":checked")) {
                adianta_dias = parseInt(mapa[0].dias);
            }
            var vencimento = parseInt($("#campo_spinner_vencimento").val());

            for (var num = 0; num < mapa.length; num++) {
                var linha = FieldTables.adicionaLinha("duplicatas");

                dias = mapa[num].dias - adianta_dias;

                // evita dízima!
                if (num == (mapa.length - 1)) {
                    valor = valor_titulo - total;
                }
                else {
                    valor = (valor_titulo * mapa[num].percentual) / 100;
                }

                // calcula valores
                valor = parseFloat(valor.toFixed(__CASAS_DECIMAIS__)); // arredonda centavos
                total += valor;

                // calcula datas
                if (data_emissao !== "") {
                    const QUANTOS_MS_TEM_UM_DIA = 24 * 60 * 60 * 1000;

                    // converte data de emissão
                    var d = parseInt(data_emissao.substr(0, 2));
                    var m = parseInt(data_emissao.substr(3, 2)) - 1;
                    var a = parseInt(data_emissao.substr(6, 4));
                    var em = new Date(a, m, d);

                    // usa vencimento fixo?
                    if (vencimento > 0) {
                        // primeiro vencimento no mês atual ou seguinte?
                        if (vencimento < d) {
                            var vo = new Date(a, m + num, vencimento);
                        }
                        else {
                            var vo = new Date(a, m + num - 1, vencimento);
                        }
                    }
                    // usa dias corridos
                    else {
                        // fallback
                        if (isNaN(dias)) dias = 0;

                        var vo = new Date(em.getTime() + (dias * QUANTOS_MS_TEM_UM_DIA));
                    }

                    // calcula vencimento real
                    var vr = new Date(vo.getTime());
                    while (vr.getDay() == 0 || vr.getDay() == 6) { // sábado ou domingo?
                        vr.setTime(vr.getTime() + QUANTOS_MS_TEM_UM_DIA);
                    }

                    // converte datas para texto
                    var vencimento_original = zeros(vo.getDate()) + "-" + zeros(vo.getMonth() + 1) + "-" + vo.getFullYear();
                    var vencimento_real = zeros(vr.getDate()) + "-" + zeros(vr.getMonth() + 1) + "-" + vr.getFullYear();

                    // re-calcula total de dias sobre vencimento real
                    dias = Math.round((vr - em) / QUANTOS_MS_TEM_UM_DIA);
                    var intervalo_atual = dias - dias_anterior;

                    dias_anterior = dias;
                }

                // atualiza valores
                $(linha).find("#label_numero_"+num).text(num +1);
                $(linha).find("#campo_numero_"+num).val(num +1);
                $(linha).find("#campo_data_vencimento_original_"+num).val(vencimento_original);
                $(linha).find("#campo_data_vencimento_real_"+num).val(vencimento_real);
                $(linha).find("#label_dias_"+num).text(dias);
                $(linha).find("#campo_dias_"+num).val(dias);
                $(linha).find("#label_intervalo_"+num).text(intervalo_atual);
                $(linha).find("#campo_intervalo_"+num).val(intervalo_atual);
                $(linha).find("#campo_valor_total_"+num).val(valor.toFixed(__CASAS_DECIMAIS__)).mask("#.##0,00", {reverse: true}).trigger('input');
                $(linha).find("#campo_forma_pagamento_"+num).val(forma).selectpicker('refresh');
            }
        }

        nota.totalProdutos();
        spinner(false);
    }
}

// linha de produtos
class Produto {
    // passar o OBJETO linha
    constructor (linha = false){
        // linha vazia
        if(!linha){
            this.num = parseInt($("#dynamic_produtos #campo_dynamic_rows").val()) - 1;	// número da linha
            this.linha = $("#dynamic_produtos tr[num='"+this.num+"']");
        }
        else{
            this.num = linha;	// número da linha
            this.linha = $("#dynamic_produtos tr[num='"+this.num+"']");
        }

        // mascaras
        this.getCampo("NCM").mask("9999.99.99");
        this.getCampo("cst_icms").mask("999");
        this.acoes();							// instancia as ações dos imputs
        this.pesquisa();						// instancia pesquisa para produtos

        this.atualizaValores(false);
    }

    //retorna o objeto campos
    getCampo(campo){
        return this.linha.find("#campo_"+campo+"_"+this.num);
    }

    // muito parecido com a função original, mas procura o valor dentro da mesma linha
    getValor(campo, tipo = false){
        var valor = this.linha.find("#campo_"+campo+"_"+this.num).val();

        if(typeof valor === "undefined"){
            return 0;
        }

        if(tipo === "int"){
            if(valor.includes(",")) {
                valor = valor.replace(".", "");
            }
            valor = valor.replace(",", ".");
            valor = parseInt(valor);
            if(isNaN(valor)) valor = 0;
        }
        else if(tipo === "str"){
            valor = String.valueOf(valor);
            if(!isNaN(valor)) valor = 0;
        }

        else if(tipo === "float"){
            valor = parseMoney(valor);
        }

        return valor;
    }

    estoque_saldo(){
        const elemento = this;

        // puxa valores
        $.ajax({
            url: "json.php?pagina=estoque_saldos",
            dataType: "json",
            data: {
                produto: elemento.id,
                endereco: elemento.endereco
            }
        })
            .done(function(valores){
                elemento.setValor("qtd_disponivel", valores.saldo);
                //$("#campo_qtd_disponivel_"+this.num).val(valores.saldo);

                // valida saldo
                const qtd_entregue = elemento.getValor("quantidade");

                /* CONSIDERAR:
                 * se movimento é entrada ou saída (soma ou subtrai) - ok
                 * se há estoque reservado (tem que puxar e informar o disponível sem o estoque de segurança?)
                 * se o produto movimenta ou não saldo
                 */
                if(valores.saldo < qtd_entregue) {
                    $("#campo_quantidade_"+this.num).addClass("form-error");
                }
                else {
                    $("#campo_quantidade_"+this.num).removeClass("form-error");
                }
            });
    }
    /* 	Pesquisa e autocompleta produto
     *	Pequena lista para otimizar a transferencia de dados
     */
    pesquisa (){
        var elemento = this;
        $("[id^='campo_produto']").autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "json.php?pagina=produtos_redux",
                    dataType: "json",
                    //async: false,
                    data: {term: request.term, filial: __FILIAL__},
                    success: function(data) {response(data);}
                });
            },
            select: function(event, ui) {
                // primeiro salva nas propriedades
                elemento.id = ui.item.id;
                elemento.nome = ui.item.label;
                elemento.unidade = ui.item.unidade;
                // depois salva nos campos
                elemento.setValor("produto", elemento.nome);
                $("#label_cod_produto_"+elemento.num).text(elemento.id);
                elemento.setValor("cod_produto", elemento.id);

                $("#label_unidade_"+elemento.num).text(elemento.unidade);
                elemento.setValor("unidade", elemento.unidade);
                elemento.atualiza();
            }
        });
    }

    // igual a anterior, mas set o valor
    setValor(str, valor){
        let campo = $("#campo_"+str+"_"+this.num);
        if($(campo).length > 0){
            $(campo).val(valor);
        }

        // atualiza de acordo com a doc de selecpicker
        if($(campo).hasClass("selectpicker")){
            $(campo).selectpicker('refresh');
        }

        campo = $("#label_"+str+"_"+this.num);
        if($(campo).length > 0){
            $(campo).text(valor);
        }
    }

    // Traz a lista completa com todos os detalhes do produto
    atualiza (){
        spinner(true);

        // o ajax nao trabalha bem com THIS
        var elemento = this;
        
        $.ajax({
            url: __PASTA__+"json.php?pagina=produto_valores",
            dataType: "json",
            data: {
                term: 			elemento.id,
                tabela: 		elemento.tabela,
                tipo_operacao: 	elemento.tipo_operacao,
                pessoa: 		$("#campo_cod_pessoa").val(),
                forma: 			$("#campo_forma_pagamento").selectpicker('val'),
                tipo_movimento: $("#campo_tipo").selectpicker('val'), // entrada/saída
                filial: 		__FILIAL__
            }
        })
            .done(function(valores){
                // preenche todos os objetos locais com os valores do JSON
                elemento.valor_unitario = valores.valorUnitario;
                elemento.perc_desconto = valores.percDesconto;
                elemento.quantidade = valores.quantidade;
                elemento.perc_ipi = valores.percIpi;
                elemento.perc_icms = valores.percIcms;
                elemento.tipo_operacao = valores.codMovimento;
                elemento.ncm = valores.ncm;
                elemento.cst = valores.cst;
                elemento.cst_ipi = valores.cstIpi;
                elemento.cfop = valores.cfop;
                elemento.csosn = valores.csosn;
                elemento.perc_pis = valores.percPis;
                elemento.perc_cofins = valores.percCofins;
                elemento.perc_issqn = valores.percIssqn;
                elemento.endereco = valores.endereco;
                elemento.modalidade = valores.modalidadeBCIcms;
                elemento.fator_bc_icms = valores.fatorBCIcms;
                elemento.substituicao = valores.usaSubstituicaoTributaria;
                elemento.mva = valores.margemValorAgregado;
                elemento.medida_t = valores.medida_t;
                elemento.lista_indice = valores.lista_indice;

                if(!valores.operacaoEstadual) elemento.cfop = valores.cfopInterestadual;
                // atualiza campos
                elemento.setValor("quantidade", 		    elemento.quantidade); // a princípio, é 1
                elemento.setValor("tipo_operacao", 	        elemento.tipo_operacao);
                elemento.setValor("valor_unitario", 	    elemento.valor_unitario);
                elemento.setValor("valor_tabela", 		    elemento.valor_unitario);
                //elemento.setValor("perc_desconto", 	        elemento.perc_desconto);
                elemento.setValor("perc_ipi", 			    elemento.perc_ipi);
                elemento.setValor("perc_icms", 		        elemento.perc_icms);
                elemento.setValor("perc_pis", 			    elemento.perc_pis);
                elemento.setValor("perc_cofins", 		    elemento.perc_cofins);
                elemento.setValor("perc_issqn", 		    elemento.perc_issqn);
                elemento.setValor("cfop", 				    elemento.cfop);
                elemento.setValor("NCM", 				    elemento.ncm);
                elemento.setValor("cst_icms",			    elemento.cst);
                elemento.setValor("CST_IPI",			    elemento.cstIpi);
                elemento.setValor("csosn", 			        elemento.csosn);
                elemento.setValor("medida_t", 			    elemento.medida_t);
                elemento.setValor("endereco",			    elemento.endereco);
                elemento.setValor("modalidade_bc",	    	elemento.modalidade);
                elemento.setValor("fator_bc", 		    	elemento.fator_bc_icms);
                elemento.setValor("usa_st",			        elemento.substituicao);
                elemento.setValor("mva",				    elemento.mva);

                // cfop estadual ou interestadual? (ou exterior?)
                $("#campo_destino").val(valores.destinoNota);  			// ############
                $("#campo_natureza_operacao").val(valores.cfopDescricao).change(); 	// ############

                elemento.estoque_saldo();
                elemento.atualizaValores();

                // monstra o alert
                if(elemento.lista_indice != "") {
                    spinner(false);
                    var popup = new Alert();
                    popup.typo = "danger";
                    popup.texto = "O produto "+elemento.nome+" esta usando a lista de preço com indice " +elemento.lista_indice;
                    popup.montaMensagem();
                }
                spinner(false);
            })
            .fail(function(){
                spinner(false);
                var popup = new Alert();
                popup.typo = "danger";
                popup.texto = "Conexão com o banco de dados perdida";
                popup.montaMensagem();
            });
    }

    /*	Atualiza valores da linha do produto
     * 	calcula varias coisas, como valor total entre outros..
     * 	Desmembrar a função de arredondamento, pois assim a performance vai melhorar consideravelmente
     */
    atualizaValores (salvar = true, obj = null){
        this.unidade = this.getValor("unidade");
        this.medida_x = this.getValor("medida_x", "int");
        this.medida_z = this.getValor("medida_z", "int");
        this.quantidade = this.getValor("quantidade", "float");
        this.valor_tabela = this.getValor("valor_tabela", "float");
        this.valor_unitario = this.getValor("valor_unitario", "float");
        this.perc_ipi = this.getValor("perc_ipi", "float");
        this.perc_icms = this.getValor("perc_icms", "float");
        this.fator_bc_icms = this.getValor("fator_bc", "float");
        this.valor_frete = this.getValor("valor_frete", "float");
        this.valor_bruto = (this.quantidade * this.valor_unitario);
        this.valor_desconto = 0;
        this.perc_desconto = 0;
        // se um foi alterado precisa refletir no outro
        let campo_valor_desconto = this.getCampo("valor_desconto");
        let campo_perc_desconto = this.getCampo("perc_desconto");

        if(campo_valor_desconto[0] === obj){
            this.valor_desconto = this.getValor("valor_desconto", "float");
            this.perc_desconto = ((this.valor_desconto * 100) / this.valor_bruto).toFixed(5);
        }
        if(campo_perc_desconto[0] === obj){
            this.perc_desconto = this.getValor("perc_desconto", "float");
            this.valor_desconto = (this.perc_desconto / 100) * this.valor_bruto;
        }

        this.valor_base = 		this.valor_bruto - this.valor_desconto;
        this.valor_bc_ipi = 	this.valor_base; // IPI não considera desconto?
        this.valor_bc_icms = 	this.valor_base * this.fator_bc_icms; // processar o % base de cálculo do tipo de operação!
        this.valor_ipi = 		this.perc_ipi * (this.valor_bc_ipi / 100);
        this.valor_icms = 		this.perc_icms * (this.valor_bc_icms / 100);

        // zera base de cálculo se não houver alíquota
        if(this.perc_ipi == 0) this.valor_bc_ipi = 0;
        if(this.perc_icms == 0) this.valor_bc_icms = 0;

        this.valor_total = this.valor_bruto - this.valor_desconto + this.valor_ipi;

        // zera base de cálculo se não houver alíquota
        if(this.perc_ipi == 0) this.valor_bc_ipi = 0;
        if(this.perc_icms == 0) this.valor_bc_icms = 0;

        if(salvar){
            this.salvaValores();
            nota.totalProdutos();
        }
    }

    // separado pois pode ter casos em que nao quero salvar os valores
    salvaValores (){
        maskMoney($("#campo_valor_bruto_"+this.num), this.valor_bruto);
        maskMoney($("#label_valor_bruto_"+this.num), this.valor_bruto);
        $("#campo_perc_desconto_"+this.num).val(this.perc_desconto).mask('00,0000%', {reverse: true}).trigger('input');
        maskMoney($("#campo_valor_desconto_"+this.num), this.valor_desconto);
        maskMoney($("#campo_valor_ipi_"+this.num), this.valor_ipi);
        maskMoney($("#label_valor_ipi_"+this.num), this.valor_ipi);
        maskMoney($("#campo_valor_bc_ipi_"+this.num), this.valor_bc_ipi);
        maskMoney($("#label_valor_bc_ipi_"+this.num), this.valor_bc_ipi);
        maskMoney($("#campo_valor_total_"+this.num), this.valor_total);
        maskMoney($("#label_valor_total_"+this.num), this.valor_total);
        maskMoney($("#campo_valor_bc_icms_"+this.num), this.valor_bc_icms);
        maskMoney($("#label_valor_bc_icms_"+this.num), this.valor_bc_icms);
        maskMoney($("#campo_valor_icms_"+this.num), this.valor_icms);
        maskMoney($("#label_valor_icms_"+this.num), this.valor_icms);
    }

    acoes(){
        const elemento = this;

        var selector = $(this.getCampo("quantidade")).add(this.getCampo("medida_x"));
        $(selector).change(() => {elemento.estoque_saldo()});

        selector = $(this.getCampo("quantidade"))
            .add(this.getCampo("medida_x"))
            .add(this.getCampo("valor_unitario"))
            .add(this.getCampo("perc_desconto"))
            .add(this.getCampo("valor_desconto"))
            .add(this.getCampo("medida_z"))
            .add(this.getCampo("emenda"))
            .add(this.getCampo("valor"))
            .add($("[id^='campo_perc']:not([id^='campo_perc_desconto'])"));	// todos os percentuais serao atualizados

        $(selector).change(function() {
            elemento.atualizaValores(true, this);
        });

        // atualiza valores com o json
        var tipo = this;
        selector = $(this.getCampo("tipo_operacao"))
        $(selector).change(function() {
            tipo.tipo_operacao = $(this).val();
            tipo.atualiza();
        });
    }
}

// classe com objetos globais como totalizadores
class Nota {
    constructor() {
        var elemento = this;
        // atualização de totais caso troque o vendedor
        $("#campo_vendedor, #campo_supervisor").change(function () {
            elemento.totalProdutos();
        });
    }

    totalProdutos() {
        this.bruto = this.somaValor("campo_valor_bruto");
        this.desconto = this.somaValor("campo_valor_desconto");
        this.ipi = this.somaValor("campo_valor_ipi");
        this.total = this.somaValor("campo_valor_total");
        this.icms = this.somaValor("campo_valor_icms");
        this.frete = this.somaValor("campo_valor_frete");
        this.sobrepreco = this.somaValor("campo_sobrepreco");
        this.comissao = this.somaValor("campo_comissao");

        var duplicatas = 0;
        // gera total de duplicatas
        $("#dynamic_duplicatas [id^='campo_valor_total']").each(function () {
            var valor = $(this).val();

            if (valor.includes(",")) {
                valor = valor.replace(".", "");
            }
            valor = valor.replace(",", ".");
            valor = parseFloat(valor);
            if (isNaN(valor)) valor = 0;
            duplicatas += valor;
        });

        Nota.setTotal("duplicatas", duplicatas);
        Nota.setTotal("produtos", this.bruto);
        Nota.setTotal("descontos", this.desconto);
        Nota.setTotal("ipi", this.ipi);
        Nota.setTotal("total", this.total);
        Nota.setTotal("icms", this.icms);
        Nota.setTotal("frete", this.frete);
        Nota.setTotal("comissao", this.comissao);
        Nota.setTotal("sobrepreco", this.sobrepreco);

    }

    // soma os totais dos produtos
    somaValor(id) {
        var total = 0;

        $("#dynamic_produtos [id^='" + id + "']").each(function () {
            var valor = $(this).val();

            if (valor.includes(",")) {
                valor = valor.replace(".", "");
            }
            valor = valor.replace(",", ".");
            valor = parseFloat(valor);
            if (isNaN(valor)) valor = 0;
            total += valor;
        });

        return total;
    }

    // igual a anterior, mas set o valor
    static setTotal(campo, valor) {
        let table = $("#dynamic_totais");
        $(table).find("[id^='campo_" + campo + "']").val(valor.toFixed(__CASAS_DECIMAIS__));
    }

    static criaModalNFE() {
        var modal = new Modal();
        modal.title = "Novo evento de nota fiscal";
        modal.btnSuccess = "Enviar Evento";
        modal.size = "modal-lg"; // "modal-lg, modal-sm";
        //modal.btnSuccessAct =  //function() {FieldTables.deleteLinha(modal)};
        modal.btnCancelAct = ""; //function() {FieldTables.deleteLinha(modal)};
        modal.name = "modal_nfe";
        modal.no_footer = false;

        var rendered = modal.render();
        $("#main-content").append(rendered);
    }

    static abreModalNFE() {
        spinner(true);

        // verifica a senha antes
        const senha = $("#campo_senha").val();
        const num_nf = $("#campo_num_nf").val();

        if (senha == "") {
            var popup = new Alert();
            popup.typo = "danger";
            popup.texto = "Por favor, preencha a senha para liberação";
            popup.montaMensagem();
            spinner(false);
            return;
        }

        $.getJSON("json.php?pagina=liberacao&term=" + senha, "")
            .done(function (resposta) {
                if (resposta.valida) {
                    var content = $("#modal_nfe .modal-body");
                    var campo_chave = $("#campo_chave").val();
                    $(content).html("");

                    var tipo_evento = {
                        class: "",
                        description: "Tipo de evento",
                        function: "",
                        icon: "",
                        options: [
                            {
                                value: "110111",
                                description: "Cancelamento",
                                checked: "N"
                            },
                            {
                                value: "110110",
                                description: "Carta de correção",
                                checked: "N"
                            },
                            {
                                value: "999990",
                                description: "Inutilização",
                                checked: "N"
                            }
                        ],
                        name: "tipo_evento",
                        size: "3",
                        type: "SELECT",
                        value: ""
                    };

                    var sequencia = {
                        class: "",
                        description: "Sequência",
                        function: "",
                        options: [],
                        icon: "",
                        name: "num_sequencia",
                        size: "3",
                        type: "SELECT",
                        value: ""
                    };

                    var chave = {
                        class: "",
                        description: "Chave",
                        function: "",
                        icon: "",
                        name: "valor_chave",
                        size: "6",
                        type: "LABEL",
                        value: campo_chave
                    };

                    var motivo = {
                        class: "",
                        description: "Informe o motivo",
                        function: "",
                        icon: "",
                        name: "justificativa",
                        size: "12",
                        type: "AREA",
                        value: ""
                    };

                    var num_inicial = {
                        class: "",
                        description: "Nº inicial (inutilização)",
                        function: "",
                        icon: "",
                        name: "inut_num_inicial",
                        size: "6",
                        type: "TEXT",
                        value: num_nf
                    };

                    var num_final = {
                        class: "",
                        description: "Nº final (inutilização)",
                        function: "",
                        icon: "",
                        name: "campo_inut_num_final",
                        size: "6",
                        type: "TEXT",
                        value: num_nf
                    };

                    for (var i = 1; i <= 20; i++) {
                        sequencia.options.push({value: i, description: i, checked: "N"});
                    }

                    Field.select(content, tipo_evento);
                    Field.select(content, sequencia);
                    Field.input("label", content, chave);
                    Field.textArea(content, motivo);
                    Field.input("text", content, num_inicial);
                    Field.input("text", content, num_final);

                    $(content).find(".selectpicker").selectpicker();
                    $("#modal_nfe").modal('show');
                    spinner(false);
                } else {
                    var popup = new Alert();
                    popup.typo = "danger";
                    popup.texto = "Senha incorreta";
                    popup.montaMensagem();
                    spinner(false);
                }

            })
            .fail(function (jqxhr) {
                var popup = new Alert();
                popup.typo = "danger";
                popup.texto = "Não foi possível verificar a senha";
                popup.montaMensagem();
                spinner(false);
            });
    }

    static notas() {
        var xml = $(`#campo_xml`);
        $(`#campo_xml`).hide();
        $(`#wrapper_xml`).append(
            `<div class="col-12 mb-2" style="overflow-y:scroll;height:500px;width:100%;overflow-x:hidden">
            <xmp style="white-space: pre-wrap;">${xml.val()}</xmp>
        </div>`
        );

        var txt = $(`#campo_txt_cupom`);
        $(`#campo_txt_cupom`).hide();
        $(`#wrapper_txt_cupom`).append(
            `<div class="col-12" style="overflow:scroll;height:300px;width:100%;overflow:auto">
            <xmp>${txt.val()}</xmp>
        </div>`
        );

        var dados_retorno = $(`#campo_dados_retorno`);
        $(`#campo_dados_retorno`).hide();
        $(`#wrapper_dados_retorno`).append(
            `<div class="col-12" style="overflow:scroll;height:300px;width:100%;overflow:auto">
            <xmp>${dados_retorno.val()}</xmp>
        </div>`
        );

        $("#campo_senha").prop("type", "password");
    }
}