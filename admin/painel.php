<?php
// ============================================================
// admin/painel.php — Painel administrativo
//
// Mostra estatísticas gerais e permite gerenciar usuários
// e objetos do sistema.
// Acesso: apenas usuários com perfil = "admin".
// ============================================================
require_once __DIR__ . "/../auth/config.php";

// Redireciona se não estiver logado
if (empty($_SESSION["usuario_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

// Bloqueia acesso a usuários comuns
if (empty($_SESSION["usuario_perfil"]) || $_SESSION["usuario_perfil"] !== "admin") {
    header("Location: ../auth/painel.php");
    exit;
}

$nome_admin = $_SESSION["usuario_nome"] ?? "Admin";
$inicial    = mb_strtoupper(mb_substr($nome_admin, 0, 1, "UTF-8"), "UTF-8");

// ============================================================
// Ações rápidas via POST (bloquear usuário, remover objeto)
// ============================================================
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    validate_csrf();

    // Bloquear / desbloquear usuário
    if (isset($_POST["toggle_usuario"])) {
        $id   = (int)$_POST["toggle_usuario"];
        $acao = trim((string)($_POST["acao"] ?? ""));

        if (in_array($acao, ["ativo", "bloqueado"], true) && $id > 0) {
            $stmt = $pdo->prepare("UPDATE usuario SET status = :s WHERE id_usuario = :id");
            $stmt->execute(["s" => $acao, "id" => $id]);
        }
    }

    // Marcar objeto como removido (moderação)
    if (isset($_POST["remover_objeto"])) {
        $id = (int)$_POST["remover_objeto"];
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE objeto SET status = \"removido\" WHERE id_objeto = :id");
            $stmt->execute(["id" => $id]);
        }
    }

    // Redireciona de volta para evitar reenvio do formulário (PRG pattern)
    header("Location: painel.php");
    exit;
}

// ============================================================
// Estatísticas gerais
// ============================================================
$stats = [
    "usuarios"   => (int)$pdo->query("SELECT COUNT(*) FROM usuario")->fetchColumn(),
    "objetos"    => (int)$pdo->query("SELECT COUNT(*) FROM objeto WHERE status != \"removido\"")->fetchColumn(),
    "abertos"    => (int)$pdo->query("SELECT COUNT(*) FROM objeto WHERE status = \"aberto\"")->fetchColumn(),
    "resolvidos" => (int)$pdo->query("SELECT COUNT(*) FROM objeto WHERE status = \"resolvido\"")->fetchColumn(),
    "pendentes"  => (int)$pdo->query("SELECT COUNT(*) FROM solicitacao WHERE status = \"pendente\"")->fetchColumn(),
];

// ============================================================
// Lista dos últimos 15 usuários cadastrados
// ============================================================
$usuarios = $pdo->query("
    SELECT id_usuario, nome, email, vinculo, perfil, status, data_cadastro
    FROM usuario
    ORDER BY data_cadastro DESC
    LIMIT 15
")->fetchAll();

// ============================================================
// Lista dos últimos 15 objetos cadastrados
// ============================================================
$objetos = $pdo->query("
    SELECT o.id_objeto, o.titulo, o.tipo, o.status, o.data_publicacao,
           u.nome AS publicado_por,
           c.nome AS categoria
    FROM objeto o
    JOIN usuario  u ON o.id_usuario   = u.id_usuario
    JOIN categoria c ON o.id_categoria = c.id_categoria
    ORDER BY o.data_publicacao DESC
    LIMIT 15
")->fetchAll();

function formatar_data(string $data): string {
    return date("d/m/Y", strtotime($data));
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Painel Admin | Achaê</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/achae/assets/style.css">
</head>
<body class="page-wrapper">

<!-- TOPBAR -->
<nav class="topbar" role="navigation" aria-label="Navegação administrativa">
  <a class="topbar-brand" href="/achae/index.php">
    <span>🔍</span> Achaê
    <span style="font-size:10px;background:#ede9fe;color:#5b21b6;padding:2px 7px;border-radius:5px;font-weight:700;margin-left:6px;">ADMIN</span>
  </a>

  <ul class="topbar-nav">
    <li><a href="/achae/admin/painel.php" class="active">Painel</a></li>
    <li><a href="/achae/admin/usuarios.php">Usuários</a></li>
    <li><a href="/achae/admin/objetos.php">Objetos</a></li>
    <li><a href="/achae/objetos/listar.php">Ver Site</a></li>
  </ul>

  <div class="topbar-right">
    <div class="topbar-user">
      <div class="avatar avatar-sm" aria-hidden="true"><?= e($inicial) ?></div>
      <?= e(explode(" ", $nome_admin)[0]) ?>
    </div>
    <a href="/achae/auth/logout.php" class="btn-ghost" id="btn-sair">Sair</a>
  </div>
</nav>

<!-- CONTEÚDO -->
<main class="page-body">

  <!-- Cabeçalho -->
  <div class="page-header">
    <div class="page-header-text">
      <h1>Painel Administrativo 🛡️</h1>
      <p>Visão geral do sistema e gerenciamento de conteúdo.</p>
    </div>
  </div>

  <!-- Estatísticas gerais -->
  <div class="stat-grid">
    <div class="stat-card">
      <div class="stat-icon purple">👤</div>
      <div class="stat-info">
        <div class="stat-value"><?= $stats["usuarios"] ?></div>
        <div class="stat-label">Usuários cadastrados</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon blue">📦</div>
      <div class="stat-info">
        <div class="stat-value"><?= $stats["objetos"] ?></div>
        <div class="stat-label">Objetos publicados</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon amber">🔓</div>
      <div class="stat-info">
        <div class="stat-value"><?= $stats["abertos"] ?></div>
        <div class="stat-label">Casos em aberto</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon green">✅</div>
      <div class="stat-info">
        <div class="stat-value"><?= $stats["resolvidos"] ?></div>
        <div class="stat-label">Casos resolvidos</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon red">🔔</div>
      <div class="stat-info">
        <div class="stat-value"><?= $stats["pendentes"] ?></div>
        <div class="stat-label">Solicitações pendentes</div>
      </div>
    </div>
  </div>

  <!-- =====================================================
       Tabela de usuários recentes
       ===================================================== -->
  <div class="section-card">
    <div class="section-card-header">
      <h2>Usuários recentes</h2>
      <a href="/achae/admin/usuarios.php" class="action-link view">Ver todos →</a>
    </div>

    <?php if (empty($usuarios)): ?>
      <div class="empty-state">
        <div class="empty-icon">👤</div>
        <h3>Nenhum usuário cadastrado</h3>
      </div>
    <?php else: ?>
    <div class="tabela-wrapper">
      <table class="data-table" id="tabela-usuarios">
        <thead>
          <tr>
            <th>#</th>
            <th>Nome</th>
            <th>E-mail</th>
            <th>Vínculo</th>
            <th>Perfil</th>
            <th>Status</th>
            <th>Cadastro</th>
            <th>Ação</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($usuarios as $u): ?>
          <tr>
            <td style="color:#a0a6b5;"><?= $u["id_usuario"] ?></td>
            <td><strong><?= e($u["nome"]) ?></strong></td>
            <td style="color:var(--muted);"><?= e($u["email"]) ?></td>
            <td><?= e(ucfirst($u["vinculo"])) ?></td>
            <td>
              <span class="badge badge-<?= e($u["perfil"]) ?>">
                <?= e(ucfirst($u["perfil"])) ?>
              </span>
            </td>
            <td>
              <span class="badge badge-<?= e($u["status"]) ?>">
                <?= e(ucfirst($u["status"])) ?>
              </span>
            </td>
            <td><?= formatar_data($u["data_cadastro"]) ?></td>
            <td>
              <!-- Formulário para bloquear ou reativar o usuário -->
              <?php if ($u["perfil"] !== "admin"): ?>
              <form method="post" action="painel.php" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="toggle_usuario" value="<?= $u["id_usuario"] ?>">
                <?php if ($u["status"] === "ativo"): ?>
                  <input type="hidden" name="acao" value="bloqueado">
                  <button type="submit"
                          class="action-link remove"
                          id="btn-bloquear-<?= $u["id_usuario"] ?>"
                          onclick="return confirm(\"Bloquear o usuário <?= e(addslashes($u["nome"])) ?>?\")">
                    Bloquear
                  </button>
                <?php else: ?>
                  <input type="hidden" name="acao" value="ativo">
                  <button type="submit"
                          class="action-link edit"
                          id="btn-ativar-<?= $u["id_usuario"] ?>"
                          onclick="return confirm(\"Reativar o usuário <?= e(addslashes($u["nome"])) ?>?\")">
                    Reativar
                  </button>
                <?php endif; ?>
              </form>
              <?php else: ?>
                <span style="color:#a0a6b5;font-size:11px;">—</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

  <!-- =====================================================
       Tabela de objetos recentes
       ===================================================== -->
  <div class="section-card">
    <div class="section-card-header">
      <h2>Publicações recentes</h2>
      <a href="/achae/admin/objetos.php" class="action-link view">Ver todas →</a>
    </div>

    <?php if (empty($objetos)): ?>
      <div class="empty-state">
        <div class="empty-icon">📦</div>
        <h3>Nenhum objeto publicado ainda</h3>
      </div>
    <?php else: ?>
    <div class="tabela-wrapper">
      <table class="data-table" id="tabela-objetos">
        <thead>
          <tr>
            <th>#</th>
            <th>Título</th>
            <th>Tipo</th>
            <th>Status</th>
            <th>Categoria</th>
            <th>Publicado por</th>
            <th>Data</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($objetos as $obj): ?>
          <tr>
            <td style="color:#a0a6b5;"><?= $obj["id_objeto"] ?></td>
            <td style="max-width:200px;"><strong><?= e($obj["titulo"]) ?></strong></td>
            <td>
              <span class="badge badge-<?= e($obj["tipo"]) ?>">
                <?= $obj["tipo"] === "perdido" ? "🔴 Perdido" : "🟢 Encontrado" ?>
              </span>
            </td>
            <td>
              <span class="badge badge-<?= e($obj["status"]) ?>">
                <?= e(ucfirst($obj["status"])) ?>
              </span>
            </td>
            <td><?= e($obj["categoria"]) ?></td>
            <td><?= e($obj["publicado_por"]) ?></td>
            <td><?= formatar_data($obj["data_publicacao"]) ?></td>
            <td>
              <div class="row-actions">
                <a href="/achae/objetos/detalhes.php?id=<?= $obj["id_objeto"] ?>"
                   class="action-link view"
                   id="btn-ver-obj-<?= $obj["id_objeto"] ?>">
                  Ver
                </a>

                <?php if ($obj["status"] !== "removido"): ?>
                <!-- Formulário para remover objeto inadequado -->
                <form method="post" action="painel.php" style="display:inline;">
                  <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="remover_objeto" value="<?= $obj["id_objeto"] ?>">
                  <button type="submit"
                          class="action-link remove"
                          id="btn-remover-obj-<?= $obj["id_objeto"] ?>"
                          onclick="return confirm(\"Remover esta publicação? A ação não pode ser desfeita.\")">
                    Remover
                  </button>
                </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

</main>
</body>
</html>
