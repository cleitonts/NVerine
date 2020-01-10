START TRANSACTION;

ALTER TABLE K_FN_PESSOAVINCULO ADD CONSTRAINT FK_4328_48387 FOREIGN KEY (PAI) REFERENCES K_FN_PESSOA(HANDLE);

ALTER TABLE K_FN_PESSOAVINCULO ADD CONSTRAINT FK_4328_48419 FOREIGN KEY (TIPOVINCULO) REFERENCES K_FN_TIPOVINCULO(HANDLE);

ALTER TABLE K_FN_PESSOAVINCULO ADD CONSTRAINT FK_4328_48388 FOREIGN KEY (FILHO) REFERENCES K_FN_PESSOA(HANDLE);

CREATE UNIQUE INDEX PK__K_FN_PES__D1CC59CE13301214 ON K_FN_PESSOAVINCULO(HANDLE);

CREATE UNIQUE INDEX PK__K_FN_TIP__D1CC59CE101E9B3F ON K_FN_TIPOVINCULO(HANDLE);

ALTER TABLE CP_CONDICOESPAGAMENTO ADD CONSTRAINT FK_1479_42580 FOREIGN KEY (FORMAPAGAMENTO) REFERENCES FN_FORMASPAGAMENTO(HANDLE);

CREATE UNIQUE INDEX PK__CP_CONDI__D1CC59CE0B20E345 ON CP_CONDICOESPAGAMENTO(HANDLE);

ALTER TABLE CP_MAPACONDICAOPAGAMENTO ADD CONSTRAINT FK_1480_42581 FOREIGN KEY (FORMAPAGAMENTO) REFERENCES FN_FORMASPAGAMENTO(HANDLE);
            
ALTER TABLE CP_MAPACONDICAOPAGAMENTO ADD CONSTRAINT FK_1480_12028 FOREIGN KEY (CONDICOESPAGAMENTO) REFERENCES CP_CONDICOESPAGAMENTO(HANDLE);
            
CREATE UNIQUE INDEX PK__CP_MAPAC__D1CC59CE3BC41AA0 ON CP_MAPACONDICAOPAGAMENTO(HANDLE);

CREATE UNIQUE INDEX PK__FN_FORMA__D1CC59CE2DD5EDFC ON FN_FORMASPAGAMENTO(HANDLE);

CREATE UNIQUE INDEX AX_759_12351 ON FN_FORMASPAGAMENTO(HANDLE);

CREATE UNIQUE INDEX PK__K_FN_ALM__D1CC59CE47C901B1 ON K_FN_ALMOXARIFADO(HANDLE);

ALTER TABLE K_FN_AREA ADD CONSTRAINT FK_4152_47339 FOREIGN KEY (FILIAL) REFERENCES K_FN_FILIAL(HANDLE);

CREATE UNIQUE INDEX PK__K_FN_ARE__D1CC59CE291C25D7 ON K_FN_AREA(HANDLE);

CREATE UNIQUE INDEX PK__K_FN_CFO__D1CC59CE238BA13B ON K_FN_CFOP(HANDLE);

CREATE UNIQUE INDEX AX_4228_12344 ON K_FN_CFOP(HANDLE);

ALTER TABLE K_FN_CONTATO ADD CONSTRAINT FK_4269_47820 FOREIGN KEY (PESSOA) REFERENCES K_FN_PESSOA(HANDLE);

CREATE UNIQUE INDEX PK__K_FN_CON__D1CC59CE64DF377F ON K_FN_CONTATO(HANDLE);

CREATE UNIQUE INDEX PK__K_FN_CST__D1CC59CE376A452E ON K_FN_CST_ORIGEM(HANDLE);

CREATE UNIQUE INDEX PK__K_FN_CST__D1CC59CE3A46B1D9 ON K_FN_CST_TRIBUTACAO(HANDLE);

CREATE UNIQUE INDEX PK__K_FN_END__D1CC59CE3FFF8B2F ON K_FN_ENDERECO(HANDLE);

CREATE UNIQUE INDEX AX_4221_12361 ON K_FN_FILIAL(HANDLE);

CREATE UNIQUE INDEX PK__K_FN_FIL__D1CC59CE42DBF7DA ON K_FN_FILIAL(HANDLE);

CREATE UNIQUE INDEX PK__K_FN_GRU__D1CC59CE3A6F0693 ON K_FN_GRUPOUSUARIO(HANDLE);

CREATE UNIQUE INDEX PK__K_FN_LIS__D1CC59CE2EFD53E7 ON K_FN_LISTAPRECOS(HANDLE);

CREATE UNIQUE INDEX PK__K_FN_PER__D1CC59CE369E75AF ON K_FN_PERMISSOES(HANDLE);

CREATE UNIQUE INDEX AX_4237_12338 ON K_FN_PESSOA(HANDLE);

CREATE UNIQUE INDEX PK__K_FN_PES__D1CC59CE4210285B ON K_FN_PESSOA(HANDLE);

CREATE UNIQUE INDEX PK__K_FN_PES__D1CC59CE68AFC863 ON K_FN_PESSOAENDERECO(HANDLE);

CREATE UNIQUE INDEX PK__K_FN_PRO__D1CC59CE59BF5D32 ON K_FN_PRODUTOENDERECO(HANDLE);

CREATE UNIQUE INDEX PK__K_FN_PRO__D1CC59CE51526BEB ON K_FN_PRODUTOESTRUTURADO(HANDLE);

CREATE UNIQUE INDEX PK__K_FN_TAB__D1CC59CE2B2CC303 ON K_FN_TABELAPRECOS(HANDLE);

CREATE UNIQUE INDEX PK__K_FN_TAR__D1CC59CE6254A333 ON K_FN_TARIFAS(HANDLE);

ALTER TABLE K_FN_USUARIOFILIAL ADD CONSTRAINT FK_4222_47341 FOREIGN KEY (USUARIO) REFERENCES K_PD_USUARIOS(HANDLE);

CREATE UNIQUE INDEX PK__K_FN_USU__D1CC59CE70A2C28A ON K_FN_USUARIOFILIAL(HANDLE);

ALTER TABLE K_LOG ADD CONSTRAINT FK_4311_48154 FOREIGN KEY (USUARIO) REFERENCES K_PD_USUARIOS(HANDLE);
            
ALTER TABLE K_LOG ADD CONSTRAINT FK_4311_48156 FOREIGN KEY (FILIAL) REFERENCES K_FN_FILIAL(HANDLE);
            
CREATE UNIQUE INDEX PK__K_LOG__D1CC59CE45F09C0B ON K_LOG(HANDLE);

CREATE UNIQUE INDEX PK__K_PD_ALC__D1CC59CE01CD4E8C ON K_PD_ALCADAS(HANDLE);

ALTER TABLE K_PD_USUARIOS ADD CONSTRAINT FK_4100_46197 FOREIGN KEY (CLIENTE) REFERENCES K_FN_PESSOA(HANDLE);
            
CREATE UNIQUE INDEX PK__K_PD_USU__D1CC59CE46777E94 ON K_PD_USUARIOS(HANDLE);

CREATE UNIQUE INDEX AX_4100_12339 ON K_PD_USUARIOS(HANDLE);

ALTER TABLE PD_FAMILIASPRODUTOS ADD CONSTRAINT FK_879_20615 FOREIGN KEY (CLASSIFICACAOTIPI) REFERENCES TR_TIPIS(HANDLE);
            
CREATE UNIQUE INDEX AX_879_754 ON PD_FAMILIASPRODUTOS (EMPRESA, CODIGO);

CREATE UNIQUE INDEX AX_879_426 ON PD_FAMILIASPRODUTOS (EMPRESA, FAMILIA);
        
CREATE UNIQUE INDEX PK__PD_FAMIL__D1CC59CE0E3E1269 ON PD_FAMILIASPRODUTOS(HANDLE);

ALTER TABLE PD_PRODUTOS ADD CONSTRAINT FK_807_5671 FOREIGN KEY (CLASSIFICACAOTIPI) REFERENCES TR_TIPIS(HANDLE);

CREATE UNIQUE INDEX PK__PD_PRODU__D1CC59CE3CC3F728 ON PD_PRODUTOS(HANDLE);

CREATE UNIQUE INDEX AX_807_690 ON PD_PRODUTOS (FILIAL, CODIGO);

CREATE UNIQUE INDEX AX_807_917 ON PD_PRODUTOS (FILIAL, CODIGOREFERENCIA);
        
CREATE UNIQUE INDEX PK__TR_TIPIS__D1CC59CE346EA849 ON TR_TIPIS(HANDLE);

ALTER TABLE ESTADOS ADD CONSTRAINT FK_714_5056 FOREIGN KEY (PAIS) REFERENCES PAISES(HANDLE);

CREATE UNIQUE INDEX PK__ESTADOS__D1CC59CE39E6BF26 ON ESTADOS(HANDLE);

ALTER TABLE MUNICIPIOS ADD CONSTRAINT FK_715_5061 FOREIGN KEY (ESTADO) REFERENCES ESTADOS(HANDLE);
            
ALTER TABLE MUNICIPIOS ADD CONSTRAINT FK_715_5060 FOREIGN KEY (PAIS) REFERENCES PAISES(HANDLE);
            
CREATE UNIQUE INDEX PK__MUNICIPI__D1CC59CE62949A55 ON MUNICIPIOS(HANDLE);

CREATE UNIQUE INDEX PK__PAISES__D1CC59CE52292862 ON PAISES(HANDLE);

CREATE UNIQUE INDEX PK__CM_UNIDA__D1CC59CE3C63391E ON CM_UNIDADESMEDIDA(HANDLE);

CREATE UNIQUE INDEX AX_813_682 ON CM_UNIDADESMEDIDA(ABREVIATURA);

ALTER TABLE K_FN_PERMISSOES ADD CONSTRAINT FK_PERMISSOES_1 FOREIGN KEY (GRUPO) REFERENCES K_FN_GRUPOUSUARIO(HANDLE);
            
ALTER TABLE K_FN_PERMISSOES ADD CONSTRAINT FK_PERMISSOES_2 FOREIGN KEY (ALCADA) REFERENCES K_PD_ALCADAS(HANDLE);
            
ALTER TABLE K_FN_USUARIOFILIAL ADD CONSTRAINT FK_USUARIOFILIAL_1 FOREIGN KEY (USUARIO) REFERENCES K_PD_USUARIOS(HANDLE);
            
ALTER TABLE K_FN_USUARIOFILIAL ADD CONSTRAINT FK_USUARIOFILIAL_2 FOREIGN KEY (FILIAL) REFERENCES K_FN_FILIAL(HANDLE);
            
ALTER TABLE K_GALERIA ADD CONSTRAINT FK_GALERIA_1 FOREIGN KEY (PRODUTO) REFERENCES PD_PRODUTOS(HANDLE);

ALTER TABLE K_CONTAS ADD CONSTRAINT FK_CONTAS_1 FOREIGN KEY (FILIAL) REFERENCES K_FN_FILIAL(HANDLE);

ALTER TABLE K_CONTAS ADD CONSTRAINT FK_CONTAS_2 FOREIGN KEY (PAI) REFERENCES K_CONTAS(HANDLE);

ALTER TABLE K_CONTASSALDOS ADD CONSTRAINT FK_CONTASSALDOS_1 FOREIGN KEY (CONTA) REFERENCES K_CONTAS(HANDLE);

ALTER TABLE K_CONTASSALDOS ADD CONSTRAINT FK_CONTASSALDOS_2 FOREIGN KEY (FILIAL) REFERENCES K_FN_FILIAL(HANDLE);

ALTER TABLE K_LANCAMENTOS ADD CONSTRAINT FK_LANCAMENTOS_1 FOREIGN KEY (FILIAL) REFERENCES K_FN_FILIAL(HANDLE);

ALTER TABLE K_LANCAMENTOS ADD CONSTRAINT FK_LANCAMENTOS_3 FOREIGN KEY (CONTA) REFERENCES K_CONTAS(HANDLE);

ALTER TABLE K_CRM_ARQUIVOS ADD CONSTRAINT FK_4339_48315 FOREIGN KEY (NEGOCIACAO) REFERENCES K_CRM_NEGOCIACOES(HANDLE);

CREATE UNIQUE INDEX PK__K_CRM_AR__D1CC59CE4002B88B ON K_CRM_ARQUIVOS(HANDLE);

ALTER TABLE K_CRM_ATUALIZACOES ADD CONSTRAINT FK_4337_48314 FOREIGN KEY (NEGOCIACAO) REFERENCES K_CRM_NEGOCIACOES(HANDLE);

ALTER TABLE K_CRM_ATUALIZACOES ADD CONSTRAINT FK_4337_48323 FOREIGN KEY (USUARIO) REFERENCES K_PD_USUARIOS(HANDLE);

CREATE UNIQUE INDEX PK__K_CRM_AT__D1CC59CE386196C3 ON K_CRM_ATUALIZACOES(HANDLE);

CREATE UNIQUE INDEX PK__K_CRM_CA__D1CC59CE254EC24F ON K_CRM_CAMPANHAS(HANDLE);

CREATE UNIQUE INDEX PK__K_CRM_ET__D1CC59CE2CEFE417 ON K_CRM_ETAPAS(HANDLE);

CREATE UNIQUE INDEX PK__K_CRM_FO__D1CC59CE217E316B ON K_CRM_FONTES(HANDLE);

ALTER TABLE K_CRM_NEGOCIACAOITENS ADD CONSTRAINT FK_4342_48339 FOREIGN KEY (PRODUTO) REFERENCES PD_PRODUTOS(HANDLE);

ALTER TABLE K_CRM_NEGOCIACAOITENS ADD CONSTRAINT FK_4342_48353 FOREIGN KEY (NEGOCIACAO) REFERENCES K_CRM_NEGOCIACOES(HANDLE);

CREATE UNIQUE INDEX PK__K_CRM_NE__D1CC59CE5D931B72 ON K_CRM_NEGOCIACAOITENS(HANDLE);

ALTER TABLE K_CRM_NEGOCIACOES ADD CONSTRAINT FK_4335_48321 FOREIGN KEY (PROJETO) REFERENCES K_CRM_PROJETOS(HANDLE);

ALTER TABLE K_CRM_NEGOCIACOES ADD CONSTRAINT FK_4335_48358 FOREIGN KEY (PROCESSO) REFERENCES K_CRM_PROCESSOS(HANDLE);

ALTER TABLE K_CRM_NEGOCIACOES ADD CONSTRAINT FK_4335_48332 FOREIGN KEY (RESPONSAVEL) REFERENCES K_PD_USUARIOS(HANDLE);

ALTER TABLE K_CRM_NEGOCIACOES ADD CONSTRAINT FK_4335_48301 FOREIGN KEY (PESSOA) REFERENCES K_FN_PESSOA(HANDLE);

ALTER TABLE K_CRM_NEGOCIACOES ADD CONSTRAINT FK_4335_48299 FOREIGN KEY (CAMPANHA) REFERENCES K_CRM_CAMPANHAS(HANDLE);

ALTER TABLE K_CRM_NEGOCIACOES ADD CONSTRAINT FK_4335_48328 FOREIGN KEY (USUARIOINCLUIU) REFERENCES K_PD_USUARIOS(HANDLE);

ALTER TABLE K_CRM_NEGOCIACOES ADD CONSTRAINT FK_4335_48325 FOREIGN KEY (FILIAL) REFERENCES K_FN_FILIAL(HANDLE);

ALTER TABLE K_CRM_NEGOCIACOES ADD CONSTRAINT FK_4335_48324 FOREIGN KEY (ETAPA) REFERENCES K_CRM_ETAPAS(HANDLE);

ALTER TABLE K_CRM_NEGOCIACOES ADD CONSTRAINT FK_4335_48298 FOREIGN KEY (FONTE) REFERENCES K_CRM_FONTES(HANDLE);

CREATE UNIQUE INDEX PK__K_CRM_NE__D1CC59CE30C074FB ON K_CRM_NEGOCIACOES(HANDLE);

ALTER TABLE K_CRM_NEGOCIACOESUSUARIOS ADD CONSTRAINT FK_4341_48330 FOREIGN KEY (USUARIO) REFERENCES K_PD_USUARIOS(HANDLE);

ALTER TABLE K_CRM_NEGOCIACOESUSUARIOS ADD CONSTRAINT FK_4341_48331 FOREIGN KEY (NEGOCIACAO) REFERENCES K_CRM_NEGOCIACOES(HANDLE);

CREATE UNIQUE INDEX PK__K_CRM_NE__D1CC59CE55F1F9AA ON K_CRM_NEGOCIACOESUSUARIOS(HANDLE);

CREATE UNIQUE INDEX PK__K_CRM_PR__D1CC59CE6904CE1E ON K_CRM_PROCESSOS(HANDLE);

ALTER TABLE K_CRM_PROCESSOSTAREFAS ADD CONSTRAINT FK_4344_48367 FOREIGN KEY (PROCESSO) REFERENCES K_CRM_PROCESSOS(HANDLE);

CREATE UNIQUE INDEX PK__K_CRM_PR__D1CC59CE70A5EFE6 ON K_CRM_PROCESSOSTAREFAS(HANDLE);

ALTER TABLE K_CRM_PROJETOS ADD CONSTRAINT FK_4340_48326 FOREIGN KEY (FILIAL) REFERENCES K_FN_FILIAL(HANDLE);

CREATE UNIQUE INDEX PK__K_CRM_PR__D1CC59CE4C688F70 ON K_CRM_PROJETOS(HANDLE);

CREATE UNIQUE INDEX PK__K_CRM_SE__D1CC59CE1DADA087 ON K_CRM_SEGMENTOS(HANDLE);

ALTER TABLE K_CRM_TAREFAS ADD CONSTRAINT FK_4336_48359 FOREIGN KEY (NEGOCIACAO) REFERENCES K_CRM_NEGOCIACOES(HANDLE);

ALTER TABLE K_CRM_TAREFAS ADD CONSTRAINT FK_4336_48360 FOREIGN KEY (CONTATO) REFERENCES K_FN_CONTATO(HANDLE);

ALTER TABLE K_FN_PESSOA ADD CONSTRAINT FK_4237_48319 FOREIGN KEY (SEGMENTO) REFERENCES K_CRM_SEGMENTOS(HANDLE);

ALTER TABLE K_NOTA ADD CONSTRAINT FK_FAT_1_1 FOREIGN KEY (FILIAL) REFERENCES K_FN_FILIAL(HANDLE);

ALTER TABLE K_NOTA ADD CONSTRAINT FK_FAT_1_2 FOREIGN KEY (PESSOA) REFERENCES K_FN_PESSOA(HANDLE);

ALTER TABLE K_NOTA ADD CONSTRAINT FK_FAT_1_3 FOREIGN KEY (USUARIO) REFERENCES K_PD_USUARIOS(HANDLE);

ALTER TABLE K_NOTA ADD CONSTRAINT FK_FAT_1_5 FOREIGN KEY (TRANSPORTADORA) REFERENCES K_FN_PESSOA(HANDLE);

ALTER TABLE K_NOTA ADD CONSTRAINT FK_FAT_1_6 FOREIGN KEY (MUNICIPIO) REFERENCES MUNICIPIOS(HANDLE);

ALTER TABLE K_NOTA ADD CONSTRAINT FK_FAT_1_7 FOREIGN KEY (VENDEDOR) REFERENCES K_PD_USUARIOS(HANDLE);

ALTER TABLE K_NOTAITENS ADD CONSTRAINT FK_FAT_2_1 FOREIGN KEY (NOTA) REFERENCES K_NOTA(HANDLE);

ALTER TABLE K_NOTAITENS ADD CONSTRAINT FK_FAT_2_2 FOREIGN KEY (PRODUTO) REFERENCES PD_PRODUTOS(HANDLE);

ALTER TABLE K_NOTAITENS ADD CONSTRAINT FK_FAT_2_3 FOREIGN KEY (TIPOOPERACAO) REFERENCES K_TIPOOPERACAO(HANDLE);

ALTER TABLE K_NOTADUPLICATAS ADD CONSTRAINT FK_FAT_3_1 FOREIGN KEY (NOTA) REFERENCES K_NOTA(HANDLE);

ALTER TABLE K_NOTADUPLICATAS ADD CONSTRAINT FK_FAT_3_2 FOREIGN KEY (FORMAPAGAMENTO) REFERENCES FN_FORMASPAGAMENTO(HANDLE);

ALTER TABLE K_NOTA ADD CONSTRAINT FK_NOTA_1 FOREIGN KEY (STATUS) REFERENCES K_STATUS(HANDLE);

ALTER TABLE K_STATUS ADD CONSTRAINT FK_STATUS_1 FOREIGN KEY (GRUPOENTRADA) REFERENCES K_FN_GRUPOUSUARIO(HANDLE);

ALTER TABLE K_STATUS ADD CONSTRAINT FK_STATUS_2 FOREIGN KEY (GRUPOSAIDA) REFERENCES K_FN_GRUPOUSUARIO(HANDLE);

ALTER TABLE K_STATUSTRANSICOES ADD CONSTRAINT FK_STATUSTRANSICOES_1 FOREIGN KEY (DE) REFERENCES K_STATUS(HANDLE);

ALTER TABLE K_STATUSTRANSICOES ADD CONSTRAINT FK_STATUSTRANSICOES_2 FOREIGN KEY (PARA) REFERENCES K_STATUS(HANDLE);

ALTER TABLE K_NOTACOMISSAO ADD CONSTRAINT FK_FAT_4_1 FOREIGN KEY (NOTA) REFERENCES K_NOTA(HANDLE);

ALTER TABLE K_NOTACOMISSAO ADD CONSTRAINT FK_FAT_4_2 FOREIGN KEY (PESSOA) REFERENCES K_FN_PESSOA(HANDLE);

ALTER TABLE K_CHAMADOHISTORICO ADD CONSTRAINT FK_4364_48489 FOREIGN KEY (USUARIO) REFERENCES K_PD_USUARIOS(HANDLE);

ALTER TABLE K_CHAMADOHISTORICO ADD CONSTRAINT FK_4364_48488 FOREIGN KEY (CHAMADO) REFERENCES K_CHAMADOS(HANDLE);

CREATE UNIQUE INDEX PK__K_CHAMAD__D1CC59CE3FCDAE61 ON K_CHAMADOHISTORICO(HANDLE);

ALTER TABLE K_CHAMADOS ADD CONSTRAINT FK_4363_48479 FOREIGN KEY (PRODUTO) REFERENCES PD_PRODUTOS(HANDLE);

ALTER TABLE K_CHAMADOS ADD CONSTRAINT FK_4363_48480 FOREIGN KEY (RESPONSAVEL) REFERENCES K_PD_USUARIOS(HANDLE);

ALTER TABLE K_CHAMADOS ADD CONSTRAINT FK_4363_48495 FOREIGN KEY (REPORTER) REFERENCES K_PD_USUARIOS(HANDLE);

ALTER TABLE K_CHAMADOS ADD CONSTRAINT FK_4363_48478 FOREIGN KEY (CLIENTE) REFERENCES K_FN_PESSOA(HANDLE);

CREATE UNIQUE INDEX PK__K_CHAMAD__D1CC59CE3920B0D2 ON K_CHAMADOS(HANDLE);

COMMIT;
