START TRANSACTION;

INSERT INTO K_FN_GRUPOUSUARIO (HANDLE,NOME,PAGINAINICIAL) VALUES 
    (1,'Administrador','home.php'), 
    (2,'Vendedor','page.php'), 
    (3,'Inativo','acesso_negado.php');
    
INSERT INTO K_PD_ALCADAS (HANDLE, NOME, COMPARTILHADO) VALUES
    -- permissões base para administrador
    (1, 'Administração', 'N'),
    (2, 'Delete', 'N'),
    (3, 'Master', 'N'),
    (4, 'Cadastrar pessoa', 'N'),
    (5, 'Estoque', 'N'),
    -- (6, 'Conciliação bancária', 'N'),
    -- (7, 'Cadastrar empresa', 'N'),
    (6, 'Contábil', 'N'),
    (7, '(inativo)', 'N'),
    (8, 'Cadastros', 'N'),
    (9, 'Fiscal', 'N'),
    (10, 'Financeiro', 'N'),
    (11, 'Comercial', 'N'),
    (12, 'Faturamento', 'N'),
    -- libera	
    (13, 'Educacional', 'N'),
    (14, 'Equipe de suporte', 'N'),
    (15, 'Bloqueia financeiro', 'N'),
    -- menus (módulos)
    (16, 'Jurídico', 'N'),
    (17, 'Compras', 'N'),
    (18, 'Fábrica', 'N'),
    (19, 'Suporte', 'N'),
    (20, 'RH', 'N'),
    (21, 'CRM', 'N'),
    (30, 'Simulados', 'N'),
    -- filtra filial
    (22, 'Conta bancária', 'N'),
    (23, 'Plano de contas', 'N'),
    (24, 'Centro de custo', 'N'),
    (25, 'Família de produto', 'N'),
    (26, 'Pessoas', 'N'),
    (27, 'Produto', 'N'),
    (28, 'Almoxarifado', 'N'),
    (29, 'Filial', 'N');
    
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
    (13, 2, 2, 'N'),
    (14, 2, 4, 'N'),
    (15, 2, 5, 'N'),
    (16, 2, 6, 'N'),
    (17, 2, 8, 'N'),
    (18, 2, 9, 'N'),
    (19, 2, 12, 'N'), 
    (20, 1, 21, 'N'),
    (21, 2, 21, 'N'),
    (22, 1, 17, 'N');
    
COMMIT;
