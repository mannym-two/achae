<?php
require_once __DIR__ . '/config.php';

if (!empty($_SESSION['usuario_id'])) {
    header('Location: painel.php');
    exit;
}

$erro = '';
$nome = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();
    $nome = trim((string)($_POST['nome'] ?? ''));
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $senha = (string)($_POST['senha'] ?? '');
    $confirmacao = (string)($_POST['confirmacao'] ?? '');

    if (mb_strlen($nome) < 2 || mb_strlen($nome) > 100) {
        $erro = 'Seu nome deve ter entre 2 e 100 caracteres.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
        $erro = 'Informe um e-mail válido.';
    } elseif (strlen($senha) < 8) {
        $erro = 'A senha precisa ter pelo menos 8 caracteres.';
    } elseif ($senha !== $confirmacao) {
        $erro = 'As senhas não são iguais.';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)');
            $stmt->execute([
                'nome' => $nome,
                'email' => $email,
                'senha' => password_hash($senha, PASSWORD_DEFAULT),
            ]);
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = (int)$pdo->lastInsertId();
            $_SESSION['usuario_nome'] = $nome;
            header('Location: painel.php');
            exit;
        } catch (PDOException $ex) {
            if ($ex->getCode() === '23000') {
                $erro = 'Este e-mail já está cadastrado. Entre na sua conta.';
            } else {
                $erro = 'Não foi possível criar sua conta agora. Tente novamente.';
            }
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
  <title>Criar conta | Achaê</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/achae/assets/style.css">
</head>
<body class="auth-page">
  <main class="auth-shell register-shell">
    <section class="brand-panel">
      <a class="brand" href="index.php"><span class="brand-mark">⌕</span><span>Achaê</span></a>
      <div class="brand-copy">
        <span class="eyebrow">FAÇA PARTE DA COMUNIDADE</span>
        <h1>Encontrou?<br><span>Faça a diferença.</span></h1>
        <p>Uma pequena atitude pode ajudar alguém a recuperar algo importante.</p>
        <div class="feature-list">
          <div><span class="feature-icon">✓</span><div><strong>Fácil de usar</strong><small>Cadastre-se em poucos passos.</small></div></div>
          <div><span class="feature-icon">⌕</span><div><strong>Comunidade conectada</strong><small>Ajude a reunir objetos e donos.</small></div></div>
          <div><span class="feature-icon">♡</span><div><strong>Feito para o campus</strong><small>Mais organização, menos preocupação.</small></div></div>
        </div>
      </div>
      <span class="decor decor-one"></span><span class="decor decor-two"></span>
      <p class="brand-footer">Achados e perdidos do campus · Feito para aproximar pessoas.</p>
    </section>

    <section class="form-panel">
      <div class="mobile-brand"><span class="brand-mark">⌕</span> Achaê</div>
      <div class="form-heading">
        <span class="eyebrow purple">COMECE AGORA</span>
        <h2>Crie sua conta</h2>
        <p>Cadastre-se gratuitamente e faça parte da comunidade.</p>
      </div>

      <?php if ($erro): ?><div class="alert" role="alert"><?= e($erro) ?></div><?php endif; ?>

      <form method="post" action="cadastro.php" class="auth-form">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label for="nome">Nome completo</label>
        <input id="nome" name="nome" type="text" placeholder="Como podemos te chamar?" autocomplete="name" minlength="2" maxlength="100" required value="<?= e($nome) ?>">
        <label for="email">E-mail</label>
        <input id="email" name="email" type="email" placeholder="voce@exemplo.com" autocomplete="email" required value="<?= e($email) ?>">
        <label for="senha">Senha</label>
        <input id="senha" name="senha" type="password" placeholder="Pelo menos 8 caracteres" autocomplete="new-password" minlength="8" required>
        <label for="confirmacao">Confirme sua senha</label>
        <input id="confirmacao" name="confirmacao" type="password" placeholder="Digite a senha novamente" autocomplete="new-password" minlength="8" required>
        <button class="primary-btn" type="submit">Criar minha conta <span aria-hidden="true">→</span></button>
      </form>

      <p class="switch-auth">Já tem uma conta? <a href="login.php">Entrar</a></p>
      <a class="back-link" href="index.php">← Voltar para o início</a>
      <p class="form-legal">Seus dados são usados para acessar sua conta no Achaê.</p>
    </section>
  </main>
  <script>
    const form = document.querySelector('form');
    form.addEventListener('submit', (event) => {
      const password = document.querySelector('#senha').value;
      const confirmation = document.querySelector('#confirmacao').value;
      if (password !== confirmation) {
        event.preventDefault();
        document.querySelector('#confirmacao').setCustomValidity('As senhas não são iguais.');
        document.querySelector('#confirmacao').reportValidity();
      }
    });
    document.querySelector('#confirmacao').addEventListener('input', (event) => event.target.setCustomValidity(''));
  </script>
</body>
</html>
