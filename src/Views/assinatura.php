<?php 
include $baseDir . '/src/Views/header.php'; 

// Captura o plano atual salvo na sessão (Padrão: 'bronze')
$planoSessao = $_SESSION['usuario_plano'] ?? 'bronze';

// TRADUÇÃO CRÍTICA: Converte os termos do ENUM do Banco de Dados para os nomes dos Cards HTML
if ($planoSessao === 'basico') {
    $planoAtual = 'bronze';
} elseif ($planoSessao === 'premium') {
    $planoAtual = 'prata';
} elseif ($planoSessao === 'enterprise') {
    $planoAtual = 'ouro';
} else {
    // Caso a sessão já possua o nome amigável guardado
    $planoAtual = $planoSessao; 
}
?>

<main class="layout">
    <div class="content" style="padding: 40px 20px;">
        
        <div style="text-align: center; margin-bottom: 50px;">
            <h2 style="font-size: 2.5rem; color: var(--text); margin-bottom: 10px;">🚀 Escolha o plano ideal para você ✨</h2>
            <p style="color: var(--muted); font-size: 1.1rem;">💎 Desbloqueie recursos incríveis e turbine o gerenciamento das suas manutenções.</p>
        </div>

        <div class="grid cols-3" style="align-items: stretch; max-width: 1200px; margin: 0 auto; gap: 25px;">
            
            <div class="card card-plano <?php echo ($planoAtual === 'bronze') ? 'plano-ativo' : ''; ?>">
                <span class="badge" style="background: rgba(255,255,255,0.05); color: var(--muted);">🥉 BÁSICO</span>
                <h3>Bronze</h3>
                <div class="preco">R$ 0<span>/mês</span></div>
                <p style="color: var(--muted); font-size: 0.9rem; margin-bottom: 20px;">Ideal para quem está começando a organizar os reparos.</p>
                <hr class="divisor" style="width: 100%; opacity: 0.1; margin-bottom: 20px;">
                <ul class="lista-recursos">
                    <li>Até 50 registros por mês</li>
                    <li>Suporte básico por e-mail</li>
                    <li>Relatórios simples em tela</li>
                </ul>
                
                <?php if ($planoAtual === 'bronze'): ?>
                    <button class="btn btn-ghost w-100" style="margin-top: auto;" disabled>✓ Plano Atual</button>
                <?php else: ?>
                    <a href="?rota=atualizar-plano&plano=bronze" class="btn btn-ghost w-100" style="margin-top: auto; text-align: center; text-decoration: none; display: block; box-sizing: border-box;">Mudar para Bronze</a>
                <?php endif; ?>
            </div>

            <div class="card card-plano destaque <?php echo ($planoAtual === 'prata') ? 'plano-ativo' : ''; ?>">
                <span class="badge ok">🌟 MAIS POPULAR</span>
                <h3>Prata</h3>
                <div class="preco">R$ 49,90<span>/mês</span></div>
                <p style="color: var(--muted); font-size: 0.9rem; margin-bottom: 20px;">Perfeito para profissionais autônomos que precisam de agilidade.</p>
                <hr class="divisor" style="width: 100%; opacity: 0.1; margin-bottom: 20px;">
                <ul class="lista-recursos">
                    <li>Registros ilimitados</li>
                    <li>Suporte prioritário 24/7</li>
                    <li>Relatórios avançados (PDF/Excel)</li>
                    <li>Acesso completo ao histórico</li>
                </ul>
                
                <?php if ($planoAtual === 'prata'): ?>
                    <button class="btn w-100" style="margin-top: auto; background: rgba(46, 204, 113, 0.2); color: #2ecc71; border: 1px solid #2ecc71;" disabled>✓ Plano Atual</button>
                <?php else: ?>
                    <form action="?rota=checkout&plano=prata" method="POST" style="width: 100%; margin-top: auto;">
                        <input type="hidden" name="plano" value="prata">
                        <button type="submit" class="btn w-100">💳 Assinar Agora</button>
                    </form>
                <?php endif; ?>
            </div>

            <div class="card card-plano <?php echo ($planoAtual === 'ouro') ? 'plano-ativo' : ''; ?>">
                <span class="badge" style="background: rgba(255,213,102,0.1); color: var(--warning);">👑 CORPORATIVO</span>
                <h3>Ouro</h3>
                <div class="preco">R$ 99,90<span>/mês</span></div>
                <p style="color: var(--muted); font-size: 0.9rem; margin-bottom: 20px;">Para grandes equipes e empresas de manutenção robustas.</p>
                <hr class="divisor" style="width: 100%; opacity: 0.1; margin-bottom: 20px;">
                <ul class="lista-recursos">
                    <li>Tudo do plano Prata</li>
                    <li>Múltiplos usuários integrados</li>
                    <li>Gerente de conta exclusivo</li>
                    <li>Backup automatizado diário</li>
                </ul>
                
                <?php if ($planoAtual === 'ouro'): ?>
                    <button class="btn w-100" style="margin-top: auto; background: rgba(46, 204, 113, 0.2); color: #2ecc71; border: 1px solid #2ecc71;" disabled>✓ Plano Atual</button>
                <?php else: ?>
                    <form action="?rota=checkout&plano=ouro" method="POST" style="width: 100%; margin-top: auto;">
                        <input type="hidden" name="plano" value="ouro">
                        <button type="submit" class="btn btn-ghost w-100">🚀 Quero Este</button>
                    </form>
                <?php endif; ?>
            </div>

        </div>
    </div>
</main>

<style>
.card-plano { display: flex; flex-direction: column; align-items: flex-start; padding: 35px 25px !important; position: relative; transition: transform var(--trans), border-color var(--trans) !important; }
.card-plano h3 { font-size: 1.8rem; margin: 15px 0 5px 0 !important; color: var(--text); }
.card-plano .preco { font-size: 2.3rem; font-weight: 800; color: var(--text); margin-bottom: 15px; }
.card-plano .preco span { font-size: 1rem; color: var(--muted); font-weight: 400; }
.lista-recursos { list-style: none; padding: 0; margin: 0 0 30px 0; width: 100%; }
.lista-recursos li { font-size: 0.95rem; color: var(--text); margin-bottom: 12px; padding-left: 25px; position: relative; opacity: 0.9; }
.lista-recursos li::before { content: "🔹"; position: absolute; left: 0; font-size: 0.8rem; top: 2px; }
.w-100 { width: 100%; box-sizing: border-box; }
.card-plano.destaque { border: 2px solid var(--primary) !important; background: linear-gradient(180deg, rgba(122, 162, 255, 0.08), rgba(255, 255, 255, 0.01)) !important; transform: scale(1.03); }

.card-plano.plano-ativo { border: 2px solid #2ecc71 !important; box-shadow: 0 0 15px rgba(46, 204, 113, 0.15); }

.card-plano:hover { transform: translateY(-5px); border-color: var(--primary-2); }
.card-plano.destaque:hover { transform: scale(1.03) translateY(-5px); }
@media (max-width: 980px) { .card-plano.destaque { transform: none; } .card-plano.destaque:hover { transform: translateY(-5px); } }
</style>

<?php 
include $baseDir . '/src/Views/footer.php'; 
?>
