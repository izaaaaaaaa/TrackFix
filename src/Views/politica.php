<?php include 'header.php'; ?>

<style>
    /* Estilo do Modal (Painel de Cookies) */
    .modal-cookies {
        display: none; /* Escondido por padrão */
        position: fixed;
        z-index: 10000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.8);
        backdrop-filter: blur(5px);
    }

    .modal-content {
        background-color: #1a1d29;
        margin: 10% auto;
        padding: 30px;
        border: 1px solid #2d3142;
        width: 90%;
        max-width: 500px;
        border-radius: 20px;
        color: white;
        box-shadow: 0 10px 40px rgba(0,0,0,0.5);
        animation: fadeIn 0.3s ease-out;
    }

    .cookie-option {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 0;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }

    /* Switch Estilizado */
    .switch {
        position: relative;
        display: inline-block;
        width: 45px;
        height: 24px;
    }

    .switch input { opacity: 0; width: 0; height: 0; }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #334155;
        transition: .4s;
        border-radius: 34px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 18px; width: 18px;
        left: 3px; bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked + .slider { background-color: #7da5fb; }
    input:checked + .slider:before { transform: translateX(21px); }
</style>

<main class="layout">
    <div class="sobre-section">
        <h2 class="sobre-titulo">Política de Privacidade</h2>
       
        <div class="sobre-grid" style="display: flex; justify-content: center;">
            <div class="sobre-card" style="max-width: 800px; width: 100%; text-align: left; background: rgba(255,255,255,0.03); padding: 40px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.1);">
                <div style="font-size: 3rem; margin-bottom: 20px;">🛡️</div>
                <h3 style="color: #7da5fb; margin-bottom: 20px;">SEGURANÇA E TRANSPARÊNCIA</h3>
                <p style="line-height: 1.8; color: #8a8f9d;">
                    • Coletamos dados pessoais (nome, e-mail, cargo) para autenticação e auditoria.<br>
                    • Usamos cookies para a sessão e preferências (tema, idioma, acessibilidade).<br>
                    • Você pode gerenciar cookies no painel e solicitar remoção de dados.
                </p>
                <br>
                <button type="button" id="btnAbrirCookies" class="btn-glow" style="border: none; cursor: pointer;">
                    Abrir painel de cookies
                </button>
            </div>
        </div>
    </div>
</main>

<div id="meuModalCookies" class="modal-cookies">
    <div class="modal-content">
        <h3 style="color: #7da5fb;">Preferências de Cookies</h3>
        <p class="muted" style="font-size: 0.9rem; margin-bottom: 20px;">Escolha quais tecnologias você permite que utilizemos.</p>

        <div class="cookie-option">
            <div>
                <strong>Necessários</strong><br>
                <small class="muted">Essenciais para o login funcionar.</small>
            </div>
            <label class="switch">
                <input type="checkbox" checked disabled> <span class="slider"></span>
            </label>
        </div>

        <div class="cookie-option">
            <div>
                <strong>Preferências</strong><br>
                <small class="muted">Salva seu tema (Escuro/Claro).</small>
            </div>
            <label class="switch">
                <input type="checkbox" id="cookieTema" checked>
                <span class="slider"></span>
            </label>
        </div>

        <div class="cookie-option">
            <div>
                <strong>Marketing</strong><br>
                <small class="muted">Não utilizamos, mas você pode bloquear.</small>
            </div>
            <label class="switch">
                <input type="checkbox">
                <span class="slider"></span>
            </label>
        </div>

        <button id="btnSalvarCookies" class="btn-glow" style="width: 100%; margin-top: 25px; border: none; cursor: pointer;">
            Salvar e Aplicar
        </button>
    </div>
</div>

<script>
    const modal = document.getElementById("meuModalCookies");
    const btnAbrir = document.getElementById("btnAbrirCookies");
    const btnSalvar = document.getElementById("btnSalvarCookies");

    // Abrir o modal
    btnAbrir.onclick = function() {
        modal.style.display = "block";
    }

    // Fechar ao clicar em salvar
    btnSalvar.onclick = function() {
        modal.style.display = "none";
        alert("Preferências salvas com sucesso!");
    }

    // Fechar se clicar fora da janelinha branca
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>

<?php include 'footer.php'; ?>
