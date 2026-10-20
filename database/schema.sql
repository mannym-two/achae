-- ============================================================
-- schema.sql — Estrutura do banco de dados do Achaê
-- Execute este arquivo no phpMyAdmin ou via linha de comando:
--   mysql -u root -p < database/schema.sql
-- ============================================================

-- Cria o banco de dados se ainda não existir e seleciona ele
CREATE DATABASE IF NOT EXISTS achae
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE achae;

-- ============================================================
-- Tabela: CATEGORIA
-- Armazena as categorias dos objetos (ex.: Documentos, Chaves)
-- ============================================================
CREATE TABLE IF NOT EXISTS categoria (
    id_categoria INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome         VARCHAR(100) NOT NULL,
    UNIQUE KEY uq_categoria_nome (nome)  -- evita categorias duplicadas
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- Tabela: USUARIO
-- Armazena os dados de todos os usuários do sistema
-- ============================================================
CREATE TABLE IF NOT EXISTS usuario (
    id_usuario    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome          VARCHAR(150) NOT NULL,
    email         VARCHAR(200) NOT NULL,
    senha_hash    VARCHAR(255) NOT NULL,          -- nunca guardar a senha em texto puro!
    vinculo       ENUM('aluno','professor','servidor') NOT NULL,  -- quem é no campus
    perfil        ENUM('usuario','admin') NOT NULL DEFAULT 'usuario', -- o que pode fazer
    telefone      VARCHAR(20)  DEFAULT NULL,       -- campo opcional
    status        ENUM('ativo','bloqueado') NOT NULL DEFAULT 'ativo',
    data_cadastro DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uq_usuario_email (email),           -- e-mail único no sistema
    INDEX idx_usuario_status (status)              -- acelera consultas de usuários ativos
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- Tabela: OBJETO
-- Armazena as publicações de objetos perdidos ou encontrados
-- ============================================================
CREATE TABLE IF NOT EXISTS objeto (
    id_objeto       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario      INT UNSIGNED NOT NULL,          -- quem publicou
    id_categoria    INT UNSIGNED NOT NULL,          -- categoria do objeto
    tipo            ENUM('perdido','encontrado') NOT NULL,
    titulo          VARCHAR(200) NOT NULL,
    descricao       TEXT         NOT NULL,
    local           VARCHAR(200) NOT NULL,          -- texto livre na v1
    data_ocorrencia DATE         NOT NULL,           -- quando foi perdido/encontrado
    foto            VARCHAR(255) DEFAULT NULL,       -- nome do arquivo (salvo em uploads/)
    status          ENUM('aberto','resolvido','removido') NOT NULL DEFAULT 'aberto',
    data_publicacao DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- Chaves estrangeiras: garantem que o usuário e a categoria existam
    CONSTRAINT fk_objeto_usuario   FOREIGN KEY (id_usuario)   REFERENCES usuario   (id_usuario)   ON DELETE CASCADE,
    CONSTRAINT fk_objeto_categoria FOREIGN KEY (id_categoria) REFERENCES categoria (id_categoria) ON DELETE RESTRICT,

    INDEX idx_objeto_tipo   (tipo),                 -- filtro por tipo
    INDEX idx_objeto_status (status),               -- filtro por status
    INDEX idx_objeto_usuario (id_usuario),          -- publicações de um usuário
    INDEX idx_objeto_data   (data_publicacao DESC)  -- ordenação pela data mais recente
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- Tabela: SOLICITACAO
-- Armazena as solicitações de devolução feitas por usuários
-- ============================================================
CREATE TABLE IF NOT EXISTS solicitacao (
    id_solicitacao  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_objeto       INT UNSIGNED NOT NULL,          -- objeto relacionado
    id_usuario      INT UNSIGNED NOT NULL,          -- quem está solicitando
    mensagem        TEXT         NOT NULL,          -- mensagem obrigatória
    status          ENUM('pendente','aceita','recusada') NOT NULL DEFAULT 'pendente',
    data_solicitacao DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- Chaves estrangeiras
    CONSTRAINT fk_solicitacao_objeto  FOREIGN KEY (id_objeto)  REFERENCES objeto  (id_objeto)  ON DELETE CASCADE,
    CONSTRAINT fk_solicitacao_usuario FOREIGN KEY (id_usuario) REFERENCES usuario (id_usuario) ON DELETE CASCADE,

    -- Regra: um usuário não pode ter duas solicitações pendentes para o mesmo objeto
    -- (verificado também no PHP, mas este índice UNIQUE garante no banco)
    -- Nota: não podemos usar UNIQUE simples aqui pois o usuário pode ter uma recusada
    -- e querer tentar novamente. A regra de "não ter duas pendentes" será verificada no PHP.

    INDEX idx_solicitacao_objeto  (id_objeto),
    INDEX idx_solicitacao_usuario (id_usuario),
    INDEX idx_solicitacao_status  (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
