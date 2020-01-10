START TRANSACTION;

INSERT INTO K_FN_GRUPOUSUARIO (HANDLE,NOME,PAGINAINICIAL) VALUES 
    (1, 'Administrador', 'home.php'),
    (2, 'Secretária escolar', 'educacional_dashboard.php'),
    (3, 'Inativo', 'acesso_negado.php'),
    (4, 'Professores', 'educacional_turma.php'),
    (5, 'Coordenação pedagógica', 'educacional_dashboard.php'),
    (6, 'Direção Escolar', 'educacional_dashboard.php'),
    (7, 'Orientação pedagógica', 'educacional_dashboard.php'),
    (8, 'Financeiro', 'home.php'),
    (9, 'Monitores', 'educacional_turma.php'),
    (10, 'Aluno', 'educacional_notas.php');
    
INSERT INTO K_FN_TIPOVINCULO (HANDLE, NOME, RELACIONADO) VALUES
    (1, 'Pai', 'Filho/a'),
    (2, 'Mãe', 'Filho/a'),
    (3, 'Responsável legal', 'Enteado'),
    (4, 'Avô', 'Neto/a'),
    (5, 'Tio', 'Sobrinho/a'),
    (6, 'Irmão/a', 'Irmão/a');

INSERT INTO K_DISCIPLINA (HANDLE, NOME, SEGMENTO, OBRIGATORIO) VALUES
    (1, 'Artes', 'EM', 'S'),
    (2, 'Língua Portuguesa', 'EM', 'S'),
    (3, 'Matemática', 'EM', 'S'),
    (4, 'História', 'EM', 'S'),
    (5, 'Geografia', 'EM', 'S'),
    (6, 'Natureza e Sociedade', 'EM', 'S'),
    (7, 'Ciências', 'EM', 'S'),
    (8, 'Inglês', 'EM', 'S'),
    (9, 'Educação física', 'EM', 'S');

INSERT INTO K_TURMA (HANDLE, FILIAL, NOME, TURNO, ATUAL, MINALUNOS, MAXALUNOS) VALUES
    (1, 1, 'Turma padrão', 'M', 'S', 0, 99);

INSERT INTO K_TURMAHORARIO (HANDLE, TURMA, DISCIPLINA, DIASEMANA, HORARIOINICIO, HORARIOTERMINO, TIMESLOT) VALUES
    (1, 1, 1, 1, '08:00', '09:00', 1),
    (2, 1, 2, 1, '09:00', '10:00', 2),
    (3, 1, 3, 1, '10:00', '11:00', 3),
    (4, 1, 4, 1, '11:00', '12:00', 4);

INSERT INTO K_TURMAAVALIACAO (HANDLE, TURMA, NOME, PESO, DISCIPLINA) VALUES
    (1, 1, 'Prova 1', 3, 4),
    (2, 1, 'Prova 2', 4, 4),
    (3, 1, 'Trabalho 1', 2, 4),
    (4, 1, 'Exercícios', 1, 4);

INSERT INTO K_PD_ALCADAS (HANDLE, NOME, COMPARTILHADO) VALUES
(1, 'Administração', 'N'),
(2, 'Delete', 'N'),
(3, 'Master', 'N'),
(4, 'Cadastrar pessoa', 'N'),
(5, 'Estoque', 'N'),
(6, 'Contábil', 'N'),
(7, '(inativo)', 'N'),
(8, 'Cadastros', 'N'),
(9, 'Fiscal', 'N'),
(10, 'Financeiro', 'N'),
(11, 'Comercial', 'N'),
(12, 'Faturamento', 'N'),
(13, 'Educacional', 'N'),
(14, 'Equipe de suporte', 'N'),
(15, 'Bloqueia financeiro', 'N'),
(16, 'Jurídico', 'N'),
(17, 'Compras', 'N'),
(18, 'Fábrica', 'N'),
(19, 'Suporte', 'N'),
(20, 'RH', 'N'),
(21, 'CRM', 'N'),
(22, 'Conta bancária', 'N'),
(23, 'Plano de contas', 'N'),
(24, 'Centro de custo', 'N'),
(25, 'Família de produto', 'S'),
(26, 'Pessoas', 'N'),
(27, 'Produto', 'S'),
(28, 'Almoxarifado', 'N'),
(29, 'Filial', 'N'),
(30, 'Simulados', 'N'),
(31, 'Auditoria', 'N'),
(32, 'Turma', 'N'),
(33, 'Lista de preço', 'N'),
(34, 'Empresa', 'N'),
(35, 'Forma de pagamento', 'N'),
(36, 'Condição de pagamento', 'N'),
(37, 'Boleto Bancário', 'N'),
(38, 'Segmento de negócio', 'N'),
(39, 'Área', 'N'),
(40, 'Unidade', 'N'),
(41, 'Prefixo', 'N'),
(42, 'Níveis', 'N'),
(43, 'Modelo', 'N'),
(44, 'Fabricante', 'N'),
(45, 'Professor', 'N'),
(46, 'Tipo de situação', 'N'),
(47, 'Tipo de vínculo', 'N'),
(48, 'Dashboard', 'N'),
(49, 'Disciplina', 'N'),
(50, 'Expedição', 'N'),
(51, 'Loja virtual', 'N'),
(52, 'Entrada de estoque', 'N'),
(53, 'Termos', 'S'),
(54, 'CST - Origem', 'S'),
(55, 'CST - Tributação', 'S'),
(56, 'Movimento de estoque', 'N'),
(57, 'Inventário', 'N'),
(58, 'Endereço', 'N'),
(59, 'Série', 'N'),
(60, 'Diário de classe', 'N'),
(61, 'Avaliações', 'N'),
(62, 'Ficha médica', 'N'),
(63, 'Agenda', 'N'),
(64, 'Aluno', 'N');

INSERT INTO K_FN_PERMISSOES (HANDLE, GRUPO, ALCADA, BLOQUEIO) VALUES
    (1, 1, 1, 'N'),
    (2, 1, 2, 'N'),
    (3, 1, 3, 'N'),
    (4, 1, 4, 'N'),
    (5, 1, 5, 'N'),
    (6, 1, 6, 'N'),
    (7, 1, 7, 'N'),
    (8, 1, 8, 'N'),
    (9, 1, 9, 'N'),
    (10, 1, 10, 'N'),
    (11, 1, 11, 'N'),
    (12, 1, 12, 'N'),
    (22, 1, 17, 'N'),
    (99, 1, 13, 'N'),
    (227, 6, 1, 'N'),
    (228, 6, 2, 'N'),
    (229, 6, 4, 'N'),
    (230, 6, 5, 'N'),
    (231, 6, 6, 'N'),
    (232, 6, 8, 'N'),
    (233, 6, 9, 'N'),
    (234, 6, 10, 'N'),
    (235, 6, 12, 'N'),
    (236, 6, 13, 'N'),
    (237, 6, 15, 'N'),
    (238, 6, 17, 'N'),
    (239, 6, 22, 'N'),
    (240, 6, 23, 'N'),
    (241, 6, 28, 'N'),
    (242, 6, 29, 'N'),
    (243, 6, 35, 'S'),
    (244, 6, 37, 'N'),
    (245, 6, 38, 'S'),
    (246, 6, 39, 'S'),
    (247, 6, 40, 'S'),
    (248, 6, 42, 'S'),
    (249, 6, 43, 'S'),
    (250, 6, 44, 'S'),
    (251, 6, 50, 'S'),
    (252, 6, 51, 'S'),
    (253, 6, 52, 'S'),
    (254, 6, 53, 'S'),
    (255, 6, 54, 'S'),
    (256, 6, 55, 'S'),
    (257, 2, 1, 'N'),
    (258, 2, 2, 'N'),
    (259, 2, 4, 'N'),
    (260, 2, 5, 'N'),
    (261, 2, 6, 'N'),
    (262, 2, 7, 'N'),
    (263, 2, 8, 'N'),
    (264, 2, 9, 'N'),
    (265, 2, 10, 'N'),
    (266, 2, 12, 'N'),
    (267, 2, 13, 'N'),
    (268, 2, 17, 'N'),
    (269, 2, 35, 'S'),
    (270, 2, 37, 'N'),
    (271, 2, 38, 'S'),
    (272, 2, 39, 'S'),
    (273, 2, 40, 'S'),
    (274, 2, 42, 'S'),
    (275, 2, 43, 'S'),
    (276, 2, 44, 'S'),
    (277, 2, 50, 'S'),
    (278, 2, 51, 'S'),
    (279, 2, 52, 'S'),
    (280, 2, 53, 'S'),
    (281, 2, 54, 'S'),
    (282, 2, 55, 'S'),
    (283, 5, 2, 'N'),
    (284, 5, 4, 'N'),
    (285, 5, 5, 'N'),
    (286, 5, 7, 'N'),
    (287, 5, 8, 'N'),
    (288, 5, 13, 'N'),
    (289, 5, 25, 'S'),
    (290, 5, 26, 'N'),
    (291, 5, 27, 'S'),
    (292, 5, 28, 'S'),
    (293, 5, 29, 'N'),
    (294, 5, 32, 'N'),
    (295, 5, 33, 'S'),
    (296, 5, 34, 'S'),
    (297, 5, 35, 'S'),
    (298, 5, 36, 'S'),
    (299, 5, 37, 'S'),
    (300, 5, 38, 'S'),
    (301, 5, 39, 'S'),
    (302, 5, 40, 'S'),
    (303, 5, 41, 'S'),
    (304, 5, 42, 'S'),
    (305, 5, 43, 'S'),
    (306, 5, 44, 'S'),
    (307, 5, 53, 'S'),
    (308, 5, 56, 'S'),
    (309, 5, 57, 'S'),
    (310, 5, 58, 'S'),
    (311, 7, 2, 'N'),
    (312, 7, 4, 'N'),
    (313, 7, 5, 'N'),
    (314, 7, 7, 'N'),
    (315, 7, 8, 'N'),
    (316, 7, 13, 'N'),
    (317, 7, 25, 'S'),
    (318, 7, 26, 'N'),
    (319, 7, 27, 'S'),
    (320, 7, 28, 'S'),
    (321, 7, 29, 'N'),
    (322, 7, 32, 'N'),
    (323, 7, 33, 'S'),
    (324, 7, 34, 'S'),
    (325, 7, 35, 'S'),
    (326, 7, 36, 'S'),
    (327, 7, 37, 'S'),
    (328, 7, 38, 'S'),
    (329, 7, 39, 'S'),
    (330, 7, 40, 'S'),
    (331, 7, 41, 'S'),
    (332, 7, 42, 'S'),
    (333, 7, 43, 'S'),
    (334, 7, 44, 'S'),
    (335, 7, 53, 'S'),
    (336, 7, 56, 'S'),
    (337, 7, 57, 'S'),
    (338, 7, 58, 'S'),
    (385, 8, 1, 'N'),
    (386, 8, 7, 'N'),
    (387, 8, 8, 'N'),
    (388, 8, 10, 'N'),
    (389, 8, 12, 'N'),
    (390, 8, 13, 'N'),
    (391, 8, 15, 'N'),
    (392, 8, 17, 'N'),
    (393, 8, 22, 'N'),
    (394, 8, 23, 'N'),
    (395, 8, 25, 'S'),
    (396, 8, 26, 'N'),
    (397, 8, 27, 'N'),
    (398, 8, 29, 'N'),
    (399, 8, 32, 'N'),
    (400, 8, 34, 'S'),
    (401, 8, 35, 'S'),
    (402, 8, 38, 'S'),
    (403, 8, 39, 'S'),
    (404, 8, 40, 'S'),
    (405, 8, 41, 'S'),
    (406, 8, 42, 'S'),
    (407, 8, 43, 'S'),
    (408, 8, 44, 'S'),
    (409, 8, 45, 'N'),
    (410, 8, 46, 'S'),
    (411, 8, 47, 'S'),
    (412, 8, 49, 'S'),
    (413, 8, 50, 'S'),
    (414, 8, 51, 'S'),
    (415, 8, 52, 'S'),
    (416, 8, 53, 'S'),
    (417, 8, 59, 'S'),
    (418, 8, 60, 'S'),
    (419, 8, 61, 'S'),
    (420, 8, 62, 'S'),
    (421, 8, 63, 'N'),
    (422, 4, 13, 'N'),
    (423, 4, 45, 'S'),
    (424, 4, 46, 'S'),
    (425, 4, 47, 'S'),
    (426, 4, 48, 'S'),
    (427, 4, 49, 'S'),
    (428, 4, 59, 'S'),
    (429, 4, 64, 'S'),
    (430, 9, 13, 'N'),
    (431, 9, 45, 'S'),
    (432, 9, 46, 'S'),
    (433, 9, 47, 'S'),
    (434, 9, 49, 'S'),
    (435, 9, 59, 'S'),
    (436, 9, 62, 'S'),
    (437, 9, 64, 'S'),
    (438, 10, 13, 'N'),
    (439, 10, 32, 'S'),
    (440, 10, 45, 'S'),
    (441, 10, 46, 'S'),
    (442, 10, 47, 'S'),
    (443, 10, 49, 'S'),
    (444, 10, 59, 'S'),
    (445, 10, 60, 'S'),
    (446, 10, 62, 'S'),
    (447, 10, 64, 'S');

INSERT INTO K_ALUNOSITUACAO (HANDLE, NOME) VALUES
    (1, 'Cursando'),
    (2, 'Matriculado'),
    (3, 'Pré-matriculado'),
    (4, 'Aprovado'),
    (5, 'Reprovado'),
    (6, 'Condicional'),
    (7, 'Transferido'),
    (8, 'Irregular'),
    (9, 'Outros');

UPDATE K_FN_PESSOA SET PROFESSOR = 'S' WHERE HANDLE = 2;

INSERT INTO K_TIPOOCORRENCIA (HANDLE, NOME, SEVERIDADE) VALUES
    (1, 'Indisciplina', 'Negativa'),
    (2, 'Mau comportamento', 'Negativa'),
    (3, 'Avaliação perdida', 'Negativa'),
    (4, 'Tarefa incompleta', 'Negativa'),
    (5, 'Tarefa não realizada', 'Negativa'),
    (6, 'Material incompleto', 'Negativa'),
    (7, 'Atraso', 'Negativa'),
    (8, 'Sem uniforme', 'Negativa'),
    (9, 'Retirado da sala', 'Negativa'),
    (10, 'Uso de celular', 'Negativa'),
    (11, 'Danos ao patrimônio', 'Negativa'),
    (12, 'Agressão verbal', 'Grave'),
    (13, 'Agressão física', 'Grave'),
    (14, 'Bullying/intimidação', 'Grave'),
    (15, 'Evasão escolar', 'Neutra'),
    (16, 'Transferência', 'Neutra'),
    (17, 'Falecimento', 'Neutra'),
    (18, 'Acompanhamento psicológico', 'Neutra');

COMMIT;
