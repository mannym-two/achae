<?php
// ============================================================
// config/database.example.php — MODELO de configuração
//
// Este arquivo é um exemplo e PODE ir para o Git.
// Para configurar o projeto:
//   1. Copie este arquivo e renomeie para "database.php"
//   2. Preencha os valores com os dados reais do seu ambiente
// ============================================================

// --- Configurações de conexão ---
define('DB_HOST',    '127.0.0.1'); // endereço do servidor MySQL
define('DB_NAME',    'achae');     // nome do banco de dados
define('DB_USER',    'root');      // usuário do MySQL
define('DB_PASS',    'COLOQUE_SUA_SENHA_AQUI'); // senha do MySQL
define('DB_CHARSET', 'utf8mb4');   // charset: suporta emojis e acentos

/**
 * Cria e retorna uma conexão PDO com o banco de dados.
 *
 * @return PDO objeto de conexão pronto para uso
 */
function conectar_banco(): PDO
{
    $dsn = 'mysql:host=' . DB_HOST
         . ';dbname='    . DB_NAME
         . ';charset='   . DB_CHARSET;

    $opcoes = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        return new PDO($dsn, DB_USER, DB_PASS, $opcoes);
    } catch (PDOException $e) {
        error_log('[Achaê] Erro de conexão com o banco: ' . $e->getMessage());
        die('Não foi possível conectar ao banco de dados. Tente novamente mais tarde.');
    }
}
