<?php
// ============================================================
// index.php — Página inicial do Achaê
//
// Esta é a primeira página que o usuário vê.
// Mostra: hero, estatísticas reais do banco, como funciona
// e as publicações mais recentes.
// ============================================================

// Define o título antes de incluir o header
$titulo_pagina = 'Achaê — Achados e Perdidos do Campus';

// Inclui o cabeçalho (inicia sessão, carrega banco, gera HTML até <main>)
require_once __DIR__ . '/includes/header.php';

// ============================================================
// Busca dados do banco para exibir na página
// ============================================================
$pdo = conectar_banco();

// Total de objetos cadastrados (sem contar os removidos por admin)
$total_objetos = (int) $pdo->query(
    "SELECT COUNT(*) FROM objeto WHERE status != 'removido'"
)->fetchColumn();

// Total de casos que já foram resolvidos
$total_resolvidos = (int) $pdo->query(
    "SELECT COUNT(*) FROM objeto WHERE status = 'resolvido'"
)->fetchColumn();

// Total de usuários com conta ativa
$total_usuarios = (int) $pdo->query(
    "SELECT COUNT(*) FROM usuario WHERE status = 'ativo'"
)->fetchColumn();

// Busca os 6 objetos mais recentes ainda abertos
// JOIN: une as tabelas objeto, categoria e usuario numa só consulta
$stmt = $pdo->prepare("
    SELECT
        o.id_objeto,
        o.titulo,
        o.tipo,
        o.local,
        o.foto,
        o.data_publicacao,
        c.nome  AS categoria,
        u.nome  AS publicado_por
    FROM objeto o
    JOIN categoria c ON o.id_categoria = c.id_categoria
    JOIN usuario   u ON o.id_usuario   = u.id_usuario
    WHERE o.status = 'aberto'
    ORDER BY o.data_publicacao DESC
    LIMIT 6
");
$stmt->execute();
$objetos_recentes = $stmt->fetchAll();
?>

<!-- ============================================================
     HERO — primeira coisa que o usuário vê
     ============================================================ -->
<section class="hero" id="inicio" aria-label="Seção de destaque">
    <div class="hero__conteudo fade-in">

        <!-- Etiqueta de contexto -->
        <div class="hero__tag">🎓 Sistema do Campus</div>

        <h1>
            Perdeu algo?<br>
            <span>Achou algo de alguém?</span>
        </h1>

        <p>
            O <strong>Achaê</strong> conecta quem perdeu com quem encontrou no campus.
            Simples, rápido e seguro.
        </p>

        <!-- Botões de ação -->
        <div class="hero__botoes">
            <a href="<?= BASE_URL ?>/objetos/listar.php"
               class="btn btn-lg"
               style="background:white; color:var(--azul);"
               id="btn-hero-ver">
                🔍 Ver Objetos
            </a>

            <?php if ($logado): ?>
                <!-- Usuário logado: vai direto para publicar -->
                <a href="<?= BASE_URL ?>/objetos/publicar.php"
                   class="btn btn-lg btn-contorno-branco"
                   id="btn-hero-publicar">
                    📢 Publicar Objeto
                </a>
            <?php else: ?>
                <!-- Não logado: convida a criar conta -->
                <a href="<?= BASE_URL ?>/auth/cadastro.php"
                   class="btn btn-lg btn-contorno-branco"
                   id="btn-hero-cadastrar">
                    ✨ Criar Conta Grátis
                </a>
            <?php endif; ?>
        </div>

    </div>

    <!-- Onda SVG que suaviza a transição entre o hero e a próxima seção -->
    <div class="hero__onda" aria-hidden="true">
        <svg viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <path d="M0,40 C360,80 1080,0 1440,40 L1440,80 L0,80 Z" fill="#F1F5F9"/>
        </svg>
    </div>
</section>

<!-- ============================================================
     STATS — contadores com dados reais do banco de dados
     ============================================================ -->
<section class="stats" id="estatisticas" aria-label="Estatísticas do sistema">
    <div class="stats__grid">
        <div class="stats__item">
            <div class="stats__numero" id="stat-objetos"><?= $total_objetos ?></div>
            <div class="stats__rotulo">📦 Objetos cadastrados</div>
        </div>
        <div class="stats__item">
            <div class="stats__numero" id="stat-resolvidos"><?= $total_resolvidos ?></div>
            <div class="stats__rotulo">✅ Casos resolvidos</div>
        </div>
        <div class="stats__item">
            <div class="stats__numero" id="stat-usuarios"><?= $total_usuarios ?></div>
            <div class="stats__rotulo">👤 Usuários ativos</div>
        </div>
    </div>
</section>

<!-- ============================================================
     COMO FUNCIONA — 3 passos explicando o fluxo do sistema
     ============================================================ -->
<section class="secao" id="como-funciona" style="background: var(--superficie);" aria-label="Como funciona o Achaê">
    <div class="container">
        <div class="secao-titulo">
            <h2>Como funciona</h2>
            <p>Três passos simples para recuperar ou devolver um objeto</p>
        </div>

        <div class="features__grid">

            <div class="feature-card">
                <span class="feature-card__numero" aria-hidden="true">1</span>
                <div class="feature-card__icone feature-card__icone--azul" aria-hidden="true">📢</div>
                <h3>Publique</h3>
                <p>
                    Cadastre o objeto perdido ou encontrado com foto, descrição e local.
                    Leva menos de 2 minutos.
                </p>
            </div>

            <div class="feature-card">
                <span class="feature-card__numero" aria-hidden="true">2</span>
                <div class="feature-card__icone feature-card__icone--amarelo" aria-hidden="true">🔗</div>
                <h3>Conecte</h3>
                <p>
                    Quem reconhecer o objeto envia uma solicitação de devolução com uma mensagem.
                    O publicante aceita ou recusa.
                </p>
            </div>

            <div class="feature-card">
                <span class="feature-card__numero" aria-hidden="true">3</span>
                <div class="feature-card__icone feature-card__icone--verde" aria-hidden="true">✅</div>
                <h3>Resolva</h3>
                <p>
                    Após a aceitação, os contatos ficam visíveis. Combinem a devolução
                    e marque o caso como resolvido.
                </p>
            </div>

        </div>
    </div>
</section>

<!-- ============================================================
     OBJETOS RECENTES — últimas publicações em aberto
     ============================================================ -->
<section class="secao" id="recentes" aria-label="Objetos recentemente publicados">
    <div class="container">
        <div class="secao-titulo">
            <h2>Publicações recentes</h2>
            <p>Os últimos objetos cadastrados no campus</p>
        </div>

        <?php if (empty($objetos_recentes)): ?>
            <!-- Estado vazio: ainda não há objetos no banco -->
            <div class="estado-vazio" id="estado-vazio-recentes">
                <div class="estado-vazio__icone" aria-hidden="true">📭</div>
                <h3>Nenhum objeto publicado ainda</h3>
                <p>Seja o primeiro a publicar um objeto perdido ou encontrado!</p>
                <div class="mt-3">
                    <a href="<?= BASE_URL ?>/auth/cadastro.php"
                       class="btn btn-primario"
                       id="btn-vazio-comecar">
                        Começar agora
                    </a>
                </div>
            </div>

        <?php else: ?>
            <!-- Grid de cards com os objetos mais recentes -->
            <div class="objetos-grid" role="list">
                <?php foreach ($objetos_recentes as $obj): ?>
                <article class="objeto-card" role="listitem" id="card-objeto-<?= $obj['id_objeto'] ?>">

                    <!-- Área da foto -->
                    <div class="objeto-card__foto">
                        <?php if ($obj['foto']): ?>
                            <img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($obj['foto']) ?>"
                                 alt="Foto do objeto: <?= htmlspecialchars($obj['titulo']) ?>"
                                 loading="lazy">
                        <?php else: ?>
                            <!-- Ícone genérico quando não há foto -->
                            <div class="objeto-card__foto-placeholder" aria-hidden="true">📦</div>
                        <?php endif; ?>

                        <!-- Badge de tipo (perdido ou encontrado) -->
                        <div class="objeto-card__badges">
                            <span class="badge badge--<?= htmlspecialchars($obj['tipo']) ?>">
                                <?= $obj['tipo'] === 'perdido' ? '🔴 Perdido' : '🟢 Encontrado' ?>
                            </span>
                        </div>
                    </div>

                    <!-- Corpo do card: título e informações -->
                    <div class="objeto-card__corpo">
                        <div class="objeto-card__titulo">
                            <?= htmlspecialchars($obj['titulo']) ?>
                        </div>
                        <div class="objeto-card__meta">
                            <span>🏷️</span>
                            <span><?= htmlspecialchars($obj['categoria']) ?></span>
                        </div>
                        <div class="objeto-card__meta">
                            <span>📍</span>
                            <span><?= htmlspecialchars($obj['local']) ?></span>
                        </div>
                    </div>

                    <!-- Rodapé do card: data e botão para ver detalhes -->
                    <div class="objeto-card__rodape">
                        <small class="texto-muted">
                            <?= date('d/m/Y', strtotime($obj['data_publicacao'])) ?>
                        </small>
                        <a href="<?= BASE_URL ?>/objetos/detalhes.php?id=<?= $obj['id_objeto'] ?>"
                           class="btn btn-secundario btn-sm"
                           id="btn-detalhes-<?= $obj['id_objeto'] ?>">
                            Ver detalhes →
                        </a>
                    </div>

                </article>
                <?php endforeach; ?>
            </div>

            <!-- Link para ver todos os objetos cadastrados -->
            <div class="texto-centro mt-4">
                <a href="<?= BASE_URL ?>/objetos/listar.php"
                   class="btn btn-contorno"
                   id="btn-ver-todos">
                    Ver todos os objetos →
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ============================================================
     CTA BANNER — convite para criar conta (só para não logados)
     ============================================================ -->
<?php if (!$logado): ?>
<div class="container">
    <div class="cta-banner" id="cta-criar-conta" role="complementary">
        <h2>Faça parte da comunidade</h2>
        <p>Crie sua conta gratuitamente e ajude a devolver objetos aos seus donos.</p>
        <a href="<?= BASE_URL ?>/auth/cadastro.php"
           class="btn btn-lg"
           style="background:white; color:var(--azul);"
           id="btn-cta-cadastrar">
            ✨ Criar conta grátis
        </a>
    </div>
</div>
<?php endif; ?>

<?php
// Inclui o rodapé (fecha <main>, exibe footer, inclui JS e fecha HTML)
require_once __DIR__ . '/includes/footer.php';
?>