-- ============================================================
-- seed.sql — Dados iniciais do banco de dados do Achaê
-- Execute DEPOIS do schema.sql:
--   mysql -u root -p achae < database/seed.sql
-- ============================================================

USE achae;

-- ============================================================
-- Categorias iniciais
-- ============================================================
INSERT INTO categoria (nome) VALUES
    ('Documentos'),
    ('Eletrônicos'),
    ('Roupas e Acessórios'),
    ('Chaves'),
    ('Material Escolar'),
    ('Garrafas e Utensílios'),
    ('Outros');


-- ============================================================
-- Usuário administrador inicial
--
-- IMPORTANTE: Troque a senha assim que o sistema estiver rodando!
-- Acesse /auth/login.php com:
--   E-mail: admin@achae.local
--   Senha:  Admin@123
-- Depois vá em "Meu Perfil" e altere a senha.
-- ============================================================
INSERT INTO usuario (nome, email, senha_hash, vinculo, perfil, status)
VALUES (
    'Administrador',
    'admin@achae.local',
    '$2y$10$0fD/QwrHUnVWwnXXKwW5c.c5cOVjPh.5zEMw644GSxCayz/s9Ngtq',   -- hash de 'Admin@123' gerado com password_hash()
    'servidor',
    'admin',
    'ativo'
);
