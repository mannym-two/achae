// ============================================================
// main.js — JavaScript principal do Achaê
// public/js/main.js
//
// Este arquivo cuida das interações visuais do site.
// É carregado no final do footer.php para não atrasar o carregamento.
// ============================================================

document.addEventListener('DOMContentLoaded', function () {

    // --------------------------------------------------------
    // 1. MENU HAMBURGUER (mobile)
    // Alterna a classe "aberto" no menu mobile ao clicar no botão
    // --------------------------------------------------------
    var hamburguer = document.getElementById('hamburguer');
    var menuMobile = document.getElementById('menu-mobile');

    if (hamburguer && menuMobile) {
        hamburguer.addEventListener('click', function () {
            var estaAberto = menuMobile.classList.toggle('aberto');
            // Atualiza o atributo aria-expanded para acessibilidade
            hamburguer.setAttribute('aria-expanded', estaAberto ? 'true' : 'false');
            // Muda o ícone do botão
            hamburguer.textContent = estaAberto ? '✕' : '☰';
        });
    }

    // --------------------------------------------------------
    // 2. DROPDOWN DO USUÁRIO (desktop)
    // Abre/fecha o menu suspenso ao clicar no botão do usuário
    // --------------------------------------------------------
    var dropdownToggle = document.getElementById('dropdown-toggle');
    var dropdownMenu   = document.getElementById('dropdown-menu');

    if (dropdownToggle && dropdownMenu) {
        // Abre ou fecha ao clicar no botão
        dropdownToggle.addEventListener('click', function (e) {
            e.stopPropagation(); // impede o clique de "vazar" para o document
            var estaAberto = dropdownMenu.classList.toggle('aberto');
            dropdownToggle.setAttribute('aria-expanded', estaAberto ? 'true' : 'false');
        });

        // Fecha ao clicar em qualquer lugar fora do dropdown
        document.addEventListener('click', function () {
            dropdownMenu.classList.remove('aberto');
            dropdownToggle.setAttribute('aria-expanded', 'false');
        });

        // Fecha ao pressionar ESC
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                dropdownMenu.classList.remove('aberto');
                dropdownToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // --------------------------------------------------------
    // 3. FLASH MESSAGES — desaparece automaticamente
    // Após 5 segundos, a mensagem some com uma animação suave
    // --------------------------------------------------------
    var flashMessages = document.querySelectorAll('.flash');
    flashMessages.forEach(function (flash) {
        setTimeout(function () {
            flash.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            flash.style.opacity   = '0';
            flash.style.transform = 'translateY(-8px)';
            // Remove o elemento do DOM depois que a animação terminar
            setTimeout(function () {
                if (flash.parentElement) {
                    flash.parentElement.remove();
                }
            }, 600);
        }, 5000); // 5000 ms = 5 segundos
    });

    // --------------------------------------------------------
    // 4. FECHAR MENU MOBILE ao clicar em um link
    // Evita que o menu fique aberto depois da navegação
    // --------------------------------------------------------
    if (menuMobile) {
        var linksMenu = menuMobile.querySelectorAll('a');
        linksMenu.forEach(function (link) {
            link.addEventListener('click', function () {
                menuMobile.classList.remove('aberto');
                if (hamburguer) {
                    hamburguer.textContent = '☰';
                    hamburguer.setAttribute('aria-expanded', 'false');
                }
            });
        });
    }

    // --------------------------------------------------------
    // 5. PREVIEW DE IMAGEM no upload de foto
    // Quando o usuário escolhe uma foto, mostra uma prévia antes de enviar
    // --------------------------------------------------------
    var inputFoto = document.getElementById('foto');
    var previewFoto = document.getElementById('preview-foto');

    if (inputFoto && previewFoto) {
        inputFoto.addEventListener('change', function () {
            var arquivo = this.files[0];
            if (arquivo) {
                // FileReader lê o arquivo localmente (sem enviar ao servidor)
                var reader = new FileReader();
                reader.onload = function (e) {
                    previewFoto.src = e.target.result;
                    previewFoto.style.display = 'block';
                };
                reader.readAsDataURL(arquivo);
            }
        });
    }

    // --------------------------------------------------------
    // 6. CONFIRMAÇÃO antes de ações destrutivas
    // Para botões de excluir ou remover, pede confirmação
    // --------------------------------------------------------
    var botoesConfirmar = document.querySelectorAll('[data-confirmar]');
    botoesConfirmar.forEach(function (botao) {
        botao.addEventListener('click', function (e) {
            var mensagem = botao.getAttribute('data-confirmar') || 'Tem certeza?';
            if (!confirm(mensagem)) {
                e.preventDefault(); // cancela o clique se o usuário negar
            }
        });
    });

});