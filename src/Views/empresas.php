<?php include 'header.php'; ?>

<main class="layout">
    <div class="content empresa-section" style="padding: 20px; animation: fadeIn 0.8s ease-out;">
        <h2 style="color: #7da5fb; margin-bottom: 25px; font-weight: bold;">🏢 Cadastrar Nova Unidade</h2>

        <div class="card" style="background: #1a1d29; border: 1px solid #2d3142; border-radius: 15px; padding: 40px;">
            <form action="index.php?rota=processar-empresa&acao=cadastrar_completo" method="POST">
                
                <h3 style="color: #fff; margin-bottom: 20px; border-bottom: 1px solid #2d3142; padding-bottom: 10px; font-size: 1.1rem;">Dados da Empresa</h3>
                
                <div style="margin-bottom: 30px;">
                    <label class="muted" style="display: block; margin-bottom: 8px;">Razão Social / Nome da Unidade</label>
                    <input type="text" name="razao_social" class="input-dark" 
                           style="width: 100%; padding: 12px; background: #0f111a; border: 1px solid #2d3142; color: #fff; border-radius: 8px;" 
                           required placeholder="Ex: Oficina Central Matriz">
                </div>

                <h3 style="color: #fff; margin-bottom: 20px; border-bottom: 1px solid #2d3142; padding-bottom: 10px;">Localização</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 2fr 1fr; gap: 30px; margin-bottom: 25px;">
                    <div>
                        <label class="muted" style="display: block; margin-bottom: 8px;">CEP</label>
                        <input type="text" name="cep" id="cep" class="input-dark" maxlength="9" 
                               style="width: 100%; padding: 12px; background: #0f111a; border: 1px solid #2d3142; color: #fff; border-radius: 8px;" 
                               required placeholder="00000-000">
                    </div>
                    <div>
                        <label class="muted" style="display: block; margin-bottom: 8px;">Logradouro (Rua/Av)</label>
                        <input type="text" name="logradouro" id="logradouro" class="input-dark" 
                               style="width: 100%; padding: 12px; background: #0f111a; border: 1px solid #2d3142; color: #fff; border-radius: 8px;" 
                               required>
                    </div>
                    <div>
                        <label class="muted" style="display: block; margin-bottom: 8px;">Número</label>
                        <input type="text" name="numero" class="input-dark" 
                               style="width: 100%; padding: 12px; background: #0f111a; border: 1px solid #2d3142; color: #fff; border-radius: 8px;" 
                               required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 30px; margin-top: 10px; margin-bottom: 30px;">
                    <div>
                        <label class="muted" style="display: block; margin-bottom: 8px;">Bairro</label>
                        <input type="text" name="bairro" id="bairro" class="input-dark" 
                               style="width: 100%; padding: 12px; background: #0f111a; border: 1px solid #2d3142; color: #fff; border-radius: 8px;" required>
                    </div>
                    <div>
                        <label class="muted" style="display: block; margin-bottom: 8px;">Cidade</label>
                        <input type="text" name="cidade" id="cidade" class="input-dark" 
                               style="width: 100%; padding: 12px; background: #0f111a; border: 1px solid #2d3142; color: #fff; border-radius: 8px;" required>
                    </div>
                    <div>
                        <label class="muted" style="display: block; margin-bottom: 8px;">Estado (UF)</label>
                        <input type="text" name="estado" id="uf" maxlength="2" class="input-dark" 
                               style="width: 100%; padding: 12px; background: #0f111a; border: 1px solid #2d3142; color: #fff; border-radius: 8px; text-transform: uppercase;" required>
                    </div>
                </div>

                <button type="submit" class="btn-glow" 
                        style="width: 100%; padding: 15px; border: none; font-weight: bold; cursor: pointer; border-radius: 10px;">
                    Finalizar Cadastro e Vincular minha Conta
                </button>
            </form>
        </div>
    </div>
</main>

<script>
document.getElementById('cep').addEventListener('blur', function() {
    // Remove caracteres não numéricos
    let cep = this.value.replace(/\D/g, '');

    // Função para limpar campos
    const limpaForm = () => {
        document.getElementById('logradouro').value = "";
        document.getElementById('bairro').value = "";
        document.getElementById('cidade').value = "";
        document.getElementById('uf').value = "";
    };

    if (cep !== "") {
        // Expressão regular para validar o CEP
        let validacep = /^[0-9]{8}$/;

        if(validacep.test(cep)) {
            // Preenche com "..." enquanto consulta
            document.getElementById('logradouro').value = "...";
            document.getElementById('bairro').value = "...";
            document.getElementById('cidade').value = "...";
            document.getElementById('uf').value = "...";

            // Consulta a API ViaCEP
            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(res => res.json())
                .then(dados => {
                    if (!("erro" in dados)) {
                        // Atualiza os campos com os valores da consulta
                        document.getElementById('logradouro').value = dados.logradouro;
                        document.getElementById('bairro').value = dados.bairro;
                        document.getElementById('cidade').value = dados.localidade;
                        document.getElementById('uf').value = dados.uf;
                        
                        // Foca automaticamente no campo Número para agilizar
                        document.getElementsByName('numero')[0].focus();
                    } else {
                        limpaForm();
                        alert("CEP não encontrado. Digite manualmente.");
                    }
                })
                .catch(() => {
                    limpaForm();
                    alert("Erro ao buscar o CEP. Verifique sua conexão.");
                });
        } else {
            limpaForm();
            alert("Formato de CEP inválido.");
        }
    } else {
        limpaForm();
    }
});
</script>

<?php include 'footer.php'; ?>
