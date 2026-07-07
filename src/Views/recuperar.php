<?php include 'header.php'; ?>

<main class="layout">
    <div class="login-cadastro-container">
        <div class="card-auth">
            <h3>Recuperar Senha</h3>
            
            <?php if (!isset($_SESSION['recuperar_passo'])): ?>
                <p>Digite seu e-mail cadastrado para gerar um código de redefinição.</p>
                <form action="?rota=processar-recuperar&acao=gerar-codigo" method="POST">
                    <div class="campo">
                        <label>E-mail</label>
                        <input type="email" name="email" placeholder="voce@exemplo.com" required>
                    </div>
                    <button type="submit" class="btn-principal">Gerar Código</button>
                </form>
                <div style="margin-top: 15px; text-align: center;">
                    <a href="?rota=login" class="link-secundario">Voltar ao Login</a>
                </div>

            <?php elseif ($_SESSION['recuperar_passo'] === 2): ?>
                <div style="background: rgba(40, 167, 69, 0.1); border: 1px solid #28a745; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                    <p style="color: #28a745; font-weight: bold; margin-bottom: 5px;">E-mail Verificado com Sucesso!</p>
                    <p style="margin: 0; font-size: 0.9rem;">Copie o código de segurança gerado abaixo:</p>
                    <h2 style="letter-spacing: 2px; color: #fff; margin: 10px 0; background: #222; padding: 10px; border-radius: 4px; display: inline-block;">
                        <?php echo $_SESSION['recuperar_codigo']; ?>
                    </h2>
                </div>

                <form action="?rota=processar-recuperar&acao=alterar-senha" method="POST">
                    <div class="campo">
                        <label>Confirme o Código de Segurança</label>
                        <input type="text" name="codigo_confirmacao" placeholder="Cole o código aqui" required autocomplete="off">
                    </div>
                    
                    <div class="campo">
                        <label>Nova Senha</label>
                        <input type="password" name="nova_senha" placeholder="No mínimo 6 caracteres" required>
                    </div>
                    
                    <button type="submit" class="btn-principal">Atualizar Senha</button>
                </form>
            <?php endif; ?>

        </div>
    </div>
</main>

<?php include 'footer.php'; ?>
