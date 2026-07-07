<?php include 'header.php'; ?>

<div class="content">
    <div class="ajuda-v2-container">
        <h2>Ajuda / Suporte</h2>

        <div class="ajuda-v2-grid">
            <div class="ajuda-v2-card">
                <h3>FAQ</h3>
                <div class="ajuda-v2-faq-item">
                    <details>
                        <summary>▶ Como faço login com 2FA?</summary>
                        <p>Ative no seu perfil para maior segurança.</p>
                    </details>
                </div>
                <div class="ajuda-v2-faq-item">
                    <details>
                        <summary>▶ Como registrar um empréstimo?</summary>
                        <p>Acesse o menu Gerenciador e selecione a ferramenta.</p>
                    </details>
                </div>
                 <div class="ajuda-v2-faq-item">
                    <details>
                        <summary>▶ Como vincular minha empresa?</summary>
                        <p>Acesse o menu cadastrar empresa e cadastre sua empresa.<br> Vá na tela de usuário e estará cadastrado em uma empresa.</p>
                    </details>
                </div>
                <div class="ajuda-v2-faq-item">
                    <details>
                        <summary>▶ Posso vincular mais de uma empresa, no meu cadastro?</summary>
                        <p>Não. Só é permitido uma empresa por cadastro.</p>
                    </details>
                </div>
            </div>

            <div class="ajuda-v2-card">
                <h3>Fale conosco</h3>
                <form action="https://api.web3forms.com/submit" method="POST" class="ajuda-v2-form">
                    
                    <input type="hidden" name="access_key" value="91257788-4348-4fa7-b140-7dd2cc37d5a8">
                    
                    <input type="hidden" name="redirect" value="<?php echo $_SERVER['REQUEST_URI']; ?>">

                    <div class="ajuda-v2-group">
                        <label>Nome</label>
                        <input type="text" name="nome" class="ajuda-v2-input" placeholder="Seu nome" required>
                    </div>
                    <div class="ajuda-v2-group">
                        <label>Mensagem</label>
                        <textarea name="mensagem" class="ajuda-v2-input" rows="4" placeholder="Escreva sua mensagem" required></textarea>
                    </div>
                    <button type="submit" class="ajuda-v2-btn">Enviar Mensagem</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
