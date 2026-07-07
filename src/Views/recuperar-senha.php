<?php include 'header.php'; ?>

<main class="layout" style="display: flex; align-items: center; justify-content: center; height: 80vh;">
    <div class="card content" style="max-width: 400px; width: 100%;">
        <div style="text-align: center; margin-bottom: 20px;">
            <h2 style="margin-bottom: 10px;">Recuperar Senha</h2>
            <p class="muted">Insira seu e-mail para receber as instruções de redefinição.</p>
        </div>

        <form method="POST" action="index.php?rota=enviar-recuperacao">
            <div class="field">
                <label>E-mail cadastrado</label>
                <input type="email" name="email" placeholder="exemplo@empresa.com" required>
            </div>
           
            <button type="submit" class="btn-ghost" style="width: 100%; background: #a5b4fc; color: #1e1e2f; font-weight: bold; margin-top: 15px;">
                Enviar Link de Recuperação
            </button>
        </form>

        <div style="text-align: center; margin-top: 20px;">
            <a href="index.php?rota=login" class="muted" style="text-decoration: none; font-size: 0.9rem;">
                ← Voltar para o Login
            </a>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>
