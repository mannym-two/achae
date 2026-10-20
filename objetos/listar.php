<?php
// ============================================================
// objetos/listar.php — Listagem pública de objetos
//
// Permite buscar por texto e filtrar por tipo, categoria
// e status. Qualquer visitante pode ver esta página.
// ============================================================
require_once __DIR__ . "/../auth/config.php";

// ============================================================
// Lê e sanitiza os filtros vindos da URL (?busca=...&tipo=...)
// ============================================================
$busca      = trim((string)($_GET["busca"]     ?? ""));
$filtro_tipo    = trim((string)($_GET["tipo"]      ?? ""));
$filtro_status  = trim((string)($_GET["status"]    ?? "aberto")); // padrão: só abertos
$filtro_cat     = (int)($_GET["categoria"]  ?? 0);
$so_meus        = !empty($_SESSION["usuario_id"]) && !empty($_GET["meus"]);

// Paginação: 12 objetos por página
$por_pagina = 12;
$pagina_atual = max(1, (int)($_GET["pagina"] ?? 1));
$offset       = ($pagina_atual - 1) * $por_pagina;

// ============================================================
// Monta a consulta dinâmica com filtros
// Usamos array de condições para montar o WHERE com segurança
// ============================================================
$condicoes  = ["o.status != \"removido\""];
$parametros = [];

if ($filtro_status !== "" && in_array($filtro_status, ["aberto","resolvido","todos"], true)) {
    if ($filtro_status !== "todos") {
        $condicoes[]    = "o.status = :status";
        $parametros["status"] = $filtro_status;
    }
    // "todos" remove o filtro de status (mas ainda exclui removidos)
}

if ($filtro_tipo !== "" && in_array($filtro_tipo, ["perdido","encontrado"], true)) {
    $condicoes[]    = "o.tipo = :tipo";
    $parametros["tipo"] = $filtro_tipo;
}

if ($filtro_cat > 0) {
    $condicoes[]    = "o.id_categoria = :cat";
    $parametros["cat"] = $filtro_cat;
}

if ($busca !== "") {
    // LIKE com % nos dois lados faz busca parcial no título e na descrição
    $condicoes[]    = "(o.titulo LIKE :busca OR o.descricao LIKE :busca)";
    $parametros["busca"] = "%" . $busca . "%";
}

if ($so_meus) {
    $condicoes[]    = "o.id_usuario = :uid";
    $parametros["uid"] = (int)$_SESSION["usuario_id"];
}

$where = "WHERE " . implode(" AND ", $condicoes);

// Conta o total de resultados para a paginação
$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM objeto o
    JOIN categoria c ON o.id_categoria = c.id_categoria
    JOIN usuario   u ON o.id_usuario   = u.id_usuario
    $where
");
$stmt->execute($parametros);
$total_resultados = (int)$stmt->fetchColumn();
$total_paginas    = max(1, (int)ceil($total_resultados / $por_pagina));
$pagina_atual     = min($pagina_atual, $total_paginas); // ajusta se passou do limite

// Busca os objetos da página atual
$stmt = $pdo->prepare("
    SELECT
        o.id_objeto, o.titulo, o.tipo, o.status, o.local,
        o.foto, o.data_publicacao, o.data_ocorrencia,
        c.nome AS categoria,
        u.nome AS publicado_por
    FROM objeto o
    JOIN categoria c ON o.id_categoria = c.id_categoria
    JOIN usuario   u ON o.id_usuario   = u.id_usuario
    $where
    ORDER BY o.data_publicacao DESC
    LIMIT :limit OFFSET :offset
");
// LIMIT e OFFSET não podem ser passados como string, precisam de bindValue com tipo inteiro
foreach ($parametros as $k => $v) {
    $stmt->bindValue(":$k", $v);
}
$stmt->bindValue(":limit",  $por_pagina, PDO::PARAM_INT);
$stmt->bindValue(":offset", $offset,     PDO::PARAM_INT);
$stmt->execute();
$objetos = $stmt->fetchAll();

// Carrega categorias para o filtro
$categorias = $pdo->query("SELECT id_categoria, nome FROM categoria ORDER BY nome")->fetchAll();

// Usuário logado?
$logado     = !empty($_SESSION["usuario_id"]);
$nome_user  = $_SESSION["usuario_nome"] ?? "";
$inicial    = $logado ? mb_strtoupper(mb_substr($nome_user, 0, 1, "UTF-8"), "UTF-8") : "";
$eh_admin   = $logado && (($_SESSION["usuario_perfil"] ?? "") === "admin");

// Monta URL base para os links de paginação (mantendo os filtros)
function url_paginacao(int $pagina): string {
    $params = $_GET;
    $params["pagina"] = $pagina;
    return "/achae/objetos/listar.php?" . http_build_query($params);
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Achados e perdidos do campus. Veja objetos perdidos e encontrados.">
  <title>Objetos | Achaê</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/achae/assets/style.css">
</head>
<body class="page-wrapper">

<!-- TOPBAR -->
<nav class="topbar" role="navigation" aria-label="Navegação principal">
  <a class="topbar-brand" href="/achae/index.php">
    <span>🔍</span> Achaê
  </a>

  <ul class="topbar-nav">
    <li><a href="/achae/index.php">Início</a></li>
    <li><a href="/achae/objetos/listar.php" class="active">Objetos</a></li>
    <?php if ($logado): ?>
      <li><a href="/achae/objetos/publicar.php">Publicar</a></li>
      <li><a href="/achae/solicitacoes/minhas.php">Solicitações</a></li>
    <?php endif; ?>
    <?php if ($eh_admin): ?>
      <li><a href="/achae/admin/painel.php">Admin</a></li>
    <?php endif; ?>
  </ul>

  <div class="topbar-right">
    <?php if ($logado): ?>
      <div class="topbar-user">
        <div class="avatar avatar-sm" aria-hidden="true"><?= e($inicial) ?></div>
        <?= e(explode(" ", $nome_user)[0]) ?>
      </div>
      <a href="<?= $eh_admin ? "/achae/admin/painel.php" : "/achae/auth/painel.php" ?>"
         class="btn-ghost">Painel</a>
      <a href="/achae/auth/logout.php" class="btn-ghost" id="btn-sair">Sair</a>
    <?php else: ?>
      <a href="/achae/auth/login.php"    class="btn-ghost">Entrar</a>
      <a href="/achae/auth/cadastro.php" class="btn-primary-sm">Criar conta</a>
    <?php endif; ?>
  </div>
</nav>

<!-- CONTEÚDO -->
<main class="page-body">

  <!-- Cabeçalho da página -->
  <div class="page-header">
    <div class="page-header-text">
      <h1>
        <?php if ($so_meus): ?>
          Minhas Publicações
        <?php elseif ($filtro_tipo === "perdido"): ?>
          🔴 Objetos Perdidos
        <?php elseif ($filtro_tipo === "encontrado"): ?>
          🟢 Objetos Encontrados
        <?php else: ?>
          📦 Todos os Objetos
        <?php endif; ?>
      </h1>
      <p>
        <?= $total_resultados ?> resultado<?= $total_resultados !== 1 ? "s" : "" ?> encontrado<?= $total_resultados !== 1 ? "s" : "" ?>
        <?= $busca !== "" ? " para "" . e($busca) . """ : "" ?>
      </p>
    </div>
    <?php if ($logado): ?>
      <a href="/achae/objetos/publicar.php" class="btn-primary-sm" id="btn-publicar">
        ＋ Publicar Objeto
      </a>
    <?php endif; ?>
  </div>

  <!-- Card principal com filtros + grid de resultados -->
  <div class="section-card">

    <!-- Barra de filtros -->
    <form method="get" action="/achae/objetos/listar.php" id="form-filtros">
      <div class="filters-bar">

        <!-- Busca por texto -->
        <input type="search"
               name="busca"
               class="filter-input"
               id="input-busca"
               placeholder="Buscar por título ou descrição..."
               value="<?= e($busca) ?>"
               aria-label="Buscar objetos">

        <!-- Filtro por tipo -->
        <select name="tipo" class="filter-select" id="select-tipo" aria-label="Filtrar por tipo">
          <option value="">Todos os tipos</option>
          <option value="perdido"    <?= $filtro_tipo === "perdido"    ? "selected" : "" ?>>🔴 Perdidos</option>
          <option value="encontrado" <?= $filtro_tipo === "encontrado" ? "selected" : "" ?>>🟢 Encontrados</option>
        </select>

        <!-- Filtro por status -->
        <select name="status" class="filter-select" id="select-status" aria-label="Filtrar por status">
          <option value="aberto"    <?= $filtro_status === "aberto"    ? "selected" : "" ?>>Em aberto</option>
          <option value="resolvido" <?= $filtro_status === "resolvido" ? "selected" : "" ?>>Resolvidos</option>
          <option value="todos"     <?= $filtro_status === "todos"     ? "selected" : "" ?>>Todos</option>
        </select>

        <!-- Filtro por categoria -->
        <select name="categoria" class="filter-select" id="select-categoria" aria-label="Filtrar por categoria">
          <option value="">Todas as categorias</option>
          <?php foreach ($categorias as $cat): ?>
            <option value="<?= $cat["id_categoria"] ?>"
              <?= $filtro_cat === (int)$cat["id_categoria"] ? "selected" : "" ?>>
              <?= e($cat["nome"]) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <button type="submit" class="filter-btn" id="btn-filtrar">Filtrar</button>

        <!-- Limpar filtros -->
        <?php $tem_filtro = $busca !== "" || $filtro_tipo !== "" || $filtro_cat > 0 || $filtro_status !== "aberto"; ?>
        <?php if ($tem_filtro): ?>
          <a href="/achae/objetos/listar.php" class="filter-reset" id="btn-limpar">✕ Limpar</a>
        <?php endif; ?>
      </div>
    </form>

    <!-- Grid de objetos ou estado vazio -->
    <?php if (empty($objetos)): ?>
      <div class="empty-state" id="estado-vazio">
        <div class="empty-icon">🔎</div>
        <h3>Nenhum objeto encontrado</h3>
        <p>
          <?php if ($tem_filtro): ?>
            Nenhum objeto corresponde aos filtros aplicados. Tente limpar a busca.
          <?php else: ?>
            Ainda não há objetos publicados. Que tal publicar o primeiro?
          <?php endif; ?>
        </p>
        <?php if ($logado): ?>
          <a href="/achae/objetos/publicar.php" class="btn-primary-sm" id="btn-pub-vazio">
            Publicar objeto
          </a>
        <?php else: ?>
          <a href="/achae/auth/cadastro.php" class="btn-primary-sm" id="btn-cad-vazio">
            Criar conta para publicar
          </a>
        <?php endif; ?>
      </div>
    <?php else: ?>

      <!-- Grid de cards -->
      <div class="objects-grid" role="list">
        <?php foreach ($objetos as $obj): ?>
        <a class="object-card"
           href="/achae/objetos/detalhes.php?id=<?= $obj["id_objeto"] ?>"
           id="card-obj-<?= $obj["id_objeto"] ?>"
           role="listitem"
           aria-label="<?= e($obj["titulo"]) ?>">

          <!-- Foto do objeto -->
          <div class="object-thumb">
            <?php if ($obj["foto"]): ?>
              <img src="/achae/uploads/<?= e($obj["foto"]) ?>"
                   alt="Foto: <?= e($obj["titulo"]) ?>"
                   loading="lazy">
            <?php else: ?>
              <div class="object-thumb-placeholder" aria-hidden="true">📦</div>
            <?php endif; ?>

            <!-- Badges de tipo e status sobre a foto -->
            <div class="object-thumb-badges">
              <span class="badge badge-<?= e($obj["tipo"]) ?>">
                <?= $obj["tipo"] === "perdido" ? "🔴 Perdido" : "🟢 Encontrado" ?>
              </span>
              <?php if ($obj["status"] === "resolvido"): ?>
                <span class="badge badge-resolvido">✅ Resolvido</span>
              <?php endif; ?>
            </div>
          </div>

          <!-- Dados do objeto -->
          <div class="object-body">
            <div class="object-title"><?= e($obj["titulo"]) ?></div>

            <div class="object-meta">
              <span>🏷️</span>
              <span><?= e($obj["categoria"]) ?></span>
            </div>

            <div class="object-meta">
              <span>📍</span>
              <span><?= e($obj["local"]) ?></span>
            </div>

            <div class="object-meta">
              <span>👤</span>
              <span><?= e($obj["publicado_por"]) ?></span>
            </div>
          </div>

          <!-- Rodapé do card -->
          <div class="object-footer">
            <small><?= date("d/m/Y", strtotime($obj["data_publicacao"])) ?></small>
            <span class="object-cta">Ver detalhes →</span>
          </div>

        </a>
        <?php endforeach; ?>
      </div>

      <!-- Paginação -->
      <?php if ($total_paginas > 1): ?>
      <nav class="pagination" aria-label="Paginação">
        <?php if ($pagina_atual > 1): ?>
          <a href="<?= e(url_paginacao($pagina_atual - 1)) ?>" aria-label="Página anterior">← Ant.</a>
        <?php else: ?>
          <span class="disabled">← Ant.</span>
        <?php endif; ?>

        <?php
        // Exibe no máximo 5 números de página ao redor da atual
        $inicio = max(1, $pagina_atual - 2);
        $fim    = min($total_paginas, $pagina_atual + 2);
        if ($inicio > 1): ?>
          <a href="<?= e(url_paginacao(1)) ?>">1</a>
          <?php if ($inicio > 2): ?><span>…</span><?php endif; ?>
        <?php endif; ?>

        <?php for ($p = $inicio; $p <= $fim; $p++): ?>
          <?php if ($p === $pagina_atual): ?>
            <span class="current" aria-current="page"><?= $p ?></span>
          <?php else: ?>
            <a href="<?= e(url_paginacao($p)) ?>"><?= $p ?></a>
          <?php endif; ?>
        <?php endfor; ?>

        <?php if ($fim < $total_paginas): ?>
          <?php if ($fim < $total_paginas - 1): ?><span>…</span><?php endif; ?>
          <a href="<?= e(url_paginacao($total_paginas)) ?>"><?= $total_paginas ?></a>
        <?php endif; ?>

        <?php if ($pagina_atual < $total_paginas): ?>
          <a href="<?= e(url_paginacao($pagina_atual + 1)) ?>" aria-label="Próxima página">Próx. →</a>
        <?php else: ?>
          <span class="disabled">Próx. →</span>
        <?php endif; ?>
      </nav>
      <?php endif; ?>

    <?php endif; ?>
  </div>

</main>
</body>
</html>
