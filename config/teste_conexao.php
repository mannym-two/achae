<?php
// ============================================================
// config/teste_conexao.php — Testa se a conexão com o banco funciona
//
// ATENÇÃO: DELETE este arquivo antes de colocar o sistema em produção!
// Ele existe apenas para testar durante o desenvolvimento.
// ============================================================

// Carrega a função conectar_banco()
require_once __DIR__ . '/database.php';

echo '<h2>Teste de Conexão — Achaê</h2>';

// Tenta conectar
$pdo = conectar_banco();
echo '<p style="color:green">✅ Conexão com o banco de dados bem-sucedida!</p>';

// Testa uma consulta simples: lista as categorias
$stmt = $pdo->query('SELECT id_categoria, nome FROM categoria ORDER BY nome');
$categorias = $stmt->fetchAll();

echo '<h3>Categorias cadastradas (' . count($categorias) . '):</h3>';
echo '<ul>';
foreach ($categorias as $cat) {
    // htmlspecialchars() protege contra XSS ao exibir dados do banco
    echo '<li>' . htmlspecialchars($cat['nome']) . '</li>';
}
echo '</ul>';

// Testa se o admin foi criado
$stmt = $pdo->prepare('SELECT nome, email, perfil FROM usuario WHERE email = ?');
$stmt->execute(['admin@achae.local']);
$admin = $stmt->fetch();

if ($admin) {
    echo '<h3>Usuário admin encontrado:</h3>';
    echo '<ul>';
    echo '<li>Nome: '   . htmlspecialchars($admin['nome'])   . '</li>';
    echo '<li>E-mail: ' . htmlspecialchars($admin['email'])  . '</li>';
    echo '<li>Perfil: ' . htmlspecialchars($admin['perfil']) . '</li>';
    echo '</ul>';
    echo '<p style="color:green">✅ seed.sql aplicado corretamente!</p>';
} else {
    echo '<p style="color:orange">⚠️ Admin não encontrado. Execute o seed.sql no phpMyAdmin.</p>';
}
