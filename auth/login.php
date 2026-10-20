<?php
require_once __DIR__ . '/config.php';



if (!empty($_SESSION['usuario_id'])) {
    header('Location: painel.php');
    exit;
}

$erro = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $senha = (string)($_POST['senha'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $senha === '') {
        $erro = 'Informe um e-mail válido e sua senha.';
    } else {
        $stmt = $pdo->prepare('
            SELECT id_usuario, nome, perfil, senha_hash, status
            FROM usuario
            WHERE email = :email
            LIMIT 1
        ');
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
            // Usuário bloqueado pelo admin não pode entrar
            if ($usuario['status'] === 'bloqueado') {
                $erro = 'Sua conta está bloqueada. Entre em contato com a administração.';
            } else {
                session_regenerate_id(true);
                $_SESSION['usuario_id']     = (int)$usuario['id_usuario'];
                $_SESSION['usuario_nome']   = $usuario['nome'];
                $_SESSION['usuario_perfil'] = $usuario['perfil'];

                // Redireciona admin para o painel administrativo
                if ($usuario['perfil'] === 'admin') {
                    header('Location: ../admin/painel.php');
                } else {
                    header('Location: painel.php');
                }
                exit;
            }
        } else {
            $erro = 'E-mail ou senha incorretos.';
        }
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#5145e5">
  <title>Entrar | Achaê</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
 <link rel="stylesheet" href="/achae/assets/style.css">
</head>
<body class="auth-page">
  <main class="auth-shell">
    <section class="brand-panel">
      <a class="brand" href="index.php"><span class="brand-mark">⌕</span><span>Achaê</span></a>
      <div class="brand-copy">
        <span class="eyebrow">SISTEMA DO CAMPUS</span>
        <h1>Perdeu algo?<br><span>Vamos encontrar.</span></h1>
        <p>Conectamos quem perdeu com quem encontrou. Simples, rápido e seguro.</p>
        <div class="mini-stats">
          <div><strong>01</strong><span>Publique um objeto</span></div>
          <div><strong>02</strong><span>Conecte-se</span></div>
          <div><strong>03</strong><span>Resolva</span></div>
        </div>
      </div>
      <span class="decor decor-one"></span><span class="decor decor-two"></span>
      <p class="brand-footer">Achados e perdidos do campus · Feito para aproximar pessoas.</p>
    </section>

    <section class="form-panel">
      <div class="mobile-brand"><span class="brand-mark">⌕</span> Achaê</div>
      <div class="form-heading">
        <span class="eyebrow purple">BEM-VINDO DE VOLTA</span>
        <h2>Entre na sua conta</h2>
        <p>Acesse para acompanhar seus objetos e solicitações.</p>
      </div>

      <?php if ($erro): ?><div class="alert" role="alert"><?= e($erro) ?></div><?php endif; ?>

      <form method="post" action="login.php" class="auth-form">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label for="email">E-mail</label>
        <input id="email" name="email" type="email" placeholder="voce@exemplo.com" autocomplete="email" required value="<?= e($email) ?>">
        <div class="label-row"><label for="senha">Senha</label><span class="field-hint">Mantenha seus dados seguros</span></div>
        <input id="senha" name="senha" type="password" placeholder="Digite sua senha" autocomplete="current-password" required>
        <button class="primary-btn" type="submit">Entrar na conta <span aria-hidden="true">→</span></button>
      </form>

      <p class="switch-auth">Ainda não tem uma conta? <a href="cadastro.php">Criar conta grátis</a></p>
      <a class="back-link" href="index.php">← Voltar para o início</a>
      <p class="form-legal">Ao continuar, você concorda em usar o Achaê de forma responsável.</p>
    </section>
  </main>
</body>
</html>
