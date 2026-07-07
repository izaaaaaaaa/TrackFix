<?php
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ?rota=login");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$mensagem = "";

// Lógica de Salvamento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_salvar'])) {
    try {
        $stmt_up = $pdo->prepare("UPDATE usuarios SET primeiro_nome = ?, sobrenome = ?, email = ? WHERE id = ?");
        $stmt_up->execute([trim($_POST['primeiro_nome']), trim($_POST['sobrenome']), trim($_POST['email']), $usuario_id]);
        $_SESSION['usuario_nome'] = $_POST['primeiro_nome'];
        $mensagem = "<div class='badge ok'>✓ Dados atualizados com sucesso!</div>";
    } catch (Exception $e) {
        $mensagem = "<div class='badge danger'>⚠ Erro ao salvar alterações.</div>";
    }
}

// BUSCA DADOS ATUALIZADOS + EMPRESA + ENDEREÇO (JOIN)
$sql = "SELECT u.*, e.razao_social, end.logradouro, end.numero, end.cidade, end.estado 
        FROM usuarios u
        LEFT JOIN empresas e ON u.empresa_id = e.id
        LEFT JOIN endereco end ON e.endereco_id = end.id
        WHERE u.id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$usuario_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

$historico = [];
try {
    $stmt_h = $pdo->query("SELECT item_do_inventario, data_manutencao FROM historico_manutencao ORDER BY id DESC LIMIT 4");
    $historico = $stmt_h->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $historico = []; }

$cargo_txt = (($user['tipo_id'] ?? 0) == 1) ? "Administrador" : "Usuário Padrao";

include 'header.php';
?>

<main class="layout">
    <div class="perfil-header">
        <h2 class="sobre-titulo" style="text-align: left; margin-bottom: 10px;">Meu Perfil</h2>
        <p class="muted">Gerencie suas informações e sua unidade vinculada.</p>
        <div style="margin-top: 15px;"><?= $mensagem ?></div>
    </div>

    <div class="perfil-grid-final">
        <section class="card-perfil-v3">
            <div class="sobre-icon">👤</div>
            <h3>Dados Pessoais</h3>
            <form method="POST" class="ajuda-v2-form">
                <div class="ajuda-v2-group">
                    <label>Primeiro Nome</label>
                    <input type="text" name="primeiro_nome" class="ajuda-v2-input" value="<?= htmlspecialchars($user['primeiro_nome'] ?? '') ?>">
                </div>
                <div class="ajuda-v2-group">
                    <label>Sobrenome</label>
                    <input type="text" name="sobrenome" class="ajuda-v2-input" value="<?= htmlspecialchars($user['sobrenome'] ?? '') ?>">
                </div>
                <div class="ajuda-v2-group">
                    <label>E-mail Corporativo</label>
                    <input type="email" name="email" class="ajuda-v2-input" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                </div>
                <button type="submit" name="btn_salvar" class="btn-principal" style="width: 100%; margin-top: 10px;">Atualizar Perfil</button>
            </form>
        </section>

        <section class="card-perfil-v3">
            <div class="sobre-icon">🏢</div>
            <h3>Minha Unidade</h3>
            <div class="historico-container" style="text-align: left;">
                <?php if (!empty($user['razao_social'])): ?>
                    <div class="item-hist" style="border-left: 3px solid #7da5fb; padding-left: 15px;">
                        <p style="color: #fff; font-weight: bold; font-size: 1.1rem; margin-bottom: 5px;">
                            <?= htmlspecialchars($user['razao_social']) ?>
                        </p>
                        <p class="muted" style="font-size: 0.9rem;">
                            📍 <?= htmlspecialchars($user['logradouro']) ?>, <?= htmlspecialchars($user['numero']) ?><br>
                            <?= htmlspecialchars($user['cidade']) ?> - <?= htmlspecialchars($user['estado']) ?>
                        </p>
                    </div>
                <?php else: ?>
                    <p class="muted">Você ainda não possui uma empresa vinculada.</p>
                    <a href="index.php?rota=empresas" class="btn-glow" style="display:inline-block; margin-top: 10px; padding: 10px 20px; text-decoration: none; font-size: 0.8rem;">Vincular Agora</a>
                <?php endif; ?>
            </div>
        </section>

        <section class="card-perfil-v3 centro">
            <div class="sobre-icon">🛡️</div>
            <h3>Nível de Acesso</h3>
            <div class="badge-cargo" style="background: var(--primary); margin: 20px 0;"><?= $cargo_txt ?></div>
            <p class="muted" style="font-size: 12px;">ID do Usuário: #<?= $user['id'] ?? '0' ?></p>
            <hr style="border: 0; border-top: 1px solid rgba(255,255,255,0.05); margin: 20px 0; width: 100%;">
            <p class="muted">Conta sincronizada com TrackFix Cloud.</p>
        </section>
    </div>

    <div style="margin-top: 40px; border: 1px solid #ff4d4d; padding: 25px; border-radius: 8px; background: rgba(255, 77, 77, 0.03); text-align: left;">
        <h3 style="color: #ff4d4d; margin-top: 0; display: flex; align-items: center; gap: 10px;">
            ⚠️ Zona de Perigo Irreversível
        </h3>
        <p class="muted" style="font-size: 0.9rem; margin-bottom: 20px; line-height: 1.5;">
            Ao clicar no botão abaixo, a sua conta e todo o histórico de notificações e vínculos ativos serão permanentemente apagados do ecossistema <strong>TrackFix</strong>. Esta operação limpa seus registros de forma direta no banco de dados e não pode ser desfeita.
        </p>
        
        <button type="button" onclick="abrirModalConfirmacao()" style="background-color: #ff4d4d; color: white; border: none; padding: 12px 24px; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 0.9rem; transition: filter 0.2s;" onmouseover="this.style.filter='brightness(0.9)'" onmouseout="this.style.filter='brightness(1)'">
            Excluir Minha Conta Permanentemente
        </button>
    </div>
</main>

<div id="modalExcluir" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.8); backdrop-filter: blur(5px); align-items: center; justify-content: center;">
    <div style="background: #18191a; border: 1px solid rgba(255, 77, 77, 0.3); padding: 35px; border-radius: 12px; max-width: 460px; width: 90%; text-align: center; box-shadow: 0 15px 40px rgba(0,0,0,0.6); transition: all 0.3s ease;">
        <div id="modalIcone" style="font-size: 3rem; margin-bottom: 15px; color: #ff4d4d;">👤</div>
        <h3 id="modalTitulo" style="color: #fff; margin-bottom: 12px; font-size: 1.4rem; font-family: inherit;">Confirmar Exclusão</h3>
        <p id="modalTexto" style="color: #b0b3b8; font-size: 0.95rem; line-height: 1.6; margin-bottom: 30px;">
            Tem certeza de que deseja EXCLUIR totalmente sua conta? Todos os seus dados associados serão deletados.
        </p>
        
        <form id="formExcluirReal" action="?rota=excluir-conta" method="POST" style="display:none;"></form>

        <div style="display: flex; gap: 14px; justify-content: center;">
            <button type="button" onclick="fecharModalConfirmacao()" style="background: #3a3b3c; color: #e4e6eb; border: none; padding: 12px 22px; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 0.9rem; transition: background 0.2s;" onmouseover="this.style.background='#4e4f50'" onmouseout="this.style.background='#3a3b3c'">
                Cancelar
            </button>
            <button type="button" id="btnModalAvancar" onclick="processarPassoModal()" style="background: #ff4d4d; color: #fff; border: none; padding: 12px 22px; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 0.9rem; transition: filter 0.2s;" onmouseover="this.style.filter='brightness(0.9)'" onmouseout="this.style.filter='brightness(1)'">
                Sim, Continuar
            </button>
        </div>
    </div>
</div>

<script>
let passoConfirmacao = 1;

function abrirModalConfirmacao() {
    passoConfirmacao = 1;
    document.getElementById('modalIcone').innerText = "👤";
    document.getElementById('modalIcone').style.color = "#ff4d4d";
    document.getElementById('modalTitulo').innerText = "Confirmar Exclusão";
    document.getElementById('modalTexto').innerHTML = "Tem certeza de que deseja <strong>EXCLUIR</strong> totalmente sua conta? Todos os dados vinculados ao seu perfil serão permanentemente apagados.";
    document.getElementById('btnModalAvancar').style.background = "#ff4d4d";
    document.getElementById('btnModalAvancar').innerText = "Sim, Continuar";
    document.getElementById('modalExcluir').style.display = 'flex';
}

function fecharModalConfirmacao() {
    document.getElementById('modalExcluir').style.display = 'none';
}

function processarPassoModal() {
    if (passoConfirmacao === 1) {
        passoConfirmacao = 2;
        // Transição visual sutil para o aviso crítico definitivo
        document.getElementById('modalIcone').innerText = "⚠️";
        document.getElementById('modalTitulo').innerText = "Aviso Crítico Final";
        document.getElementById('modalTexto').innerHTML = "Esta ação é <strong>estritamente irreversível</strong> e removerá seu usuário direto da base de dados do TrackFix. Deseja mesmo prosseguir?";
        document.getElementById('btnModalAvancar').innerText = "Excluir Permanentemente";
    } else if (passoConfirmacao === 2) {
        // Envia o formulário oculto de forma nativa e segura
        document.getElementById('formExcluirReal').submit();
    }
}

// Fecha o modal caso o usuário clique fora da caixa interna
window.onclick = function(event) {
    const modal = document.getElementById('modalExcluir');
    if (event.target === modal) {
        fecharModalConfirmacao();
    }
}
</script>

<?php include 'footer.php'; ?>
