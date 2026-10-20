<?php
// ============================================================
// includes/footer.php — Rodapé compartilhado
//
// Este arquivo é incluído no FINAL de todas as páginas.
// Ele fecha a tag <main>, exibe o footer e inclui o JavaScript.
// ============================================================
?>

</main>
<!-- fim do conteúdo principal -->

<!-- ============================================================
     FOOTER — rodapé do site
     ============================================================ -->
<footer class="footer" role="contentinfo">
    <div class="footer__inner">

        <!-- Topo do footer: logo + links -->
        <div class="footer__topo">

            <!-- Descrição do sistema -->
            <div>
                <div class="footer__logo">🔍 Achaê</div>
                <p class="footer__desc">
                    Sistema de achados e perdidos do campus.
                    Conectamos quem perdeu com quem encontrou.
                </p>
            </div>

            <!-- Colunas de links -->
            <div class="footer__links">
                <div class="footer__col">
                    <h4>Objetos</h4>
                    <a href="<?= BASE_URL ?>/objetos/listar.php">Ver todos</a>
                    <a href="<?= BASE_URL ?>/objetos/listar.php?tipo=perdido">Perdidos</a>
                    <a href="<?= BASE_URL ?>/objetos/listar.php?tipo=encontrado">Encontrados</a>
                </div>

                <div class="footer__col">
                    <h4>Conta</h4>
                    <?php if (isset($_SESSION['id_usuario'])): ?>
                        <a href="<?= BASE_URL ?>/objetos/publicar.php">Publicar objeto</a>
                        <a href="<?= BASE_URL ?>/solicitacoes/minhas.php">Minhas solicitações</a>
                        <a href="<?= BASE_URL ?>/auth/perfil.php">Meu perfil</a>
                        <a href="<?= BASE_URL ?>/auth/logout.php">Sair</a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/auth/login.php">Entrar</a>
                        <a href="<?= BASE_URL ?>/auth/cadastro.php">Criar conta</a>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- Rodapé: copyright -->
        <div class="footer__rodape">
            <p>
                &copy; <?= date('Y') ?> Achaê &mdash;
                Projeto acadêmico. Desenvolvido com PHP, MySQL e XAMPP.
            </p>
        </div>

    </div>
</footer>

<!-- ============================================================
     JAVASCRIPT
     Carregado no final para não atrasar o carregamento do HTML
     ============================================================ -->
<script src="<?= BASE_URL ?>/public/js/main.js"></script>

</body>
</html>