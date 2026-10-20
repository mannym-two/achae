<?php
// ============================================================
// config/database.php — Conexão com o banco de dados
//
// ATENÇÃO: este arquivo NÃO vai para o Git (.gitignore).
// Nunca compartilhe este arquivo publicamente.
// ============================================================

// --- Configurações de conexão ---
// Mude os valores abaixo para os do seu ambiente
define('DB_HOST',    '127.0.0.1'); // endereço do servidor MySQL
define('DB_NAME',    'achae');     // nome do banco de dados
define('DB_USER',    'root');      // usuário do MySQL
define('DB_PASS',    'alunoifro');          // senha do MySQL (deixe vazio se não tiver)
define('DB_CHARSET', 'utf8mb4');   // charset: suporta emojis e acentos

/**
 * Cria e retorna uma conexão PDO com o banco de dados.
 *
 * O que é PDO?
 * PDO (PHP Data Objects) é uma forma segura de conversar com o banco de dados.
 * Com PDO usamos "consultas preparadas", que impedem ataques de SQL Injection.
 *
 * @return PDO objeto de conexão pronto para uso
 */
function conectar_banco(): PDO
{
    // DSN = "Data Source Name": string que identifica o banco
    // Exemplo: "mysql:host=127.0.0.1;dbname=achae;charset=utf8mb4"
    $dsn = 'mysql:host=' . DB_HOST
         . ';dbname='    . DB_NAME
         . ';charset='   . DB_CHARSET;

    // Opções que tornam o PDO mais seguro e prático:
    $opcoes = [
        // Lança exceções em caso de erro (mais fácil de depurar)
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,

        // Retorna cada linha como array associativo: $linha['nome'], $linha['email']...
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

        // Desativa emulação de prepared statements (mais seguro)
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        // Tenta criar a conexão
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $opcoes);
        return $pdo;

    } catch (PDOException $e) {
        // Se falhar: registra o erro real no log do servidor (invisível ao usuário)
        error_log('[Achaê] Erro de conexão com o banco: ' . $e->getMessage());

        // Exibe mensagem genérica para o usuário (nunca mostrar detalhes técnicos!)
        die('Não foi possível conectar ao banco de dados. Tente novamente mais tarde.');
    }
}
