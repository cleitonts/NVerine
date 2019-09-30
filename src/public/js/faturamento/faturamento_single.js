// var locais
var produtos = [];
var comissao = [];
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

    // inicia modal para configuração de descontos
    Desconto.criaModal();
    Desconto.criaModalNFE();

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

    notas();
}

function abreModalNFE() {
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

                for (var i = 1; i <= 20; i++){
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
            }
            else {
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

function notas(){
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

class Duplicatas{
    static processa(){
        spinner(true);
        // limpa tabela antes de atualizar
        $("#dynamic_duplicatas [num]").not("[num='templatex']").remove();
        $("#dynamic_duplicatas #campo_dynamic_rows").val(0);

        var forma_pagamento = parseInt($("#campo_forma_pagamento").val());
        var condicao_pagamento = parseInt($("#campo_condicao_pagamento").val());

        // validações
        if (isNaN(forma_pagamento)) {
            var popup = new Alert();
            popup.typo = "danger";
            popup.texto = "Por favor, preencha a forma de pagamento";
            popup.montaMensagem();
            spinner(false);
            return;
        }

        if (isNaN(condicao_pagamento)) {
            var popup = new Alert();
            popup.typo = "danger";
            popup.texto = "Por favor, preencha a condição de pagamento";
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
            //this.id = this.getValor("cod_two_produto", "int");
            //this.valor_desconto = this.getValor("valor_desconto", "float");
        }

        // cria botão para abrir modal de percentagem
        Desconto.criaBotao($(this.linha).find("[id^='label_perc_desconto']"));


        // mascaras
        this.getCampo("NCM").mask("9999.99.99");
        this.getCampo("cst_icms").mask("999");

        // instancia changes obrigatorios
        // var selector = $(this.getCampo("valor_unitario"))
        //     .add(this.getCampo("perc_desconto"))
        //     .add(this.getCampo("valor_desconto"));
        //
        // Funcoes.newChange(selector);


        this.acoes();							// instancia as ações dos imputs
        this.pesquisa();						// instancia pesquisa para produtos

        this.atualizaValores(false);
        this.sobrePreco();
        //this.getMedidas();

        // instancia a classe de desconto
        //_desconto[this.num] = new DescontoItens(this.num);
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

        if(tipo == "int"){
            if(valor.includes(",")) {
                valor = valor.replace(".", "");
            }
            valor = valor.replace(",", ".");
            valor = parseInt(valor);
            if(isNaN(valor)) valor = 0;
        }
        else if(tipo == "str"){
            valor = String.valueOf(valor);
            if(!isNaN(valor)) valor = 0;
        }

        else if(tipo == "float"){
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


    // calcula a medida padrão e arredonda
    getMedidas (){
        spinner(true);

        var elemento = this;
        elemento.medidas_padrao = [];

        $.ajax({
            url: __PASTA__+"json.php?pagina=medidas_padrao",
            dataType: "json",
            data: {handle_produto: elemento.id}
        })
            .done(function(valores){
                if (valores != null) {
                    if(typeof (valores.medida_base ) != "undefined") {
                        elemento.medida_base = parseFloat(valores.medida_base);
                        // converte para um array que possua somente as medidas
                        for (var i = valores.itens.length - 1; i >= 0; i--) {
                            elemento.medidas_padrao.push(valores.itens[i].medidas);
                        }
                    }
                }
                spinner(false);
            })
            .fail(function(){
                var popup = new Alert();
                popup.typo = "danger";
                popup.texto = "Medidas não encontradas";
                popup.montaMensagem();
                spinner(false);
            });
    }

    // Traz a lista completa com todos os detalhes do produto
    atualiza (){
        spinner(true);

        // o ajax nao trabalha bem com THIS
        var elemento = this;

        // carrega as medidas padrões
        elemento.getMedidas();
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

    // Calcula a metragem total do produto
    calculaMetragem (){
        // medida arredondada é o comprimento
        var index;
        var medida_arredondar_x;
        var medida_arredondar_z;

        if(this.emenda == "N"){
            // itera tabela de medidas padrão
            if(this.unidade == "M" || this.unidade == "M2"){

                // verifica a medida z
                for (var i = this.medidas_padrao.length - 1; i >= 0; i--) {
                    if(parseInt(this.medidas_padrao[i]) >= this.medida_z){
                        index = i;
                        break;
                    }
                }

                // salva valor
                if(!isNaN(index)) {
                    medida_arredondar_z = parseFloat(this.medidas_padrao[index]);
                    index = null;
                }

                // verifica a medida x
                for (var i = this.medidas_padrao.length - 1; i >= 0; i--) {
                    if(parseInt(this.medidas_padrao[i]) >= this.medida_x){
                        index = i;
                        break;
                    }
                }

                if(!isNaN(index)){
                    medida_arredondar_x = parseFloat(this.medidas_padrao[index]);
                    index = null;
                }
            }
        }

        // verifica se arredonda medida x
        if((medida_arredondar_x > 0) && ((medida_arredondar_x - this.medida_x) <= (medida_arredondar_z - this.medida_z) || isNaN(medida_arredondar_z))){     // isto assusta
            this.arredonda_x = medida_arredondar_x;
            this.arredonda_z = this.medida_z;
        }

        // verifica se arredonda medida z
        else if((medida_arredondar_z > 0) && ((medida_arredondar_z - this.medida_z) <= (medida_arredondar_x - this.medida_x) || isNaN(medida_arredondar_x))){
            this.arredonda_x = this.medida_x;
            this.arredonda_z = medida_arredondar_z;
        }

        // se chegou aqui é pq as medidas estão zeradas
        else{
            this.arredonda_x = this.medida_x;
            this.arredonda_z = this.medida_z;
        }

        // calcula arredondamenoto de acordo com unidade de medida
        if(this.unidade == "M2") {
            this.medida_t = (this.arredonda_x * this.arredonda_z * this.quantidade) / 1000000;
        }
        else if(this.unidade == "M") {
            this.medida_t = (this.arredonda_z * this.quantidade) / 1000;
        }
        else {
            this.valor_bruto = (this.quantidade * this.valor_unitario);
        }
        //para não causar erro de JS caso produto não use medidas
        if(this.medida_t > 0){
            this.setValor("medida_t", this.medida_t.toFixed(5));
            this.valor_bruto = (this.medida_t * this.valor_unitario);
        }

        //se o produto for m2 ou m ele vai pegar os valores diretamente acima, senão ele pega o valor * quantidade
        if(isNaN(this.valor_bruto) || this.valor_bruto == 0)	{
            this.valor_bruto = (this.quantidade * this.valor_unitario);
        }
    }

    /*	Atualiza valores da linha do produto
     * 	calcula varias coisas, como valor total entre outros..
     * 	Desmembrar a função de arredondamento, pois assim a performance vai melhorar consideravelmente
     */
    atualizaValores (salvar = true){
        this.unidade = this.getValor("unidade");
        this.medida_x = this.getValor("medida_x", "int");
        this.medida_z = this.getValor("medida_z", "int");
        this.quantidade = this.getValor("quantidade", "float");
        this.valor_tabela = this.getValor("valor_tabela", "float");
        this.valor_unitario = this.getValor("valor_unitario", "float");
        this.perc_ipi = this.getValor("perc_ipi", "float");
        this.perc_icms = this.getValor("perc_icms", "float");
        this.fator_bc_icms = this.getValor("fator_bc", "float");
        this.margem_valor_agregado = this.getValor("mva", "float");
        this.valor_frete = this.getValor("valor_frete", "float");
        this.emenda = this.getValor("emenda");
        // por default o valor sera N
        if(this.emenda == 0) this.emenda = "N";

        // seta apenas se for nulo
        if(isNaN(parseInt(this.valor_desconto))){
            this.valor_desconto = this.getValor("valor_desconto", "float");
            this.perc_desconto = this.getValor("perc_desconto", "float");
        }

        this.calculaMetragem();

        this.valor_base = 		this.valor_bruto - this.valor_desconto;
        this.valor_bc_ipi = 	this.valor_base; // IPI não considera desconto?
        this.valor_bc_icms = 	this.valor_base * this.fator_bc_icms; // processar o % base de cálculo do tipo de operação!
        this.valor_ipi = 		this.perc_ipi * (this.valor_bc_ipi / 100);
        this.valor_icms = 		this.perc_icms * (this.valor_bc_icms / 100);

        // zera base de cálculo se não houver alíquota
        if(this.perc_ipi == 0) this.valor_bc_ipi = 0;
        if(this.perc_icms == 0) this.valor_bc_icms = 0;

        // cálculo ICMS ST
        this.valor_bc_icms_st = 0;
        this.valor_icms_st = 0;

        if(this.getValor("usa_st", "str") == "S") {
            /* deve tratar por tipo de modalidade (MVA, pauta, tabelado, etc.)
             * por enquanto SÓ implementamos o cálculo do MVA!
             */
            this.valor_bc_icms_st = this.valor_base + this.valor_ipi + this.valor_frete;
            this.valor_bc_icms_st *= 1 + (this.margem_valor_agregado / 100);

            var diferenca = this.valor_bc_icms_st - this.valor_bc_icms;
            this.valor_icms_st = (diferenca / 100) * this.perc_icms;
        }

        this.valor_total = this.valor_bruto - this.valor_desconto + this.valor_ipi + this.valor_icms_st;

        // zera base de cálculo se não houver alíquota
        if(this.perc_ipi == 0) this.valor_bc_ipi = 0;
        if(this.perc_icms == 0) this.valor_bc_icms = 0;

        this.comissaoProduto();

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
        $("#label_perc_desconto_"+this.num+" .btn").text(this.perc_desconto);
        maskMoney($("#campo_valor_desconto_"+this.num), this.valor_desconto);
        maskMoney($("#label_valor_desconto_"+this.num), this.valor_desconto);
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
        maskMoney($("#campo_valor_bc_st_icms_"+this.num), this.valor_bc_icms_st);
        maskMoney($("#campo_valor_st_icms_"+this.num), this.valor_icms_st);
    }

    //	Bloqueia alterações no campo de valor unitario para valores abaixo de tabela
    valorUnitario (){
        this.valor_unitario = this.getValor("valor_unitario");

        if(this.valor_tabela >= this.valor_unitario){
            $("#campo_valor_unitario_"+this.num).val(this.valor_tabela.toFixed(__CASAS_DECIMAIS__)).mask("#.##0,00", {reverse: true}).trigger('input');
            this.sobrepreco = 0;
        }
        else{
            if(!this.desconto_sobrepreco()){
                this.valor_unitario = this.valor_tabela;
                $("#campo_valor_unitario_"+this.num).val(this.valor_tabela.toFixed(__CASAS_DECIMAIS__)).mask("#.##0,00", {reverse: true}).trigger('input');
                this.sobrepreco = 0;
                return -1;
            }
            this.sobrePreco();
        }
        this.atualizaValores();
    }

    // verifica se tem desconto e sobrepreço na mesma linha
    desconto_sobrepreco(desconto = false){

        // se valor da tabela estiver zerado, não tem bloqueio
        if(this.valor_tabela == 0){
            return true;
        }

        this.valor_unitario = this.getValor("valor_unitario", "float");
        var sobrepreco = this.valor_unitario - this.valor_tabela;   // se for maior que 0 tem sobrepreço

        if(!desconto){
            desconto = this.getValor("valor_desconto", "float");
        }

        if(sobrepreco > 0 && desconto > 0){
            var popup = new Alert();
            popup.typo = "danger";
            popup.texto = "Não é possivel aplicar desconto em um produto que possui sobrepreço";
            popup.montaMensagem();

            return false;
        }

        return true;
    }

    // Atualiza o valor da comissão na linha do produto
    comissaoProduto(){
        var elemento = this;
        //ate o momento a formula usada é sinonimo de: comissao = (valor_tabela * ((10 - (perc_desconto * __COMISSAO_FATOR__)) * (__PERC_COMISSAO__ / 10))) * quantidade;
        if(elemento.perc_desconto > 0){
            elemento.comissao = elemento.valor_base * ((10 - (elemento.perc_desconto * __COMISSAO_FATOR__)) / 100);
        }
        else{
            elemento.comissao = elemento.valor_base * __COMISSAO_PERC__;
        }
        if(elemento.comissao < 0) elemento.comissao = 0;

        elemento.setValor("comissao", parseFloat(elemento.comissao).toFixed(__CASAS_DECIMAIS__));
    }

    // esta função calcula o sobrePreco de cada produto individualmente e salva em um campo invisível
    sobrePreco (){
        this.sobrepreco = 0;

        if(this.medida_t > 0){
            this.sobrepreco = ((this.valor_unitario - this.valor_tabela) * this.medida_t);
        }
        else {
            this.sobrepreco = ((this.valor_unitario - this.valor_tabela) * this.quantidade);
        }

        this.setValor("sobrepreco", this.sobrepreco.toFixed(__CASAS_DECIMAIS__))
    }

    acoes(){
        const elemento = this;

        // executa bloqueio caso o valor unitario seja editado
        $(this.getCampo("valor_unitario")).blur(function(){
            elemento.valorUnitario();
        });

        var selector = $(this.getCampo("quantidade")).add(this.getCampo("medida_x"));
        $(selector).change(() => {elemento.estoque_saldo()});

        selector = $(this.getCampo("quantidade"))
            .add(this.getCampo("medida_x"))
            .add(this.getCampo("medida_z"))
            .add(this.getCampo("emenda"))
            .add(this.getCampo("valor"))
            .add($("[id^='campo_perc']:not([id^='campo_perc_desconto'])"));	// todos os percentuais serao atualizados

        $(selector).change(function() {
            elemento.atualizaValores();
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
class Nota{
    constructor (){
        var elemento = this;
        // atualização de totais caso troque o vendedor
        $("#campo_vendedor, #campo_supervisor").change(function(){
            elemento.totalProdutos();
        });

        // precisa estar carregado com o valor
        this.comissao = this.somaValor("campo_prod_comissao");
        this.sobrepreco = this.somaValor("campo_prod_sobrepreco");

        // 1 => comissao padrao
        // 2 => supervisor
        // 3 => sobrepreço
        // atualiza a tabela de comissão
        $("#campo_supervisor").change(function(){
            comissao[2].atualizaPessoa(this.value);	// supervisor
        });
        $("#campo_vendedor").change(function(){
            comissao[1].atualizaPessoa(this.value);	// comissao normal
            comissao[3].atualizaPessoa(this.value);	// sobrepreço
        });

        // seta o tipo
        comissao.push(new ComissaoItens(1));
        comissao.push(new ComissaoItens(2));
        comissao.push(new ComissaoItens(3));
    }

    totalProdutos (){
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
        $("#dynamic_duplicatas [id^='campo_valor_total']").each(function() {
            var valor = $(this).val();

            if(valor.includes(",")) {
                valor = valor.replace(".", "");
            }
            valor = valor.replace(",", ".");
            valor = parseFloat(valor);
            if(isNaN(valor)) valor = 0;
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

        Nota.rateio_comissao();
    }

    // soma os totais dos produtos
    somaValor(id){
        var total = 0;

        $("#dynamic_produtos [id^='"+id+"']").each(function() {
            var valor = $(this).val();

            if(valor.includes(",")) {
                valor = valor.replace(".", "");
            }
            valor = valor.replace(",", ".");
            valor = parseFloat(valor);
            if(isNaN(valor)) valor = 0;
            total += valor;
        });

        return total;
    }

    // igual a anterior, mas set o valor
    static setTotal(campo, valor){
        let table = $("#dynamic_totais");
        $(table).find("[id^='campo_"+campo+"']").val(valor.toFixed(__CASAS_DECIMAIS__));
    }

    static rateio_comissao (){
        // atualiza comissao base do vendedor
        for(var i = comissao.length -1; i > 0; i--){
            comissao[i].atualiza();
        }
    }
}

// linhas de comissão
class ComissaoItens{
    constructor (tipo){
        // seta o tipo
        this.tipo = tipo;
        this.obj = $("table#comissao tbody tr:nth-child("+this.tipo+")");
        this.obj.find("[id^='campo_comissao_nome']").val(this.getNomeTipo());
        this.cod_pessoa = this.obj.find("[id^='campo_comissao_cod_pessoa']").val();

        // se estiver vazio, precisa pesquisar novamente
        if(isNaN(parseInt(this.cod_pessoa))){
            if(tipo == 2){
                this.atualizaPessoa($("#campo_supervisor").val());
            }
            else{
                this.atualizaPessoa($("#campo_vendedor").val());
            }
        }
        else{
            this.perc_comissao = this.obj.find("[id^='campo_perc_comissao']").val();
            this.nome_pessoa = this.obj.find("[id^='campo_comissao_pessoa']").val();
        }
    }
    atualiza (){
        if(isNaN(this.perc_comissao) && this.tipo == 2){
            this.perc_comissao = 0;
        }

        else if(isNaN(this.perc_comissao) && this.tipo == 1){
            this.perc_comissao = 100;
        }

        if(this.tipo == 3){
            this.base = nota.sobrepreco;
            this.valor = nota.sobrepreco * 0.3;
            this.perc_comissao = 30;
        }
        else{
            // diferenciação para sobrepreço
            this.base = parseFloat(nota.comissao).toFixed(2);
            this.valor = this.base * (this.perc_comissao / 100);

        }

        this.obj.find("[id^='campo_comissao_origem']").val(this.tipo);
        this.obj.find("[id^='campo_comissao_pessoa']").val(this.nome_pessoa);
        this.obj.find("[id^='campo_comissao_cod_pessoa']").val(this.cod_pessoa);
        this.obj.find("[id^='campo_comissao_valor_base']").val(parseFloat(this.base).toFixed(2));
        this.obj.find("[id^='campo_comissao_nome']").val(this.getNomeTipo());
        this.obj.find("[id^='campo_perc_comissao']").val(parseFloat(this.perc_comissao).toFixed(2));
        this.obj.find("[id^='campo_comissao_valor_total']").val(parseFloat(this.valor).toFixed(2));
    }

    // atualiza de usuarios para pessoas
    atualizaPessoa (id){
        spinner(true);

        var itens = this;
        $.ajax({
            url: "json.php?pagina=comissao",
            dataType: "json",
            data: {	term: id }
        })
            .done(function(valores){
                itens.nome_pessoa = valores.pessoa;
                itens.cod_pessoa = valores.codPessoa;
                itens.perc_comissao = parseFloat(valores.comissao) * 100; // corrige decimais

                // esta atualização deve estar aqui dentro
                comissao[1].perc_comissao = 100 - comissao[2].perc_comissao;
                Nota.rateio_comissao();
                spinner(false);
            })
            .fail(function(){
                spinner(false);
                var popup = new Alert();
                popup.typo = "danger";
                popup.texto = "Preencha os campos Supervisor/Vendedor com informações válidas";
                popup.montaMensagem();
            });
    }

    getNomeTipo (){
        switch(this.tipo) {
            case 1:
                return "Vendedor";
            case 2:
                return "Sup/indic";
            case 3:
                return "Sobrepreço";
            default:
                return "Está errado"
        }
    }
}

class Desconto {
    static criaModal(){
        var modal = new Modal();
        modal.title = "Desconto unitário";
        modal.btnSuccess = "Aplicar";
        modal.btnCancel = "Cancelar";
        modal.size = ""; // "modal-lg, modal-sm";
        modal.btnSuccessAct = function() {Desconto.verifica()}; //function() {FieldTables.deleteLinha(modal)};
        modal.btnCancelAct = ""; //function() {FieldTables.deleteLinha(modal)};
        modal.name = "modal_desconto";
        modal.no_footer = false;

        var rendered = modal.render();
        $("#main-content").append(rendered);
    }

    static criaModalNFE(){
        var modal = new Modal();
        modal.title = "Novo evento de nota fiscal";
        modal.btnSuccess = "Enviar Evento";
        modal.size = "modal-lg"; // "modal-lg, modal-sm";
        modal.btnSuccessAct = function() {Desconto.verifica()}; //function() {FieldTables.deleteLinha(modal)};
        modal.btnCancelAct = ""; //function() {FieldTables.deleteLinha(modal)};
        modal.name = "modal_nfe";
        modal.no_footer = false;

        var rendered = modal.render();
        $("#main-content").append(rendered);
    }

    static abreModal(obj){
        var num = $(obj).closest("[num]").attr("num");
        var content = $("#modal_desconto .modal-body");

        // checa primeiro
        if(!produtos[num -1].desconto_sobrepreco(1)){
            return -1;
        }

        $(content).html("");

        var total = {   class: "",
            description: "Valor bruto",
            function: "",
            icon: "",
            name: "desc_valor_bruto",
            size: "12",
            type: "LABEL",
            value: parseFloat(produtos[num -1].valor_bruto).toFixed(__CASAS_DECIMAIS__)
        };

        var valor = {   class: "",
            description: "Valor desconto",
            function: "",
            icon: "",
            name: "desc_valor_desconto",
            size: "12",
            type: "TEXT",
            value: parseFloat(produtos[num -1].valor_desconto).toFixed(__CASAS_DECIMAIS__)
        };

        var desconto = {    class: "",
            description: "Perc. desconto",
            function: "",
            icon: "",
            name: "desc_perc_desconto",
            size: "12",
            type: "TEXT",
            value: parseFloat(produtos[num -1].perc_desconto).toFixed(4)
        };

        var referencia = {      class: "",
            description: "",
            function: "",
            icon: "",
            name: "desc_referencia",
            size: "12",
            type: "HIDDEN",
            value: num
        };

        var senha = {   class: "",
            description: "Senha para liberação (mais de "+ (__MAX_DESCONTO_VENDA__  * 100) +"%)",
            name: "senha_mestra",
            function: "",
            icon: "",
            size: "12",
            type: "PASSWORD",
            value: ""
        };

        Field.input("label", content, total);
        Field.input("text", content, valor);
        Field.input("text", content, desconto);
        Field.input("hidden", content, referencia);

        // so mostra botão quando existir o bloqueio
        if(__MAX_DESCONTO_VENDA__ < 1) {
            Field.input("text", content, senha);
        }

        $(content).find("#campo_desc_valor_desconto").keyup(function(){Desconto.calcPerc(this)})
            .mask("#.##0,00", {reverse: true});
        $(content).find("#campo_desc_valor_bruto").mask("#.##0,00", {reverse: true});
        $(content).find("#campo_desc_perc_desconto").keyup(function(){Desconto.calcVal(this)})
            .mask('00,0000%', {reverse: true});

        $("#modal_desconto").modal('show');
    }

    static calcPerc(obj){
        var bruto = parseFloat($("#campo_desc_valor_bruto").val().replace(',', '.'));
        var valor = parseFloat($(obj).val().replace(',', '.'));
        var masked = $("#campo_desc_perc_desconto").masked((valor / bruto * 100).toFixed(4));

        $("#campo_desc_perc_desconto").val(masked)
            .closest(".form-group").addClass("is-filled");
    }

    static calcVal(obj){
        var bruto = parseFloat($("#campo_desc_valor_bruto").val().replace(',', '.'));
        var perc = parseFloat($(obj).val().replace(',', '.'));
        var masked = $("#campo_desc_valor_desconto").masked((bruto * (perc / 100)).toFixed(2));

        $("#campo_desc_valor_desconto").val(masked)
            .closest(".form-group").addClass("is-filled");
    }

    static verifica(){
        var perc = parseFloat($("#campo_desc_perc_desconto").val().replace(',', '.'));
        spinner(true);

        // faz a liberação
        if(perc > (__MAX_DESCONTO_VENDA__ * 100)){
            var senha = $("#campo_senha_mestra").val();
            if(senha.length == 0){
                var popup = new Alert();
                popup.typo = "danger";
                popup.texto = "Campo de senha não preenchido.";
                popup.montaMensagem();
                spinner(false);
            }
            else{
                $.getJSON(__PASTA__+"json.php?pagina=liberacao&term="+senha, "")
                    .done(function(resposta){
                        if(resposta.valida){
                            $("#modal_desconto").modal('hide');
                            Desconto.aplica();
                            spinner(false);
                        }
                        else{
                            var popup = new Alert();
                            popup.typo = "danger";
                            popup.texto = "Senha incorreta.";
                            popup.montaMensagem();
                            spinner(false);
                        }
                    })
                    .fail(function(jqxhr){
                        var popup = new Alert();
                        popup.typo = "danger";
                        popup.texto = "Campo de senha não preenchido.";
                        popup.montaMensagem();
                        spinner(false);
                    });
            }
        }
        else{
            $("#modal_desconto").modal('hide');
            Desconto.aplica();
            spinner(false);
        }
    }

    static criaBotao(obj){
        var texto = $(obj).text();

        var btn = document.createElement("div");
        btn.className = "btn btn-warning btn-sm btn-block";
        btn.onclick = function(){
            Desconto.abreModal(this);
        };
        btn.appendChild(document.createTextNode(texto));

        $(obj).html(btn);
    }

    static aplica(){
        var num = $("#campo_desc_referencia").val();

        var perc = $("#campo_desc_perc_desconto").val();
        perc = perc.replace(".", "");
        perc = perc.replace(",", ".");
        perc = parseFloat(perc);

        var bruto = $("#campo_desc_valor_bruto").val();
        bruto = bruto.replace(".", "");
        bruto = bruto.replace(",", ".");
        bruto = parseFloat(bruto);

        var valor = (bruto * (perc / 100));

        produtos[num-1].perc_desconto = perc;
        produtos[num-1].valor_desconto = valor;
        produtos[num-1].atualizaValores(true);

        // $("#label_perc_desconto_"+num+" .btn").text(perc);
        // $("#campo_perc_desconto_"+num).val(perc);
        // $("#label_valor_desconto_"+num).text(valor.toFixed(__CASAS_DECIMAIS__)).mask("#.##0,00", {reverse: true}).trigger('input');
        // $("#campo_valor_desconto_"+num).val(valor.toFixed(__CASAS_DECIMAIS__)).mask("#.##0,00", {reverse: true}).trigger('input');
    }
}
