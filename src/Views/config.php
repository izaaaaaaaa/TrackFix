<?php include 'header.php'; ?>

<main class="layout">
    <div class="content">
        <h2 style="margin-bottom: 20px;">Configurações da Conta</h2>

        <form action="?rota=atualizar-config" method="POST" class="grid cols-3">
           
            <div class="card">
                <h3>Segurança</h3>
                <div class="field">
                    <label>Trocar e-mail</label>
                    <input type="email" name="novo_email" placeholder="novo@exemplo.com">
                </div>
                <div class="field">
                    <label>Trocar senha</label>
                    <input type="password" name="nova_senha" placeholder="Nova senha">
                </div>
                <div class="check-group" style="margin-top: 15px;">
                    <input type="checkbox" id="2fa" name="2fa">
                    <label for="2fa">Ativar 2FA</label>
                </div>
                <button type="submit" class="btn-principal" style="width: 100%; margin-top: 20px; background: #a2c2ff; color: #000;">Aplicar</button>
            </div>

            <div class="card">
                <h3>Notificações</h3>
                <div class="check-group">
                    <input type="checkbox" id="notif_manutencao" name="notif_manutencao" checked>
                    <label for="notif_manutencao">Manutenção</label>
                </div>
                <div class="check-group">
                    <input type="checkbox" id="notif_falha" name="notif_falha" checked>
                    <label for="notif_falha">Alertas de falha</label>
                </div>
                <div class="check-group">
                    <input type="checkbox" id="notif_emprestimos" name="notif_emprestimos">
                    <label for="notif_emprestimos">Empréstimos</label>
                </div>
            </div>

            <div class="card">
                <h3>Preferências</h3>
                <div class="check-group">
                    <input type="checkbox" id="pref_compacto" name="pref_compacto" checked>
                    <label for="pref_compacto">Layout compacto</label>
                </div>
                <div class="check-group">
                    <input type="checkbox" id="pref_sync" name="pref_sync" checked>
                    <label for="pref_sync">Sincronizar idioma do topo</label>
                </div>
            </div>

        </form>
    </div>
</main>

<?php include 'footer.php'; ?>0
