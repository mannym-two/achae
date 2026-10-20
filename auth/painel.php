<?php
// ============================================================
// auth/painel.php — Painel do usuário comum
//
// Mostra um resumo das publicações do usuário e das
// solicitações que ele recebeu nos seus objetos.
// Acesso: apenas usuários logados.
// ============================================================
require_once __DIR__ . "/config.php";

// Redireciona para o login se não estiver logado
// Usa a variável de sessão definida no login.php
if (empty($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

// Admin vai para o painel administrativo
if (!empty($_SESSION["usuario_perfil"]) && $_SESSION["usuario_perfil"] === "admin") {
    header("Location: ../admin/painel.php");
    exit;
}

$id_usuario   = (int)$_SESSION["usuario_id"];
$nome_usuario = $_SESSION["usuario_nome"] ?? "Usuário";
// Pega a primeira letra do nome para o avatar
$inicial      = mb_strtoupper(mb_substr($nome_usuario, 0, 1, "UTF-8"), "UTF-8");

// ============================================================
// Busca dados do banco
// ============================================================

// Total de objetos publicados por este usuário (exceto removidos)
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM objeto
    WHERE id_usuario = :id AND status != \"removido\"
");
$stmt->execute(["id" => $id_usuario]);
$total_publicacoes = (int)$stmt->fetchColumn();

// Total de objetos resolvidos pelo usuário
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM objeto
    WHERE id_usuario = :id AND status = \"resolvido\"
");
$stmt->execute(["id" => $id_usuario]);
$total_resolvidos = (int)$stmt->fetchColumn();

// Total de solicitações pendentes recebidas nos objetos do usuário
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM solicitacao s
    JOIN objeto o ON s.id_objeto = o.id_objeto
    WHERE o.id_usuario = :id AND s.status = \"pendente\"
");
$stmt->execute(["id" => $id_usuario]);
$total_pendentes = (int)$stmt->fetchColumn();

// Busca os 5 objetos mais recentes do usuário
$stmt = $pdo->prepare("
    SELECT o.id_objeto, o.titulo, o.tipo, o.status, o.data_publicacao,
           c.nome AS categoria,
           (SELECT COUNT(*) FROM solicitacao s
            WHERE s.id_objeto = o.id_objeto AND s.status = \"pendente\") AS pendentes
    FROM objeto o
    JOIN categoria c ON o.id_categoria = c.id_categoria
    WHERE o.id_usuario = :id AND o.status != \"removido\"
    ORDER BY o.data_publicacao DESC
    LIMIT 5
");
$stmt->execute(["id" => $id_usuario]);
$meus_objetos = $stmt->fetchAll();

// Busca as 5 solicitações pendentes mais recentes recebidas pelo usuário
$stmt = $pdo->prepare("
    SELECT s.id_solicitacao, s.mensagem, s.data_solicitacao,
           o.titulo AS objeto_titulo, o.id_objeto,
           u.nome   AS solicitante_nome
    FROM solicitacao s
    JOIN objeto  o ON s.id_objeto  = o.id_objeto
    JOIN usuario u ON s.id_usuario = u.id_usuario
    WHERE o.id_usuario = :id AND s.status = \"pendente\"
    ORDER BY s.data_solicitacao DESC
    LIMIT 5
");
$stmt->execute(["id" => $id_usuario]);
$solicitacoes_recebidas = $stmt->fetchAll();

// Formata datas para exibição amigável
function formatar_data(string $data): string {
    $ts = strtotime($data);
    return date("d/m/Y", $ts);
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Meu Painel | Achaê</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/achae/assets/style.css">
</head>
<body class="page-wrapper">

<!-- ============================================================
     TOPBAR — barra de navegação superior
     ============================================================ -->
<nav class="topbar" role="navigation" aria-label="Navegação principal">
  <a class="topbar-brand" href="/achae/index.php">
    <span>🔍</span> Achaê
  </a>

  <ul class="topbar-nav">
    <li><a href="/achae/auth/painel.php" class="active">Meu Painel</a></li>
    <li><a href="/achae/objetos/listar.php">Ver Objetos</a></li>
    <li><a href="/achae/objetos/publicar.php">Publicar Objeto</a></li>
    <li><a href="/achae/solicitacoes/minhas.php">Solicitações</a></li>
  </ul>

  <div class="topbar-right">
    <div class="topbar-user">
      <div class="avatar avatar-sm" aria-hidden="true"><?= e($inicial) ?></div>
      <?= e(explode(" ", $nome_usuario)[0]) ?>
    </div>
    <a href="/achae/auth/logout.php" class="btn-ghost" id="btn-sair">Sair</a>
  </div>
</nav>

<!-- ============================================================
     CONTEÚDO PRINCIPAL
     ============================================================ -->
<main class="page-body">

  <!-- Cabeçalho da página -->
  <div class="page-header">
    <div class="page-header-text">
      <h1>Olá, <?= e(explode(" ", $nome_usuario)[0]) ?>! 👋</h1>
      <p>Acompanhe suas publicações e solicitações recebidas.</p>
    </div>
    <a href="/achae/objetos/publicar.php" class="btn-primary-sm" id="btn-publicar">
      ＋ Publicar Objeto
    </a>
  </div>

  <!-- Cards de estatísticas -->
  <div class="stat-grid">
    <div class="stat-card">
      <div class="stat-icon blue">📦</div>
      <div class="stat-info">
        <div class="stat-value" id="stat-publicacoes"><?= $total_publicacoes ?></div>
        <div class="stat-label">Publicações ativas</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon green">✅</div>
      <div class="stat-info">
        <div class="stat-value" id="stat-resolvidos"><?= $total_resolvidos ?></div>
        <div class="stat-label">Casos resolvidos</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon amber">🔔</div>
      <div class="stat-info">
        <div class="stat-value" id="stat-pendentes"><?= $total_pendentes ?></div>
        <div class="stat-label">Solicitações pendentes</div>
      </div>
    </div>
  </div>

  <!-- Minhas publicações recentes -->
  <div class="section-card">
    <div class="section-card-header">
      <h2>Minhas publicações recentes</h2>
      <a href="/achae/objetos/listar.php?meus=1" class="action-link view">Ver todas →</a>
    </div>

    <?php if (empty($meus_objetos)): ?>
      <div class="empty-state">
        <div class="empty-icon">📭</div>
        <h3>Nenhuma publicação ainda</h3>
        <p>Você ainda não publicou nenhum objeto. Que tal começar agora?</p>
        <a href="/achae/objetos/publicar.php" class="btn-primary-sm" id="btn-primeira-pub">
          Publicar meu primeiro objeto
        </a>
      </div>
    <?php else: ?>
      <div class="tabela-wrapper">
        <table class="data-table" id="tabela-meus-objetos">
          <thead>
            <tr>
              <th>Título</th>
              <th>Tipo</th>
              <th>Status</th>
              <th>Categoria</th>
              <th>Publicado em</th>
              <th>Pendentes</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($meus_objetos as $obj): ?>
            <tr>
              <td style="max-width:200px;">
                <strong><?= e($obj["titulo"]) ?></strong>
              </td>
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
              <td><?= formatar_data($obj["data_publicacao"]) ?></td>
              <td>
                <?php if ($obj["pendentes"] > 0): ?>
                  <span class="badge badge-pendente">
                    🔔 <?= (int)$obj["pendentes"] ?>
                  </span>
                <?php else: ?>
                  <span style="color:#a0a6b5; font-size:11px;">—</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="row-actions">
                  <a href="/achae/objetos/detalhes.php?id=<?= $obj["id_objeto"] ?>"
                     class="action-link view"
                     id="btn-ver-<?= $obj["id_objeto"] ?>">
                    Ver
                  </a>
                  <?php if ($obj["status"] === "aberto"): ?>
                  <a href="/achae/objetos/editar.php?id=<?= $obj["id_objeto"] ?>"
                     class="action-link edit"
                     id="btn-editar-<?= $obj["id_objeto"] ?>">
                    Editar
                  </a>
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

  <!-- Solicitações recebidas pendentes -->
  <div class="section-card">
    <div class="section-card-header">
      <h2>Solicitações pendentes recebidas</h2>
      <a href="/achae/solicitacoes/minhas.php" class="action-link view">Ver todas →</a>
    </div>

    <?php if (empty($solicitacoes_recebidas)): ?>
      <div class="empty-state">
        <div class="empty-icon">🤝</div>
        <h3>Nenhuma solicitação pendente</h3>
        <p>Quando alguém solicitar a devolução de um dos seus objetos, aparecerá aqui.</p>
      </div>
    <?php else: ?>
      <div class="tabela-wrapper">
        <table class="data-table" id="tabela-solicitacoes">
          <thead>
            <tr>
              <th>Solicitante</th>
              <th>Objeto</th>
              <th>Mensagem</th>
              <th>Data</th>
              <th>Ação</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($solicitacoes_recebidas as $sol): ?>
            <tr>
              <td><strong><?= e($sol["solicitante_nome"]) ?></strong></td>
              <td>
                <a href="/achae/objetos/detalhes.php?id=<?= $sol["id_objeto"] ?>"
                   style="color:var(--purple); font-weight:600; font-size:12px;">
                  <?= e($sol["objeto_titulo"]) ?>
                </a>
              </td>
              <td style="max-width:220px; color:var(--muted);">
                <?= e(mb_substr($sol["mensagem"], 0, 80)) ?>
                <?= mb_strlen($sol["mensagem"]) > 80 ? "…" : "" ?>
              </td>
              <td><?= formatar_data($sol["data_solicitacao"]) ?></td>
              <td>
                <a href="/achae/solicitacoes/responder.php?id=<?= $sol["id_solicitacao"] ?>"
                   class="action-link view"
                   id="btn-responder-<?= $sol["id_solicitacao"] ?>">
                  Responder
                </a>
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
