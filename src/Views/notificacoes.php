<?php 
/**
 * ARQUIVO: src/Views/notificacoes.php
 * Central de Gerenciamento de Alertas TrackFix (Painel Unificado - Sem emojis quebrando)
 */
    
$baseDir = dirname(dirname(__DIR__));
include $baseDir . '/src/Views/header.php'; 

try {
    // Busca absolutamente todas as notificações do usuário logado por ordem de data
    $stmt = $pdo->prepare("SELECT * FROM notificacoes WHERE usuario_id = ? OR usuario_id = 0 ORDER BY data_criacao DESC");
    $stmt->execute([$_SESSION['usuario_id']]);
    $notificacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $notificacoes = [];
}
?>

<style>
    .notif-card {
        background: #1a1d29;
        border: 1px solid #2d3142;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 25px;
        width: 100%;
    }
    .notif-header-box {
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        margin-bottom: 25px;
        flex-wrap: wrap;
        gap: 15px;
    }
    .filter-btn {
        background: transparent;
        border: 1px solid #2d3142;
        color: #8a8f9d;
        padding: 8px 16px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: bold;
        text-decoration: none;
        font-size: 0.85rem;
    }
    .notif-item {
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        padding: 16px 20px; 
        border-bottom: 1px solid rgba(255,255,255,0.05); 
        background: rgba(0,0,0,0.2); 
        border-radius: 10px; 
        margin-bottom: 12px;
        gap: 20px;
    }
    .icon-box {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }
    /* Cores dinâmicas para os ícones */
    .type-ferramenta { background: rgba(52, 211, 153, 0.1); color: #34d399; }
    .type-manutencao { background: rgba(125, 165, 251, 0.1); color: #7da5fb; }
    .type-excluido { background: rgba(255, 77, 77, 0.1); color: #ff4d4d; }
    .type-padrao { background: rgba(0, 188, 212, 0.1); color: #00bcd4; }

    .btn-action {
        text-decoration: none; 
        font-size: 11px; 
        font-weight: bold; 
        padding: 6px 12px; 
        border-radius: 6px;
        white-space: nowrap;
    }
</style>

<main class="layout" style="margin-top: 60px;">
    <div class="content" style="padding: 20px; width: 100%;">
        
        <div class="notif-header-box">
            <h2 style="color: var(--primary, #00bcd4); margin: 0; font-weight: bold;">🔔 Central de Notificações</h2>
            <div>
                <?php if (!empty($notificacoes)): ?>
                    <a href="?rota=notificacoes-acao&acao=ler_todas" class="filter-btn" style="border-color: #34d399; color: #34d399;">✓ Marcar todas como lidas</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="notif-card">
            <?php if (empty($notificacoes)): ?>
                <div style="text-align: center; padding: 40px 20px;">
                    <span style="font-size: 35px; display: block; margin-bottom: 15px; opacity: 0.4;">📨</span>
                    <h4 style="margin: 0; color: #fff;">Nenhum alerta por aqui</h4>
                    <p style="margin: 6px 0 0 0; color: #888; font-size: 0.85rem;">Sua caixa de entrada está completamente limpa.</p>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column;">
                    <?php foreach ($notificacoes as $notif): 
                        // Lógica limpa: analisa o texto para decidir qual tag FontAwesome renderizar
                        $iconHtml = '<i class="fas fa-bell"></i>';
                        $classType = 'type-padrao';
                        
                        if (stripos($notif['mensagem'], 'cadastrada') !== false || stripos($notif['mensagem'], 'Editada') !== false) {
                            $iconHtml = '<i class="fas fa-wrench"></i>';
                            $classType = 'type-ferramenta';
                        } elseif (stripos($notif['mensagem'], 'manutenção') !== false) {
                            $iconHtml = '<i class="fas fa-tools"></i>';
                            $classType = 'type-manutencao';
                        } elseif (stripos($notif['mensagem'], 'removida') !== false || stripos($notif['mensagem'], 'cancelada') !== false) {
                            $iconHtml = '<i class="fas fa-trash-alt"></i>';
                            $classType = 'type-excluido';
                        }

                        // Remove eventuais pontos de interrogação gerados antigamente pelo banco no início da string
                        $textoLimpo = ltrim($notif['mensagem'], '? ');
                    ?>
                        <div class="notif-item" style="border-left: 4px solid <?= $notif['lida'] ? 'transparent' : 'var(--primary, #00bcd4)' ?>; opacity: <?= $notif['lida'] ? '0.6' : '1' ?>;">
                            
                            <div style="display: flex; gap: 15px; align-items: center; flex: 1;">
                                <div class="icon-box <?= $classType ?>"><?= $iconHtml ?></div>
                                <div>
                                    <p style="margin: 0; font-size: 0.95rem; line-height: 1.4; color: #fff;">
                                        <?= htmlspecialchars($textoLimpo) ?>
                                    </p>
                                    <div style="color: #626775; font-size: 0.75rem; margin-top: 4px;">
                                        📅 <?= date('d/m/Y \à\s H:i', strtotime($notif['data_criacao'])) ?>
                                    </div>
                                </div>
                            </div>

                            <div style="display: flex; gap: 8px; align-items: center;">
                                <?php if (!$notif['lida']): ?>
                                    <a href="?rota=notificacoes-acao&acao=ler&id=<?= $notif['id'] ?>" class="btn-action" style="color: #000; background: var(--primary, #00bcd4);">Marcar lida</a>
                                <?php endif; ?>
                                <a href="?rota=notificacoes-acao&acao=excluir&id=<?= $notif['id'] ?>" class="btn-action" style="color: #ff4d4d; background: rgba(255, 77, 77, 0.05); border: 1px solid #ff4d4d;" onclick="return confirm('Deseja excluir esta notificação?')">Excluir</a>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</main>

<?php include $baseDir . '/src/Views/footer.php'; ?>
