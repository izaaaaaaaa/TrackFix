<div id="cookie-banner" class="cookie-overlay" style="display: none;">
    <div class="cookie-content">
        <div class="cookie-text">
            <h4>Cookies & Privacidade</h4>
            <p>Usamos cookies para sessão e preferências. Gerencie abaixo.</p>
        </div>
        <div class="cookie-buttons">
            <button type="button" class="btn-preferencias">Preferências</button>
            <button type="button" id="aceitar-cookie" class="btn-aceitar">Aceitar</button>
        </div>
    </div>
</div>

<script>
    (function() {
        /** * LOGICA DE COOKIES
         */
        const NOME_CHAVE = 'trackfix_status_privacidade';
        const banner = document.getElementById('cookie-banner');
        const btnAceitar = document.getElementById('aceitar-cookie');

        if (!localStorage.getItem(NOME_CHAVE)) {
            banner.style.display = 'block';
        }

        btnAceitar.addEventListener('click', function() {
            banner.style.display = 'none';
            localStorage.setItem(NOME_CHAVE, 'true');
        });

        /** * LOGICA DA BARRA DE PESQUISA GLOBAL (TOPO)
         */
        const globalSearch = document.getElementById('globalSearch');
        if (globalSearch) {
            // Atalho "/" para focar na busca
            document.addEventListener('keydown', function(e) {
                if (e.key === '/' && document.activeElement !== globalSearch) {
                    e.preventDefault();
                    globalSearch.focus();
                }
            });

            // Enter na busca do topo
            globalSearch.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    const query = globalSearch.value.trim();
                    if (query.length > 0) {
                        // Redireciona para a rota search levando o texto no parâmetro 'q'
                        window.location.href = `?rota=search&q=${encodeURIComponent(query)}`;
                    }
                }
            });
        }
    })();
</script>

<script src="/trackfix/public/js/script_trackfix.js?v=<?php echo time(); ?>"></script>
</body>
</html>
