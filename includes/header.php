<?php
// ============================================================
// includes/header.php — Cabeçalho compartilhado
//
// Este arquivo é incluído no INÍCIO de todas as páginas.
// Ele cuida de:
//   - Iniciar a sessão do PHP
//   - Carregar a conexão com o banco
//   - Gerar o HTML do <head> (meta tags, CSS)
//   - Gerar a navbar responsiva
//   - Exibir mensagens flash (feedback de ações)
// ============================================================

// Inicia a sessão apenas se ainda não foi iniciada
// (isso evita o erro "session already started")
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Carrega a função conectar_banco()
// dirname(__DIR__) sobe um nível: de includes/ para a raiz do projeto
require_once dirname(__DIR__) . '/config/database.php';

// Caminho base da aplicação na URL
// Mude para '' se o projeto estiver na raiz do servidor (ex.: em produção)
if (!defined('BASE_URL')) {
    define('BASE_URL', '/achae');
}

// Título padrão da aba do navegador
// Cada página pode definir $titulo_pagina antes de incluir este arquivo
if (!isset($titulo_pagina)) {
    $titulo_pagina = 'Achaê — Achados e Perdidos do Campus';
}

// Detecta a página e a pasta atual para marcar o link ativo na navbar
$pagina_atual = basename($_SERVER['PHP_SELF']);           // ex.: "listar.php"
$pasta_atual  = basename(dirname($_SERVER['PHP_SELF'])); // ex.: "objetos"

// Verifica o status de login a partir da sessão
$logado         = isset($_SESSION['id_usuario']);
$eh_admin       = $logado && (($_SESSION['perfil'] ?? '') === 'admin');
$nome_usuario   = $logado ? ($_SESSION['nome'] ?? '') : '';

// Pega só a primeira letra do nome para o avatar
$inicial = $logado && $nome_usuario
    ? mb_strtoupper(mb_substr($nome_usuario, 0, 1, 'UTF-8'), 'UTF-8')
    : '';

// Pega só o primeiro nome para exibir no botão do dropdown
$primeiro_nome = $logado
    ? htmlspecialchars(explode(' ', trim($nome_usuario))[0])
    : '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Achaê — Sistema de achados e perdidos do campus. Publique objetos perdidos ou encontrados e conecte-se com quem pode ajudar.">

    <!-- Título da página -->
    <title><?= htmlspecialchars($titulo_pagina) ?></title>

    <!-- Pré-conexão com o Google Fonts para carregamento mais rápido -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Folha de estilos principal -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>
<body>

<!-- ============================================================
     NAVBAR — barra de navegação fixa no topo
     ============================================================ -->
<nav class="navbar" id="navbar" role="navigation" aria-label="Navegação principal">
    <div class="navbar__inner">

        <!-- Logo do sistema -->
        <a href="<?= BASE_URL ?>/index.php" class="navbar__logo" id="logo-navbar">
            <span class="navbar__logo-icone" aria-hidden="true">🔍</span>
            Achaê
        </a>

        <!-- Links de navegação (visíveis apenas em telas maiores) -->
        <ul class="navbar__nav" role="list">
            <li>
                <a href="<?= BASE_URL ?>/index.php"
                   id="nav-inicio"
                   <?= ($pagina_atual === 'index.php') ? 'class="ativo" aria-current="page"' : '' ?>>
                    Início
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/objetos/listar.php"
                   id="nav-objetos"
                   <?= ($pasta_atual === 'objetos') ? 'class="ativo" aria-current="page"' : '' ?>>
                    Objetos
                </a>
            </li>
            <?php if ($logado): ?>
            <li>
                <a href="<?= BASE_URL ?>/objetos/publicar.php"
                   id="nav-publicar"
                   <?= ($pagina_atual === 'publicar.php') ? 'class="ativo" aria-current="page"' : '' ?>>
                    Publicar
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/solicitacoes/minhas.php"
                   id="nav-solicitacoes"
                   <?= ($pasta_atual === 'solicitacoes') ? 'class="ativo" aria-current="page"' : '' ?>>
                    Solicitações
                </a>
            </li>
            <?php endif; ?>
            <?php if ($eh_admin): ?>
            <li>
                <a href="<?= BASE_URL ?>/admin/painel.php"
                   id="nav-admin"
                   <?= ($pasta_atual === 'admin') ? 'class="ativo" aria-current="page"' : '' ?>>
                    ⚙️ Admin
                </a>
            </li>
            <?php endif; ?>
        </ul>

        <!-- Área de ações: botões de login/cadastro ou dropdown do usuário -->
        <div class="navbar__acoes">
            <?php if ($logado): ?>

                <!-- Dropdown do usuário logado -->
                <div class="dropdown" id="dropdown-usuario">
                    <button class="dropdown__toggle"
                            id="dropdown-toggle"
                            aria-expanded="false"
                            aria-haspopup="true"
                            aria-controls="dropdown-menu">
                        <!-- Avatar com a inicial do nome -->
                        <span class="dropdown__avatar" aria-hidden="true">
                            <?= htmlspecialchars($inicial) ?>
                        </span>
                        <?= $primeiro_nome ?>
                        <span aria-hidden="true">▾</span>
                    </button>

                    <div class="dropdown__menu" id="dropdown-menu" role="menu">
                        <a href="<?= BASE_URL ?>/auth/perfil.php" role="menuitem" id="menu-perfil">
                            👤 Meu Perfil
                        </a>
                        <a href="<?= BASE_URL ?>/objetos/listar.php?meus=1" role="menuitem" id="menu-publicacoes">
                            📦 Minhas Publicações
                        </a>
                        <a href="<?= BASE_URL ?>/solicitacoes/minhas.php" role="menuitem" id="menu-solicitacoes">
                            📋 Minhas Solicitações
                        </a>
                        <?php if ($eh_admin): ?>
                        <div class="dropdown__separador" role="separator"></div>
                        <a href="<?= BASE_URL ?>/admin/painel.php" role="menuitem" id="menu-admin">
                            ⚙️ Painel Admin
                        </a>
                        <?php endif; ?>
                        <div class="dropdown__separador" role="separator"></div>
                        <a href="<?= BASE_URL ?>/auth/logout.php" class="sair" role="menuitem" id="menu-sair">
                            🚪 Sair
                        </a>
                    </div>
                </div>

            <?php else: ?>

                <!-- Usuário não logado: mostra botões de entrar e cadastrar -->
                <a href="<?= BASE_URL ?>/auth/login.php"
                   class="btn btn-secundario btn-sm"
                   id="btn-navbar-login">
                    Entrar
                </a>
                <a href="<?= BASE_URL ?>/auth/cadastro.php"
                   class="btn btn-primario btn-sm"
                   id="btn-navbar-cadastro">
                    Cadastrar
                </a>

            <?php endif; ?>
        </div>

        <!-- Botão hamburguer: só aparece em telas pequenas (CSS: display:none no desktop) -->
        <button class="navbar__hamburguer"
                id="hamburguer"
                aria-label="Abrir menu de navegação"
                aria-expanded="false"
                aria-controls="menu-mobile">
            ☰
        </button>

    </div>
</nav>

<!-- Menu de navegação para dispositivos móveis -->
<div class="navbar__mobile"
     id="menu-mobile"
     role="navigation"
     aria-label="Menu mobile">

    <a href="<?= BASE_URL ?>/index.php">🏠 Início</a>
    <a href="<?= BASE_URL ?>/objetos/listar.php">📦 Objetos</a>

    <?php if ($logado): ?>
        <a href="<?= BASE_URL ?>/objetos/publicar.php">📢 Publicar Objeto</a>
        <a href="<?= BASE_URL ?>/solicitacoes/minhas.php">📋 Minhas Solicitações</a>
        <?php if ($eh_admin): ?>
            <a href="<?= BASE_URL ?>/admin/painel.php">⚙️ Painel Admin</a>
        <?php endif; ?>
        <hr>
        <a href="<?= BASE_URL ?>/auth/perfil.php">👤 Meu Perfil</a>
        <a href="<?= BASE_URL ?>/auth/logout.php" class="sair">🚪 Sair</a>
    <?php else: ?>
        <hr>
        <a href="<?= BASE_URL ?>/auth/login.php">🔑 Entrar</a>
        <a href="<?= BASE_URL ?>/auth/cadastro.php">✨ Criar conta</a>
    <?php endif; ?>
</div>

<!-- ============================================================
     FLASH MESSAGES — mensagens de feedback das ações
     Aparecem no topo da página após redirecionamentos
     Tipos: sucesso, erro, aviso, info
     ============================================================ -->
<?php if (isset($_SESSION['flash'])): ?>
<div class="flash-container" role="alert" aria-live="polite">
    <div class="flash flash--<?= htmlspecialchars($_SESSION['flash']['tipo']) ?>">
        <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
    </div>
</div>
<?php
    // Limpa a mensagem depois de exibir (para não aparecer novamente ao atualizar a página)
    unset($_SESSION['flash']);
endif;
?>

<!-- ============================================================
     INÍCIO DO CONTEÚDO PRINCIPAL
     Cada página coloca seu conteúdo entre o header e o footer
     ============================================================ -->
<main id="conteudo-principal" tabindex="-1">