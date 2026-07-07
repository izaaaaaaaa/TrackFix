<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Trackfix • Oficina</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style_trackfix.css?v=<?php echo time(); ?>">
</head>
<body class="<?php echo (isset($_COOKIE['sidebarStatus']) && $_COOKIE['sidebarStatus'] === 'active') ? 'menu-open-body' : ''; ?>">
   
    <header class="nav">
        <div class="brand">
            <button id="toggleMenu" class="btn-ghost" style="font-size:20px">☰</button>
            <div class="logo"></div>
            <div>
                <div style="font-size:16px">Oficina • <span class="badge ok">Gestão</span></div>
                <small class="muted">TrackFix</small>
            </div>
        </div>
       
        <form action="index.php" method="GET" class="searchbar">
            <input type="hidden" name="rota" value="search">
            <span class="icon">🔎</span>
            <input name="q" id="globalSearch" type="search" placeholder="Buscar (Atalho: / )" value="<?= $_GET['q'] ?? '' ?>" />
        </form>

        <div class="actions">
            <button id="toggleTheme" class="btn-ghost" title="Alternar Tema">🌓</button>
            <button id="toggleContrast" class="btn-ghost" title="Alto Contraste">◐</button>
            
            <div style="position: relative; display: inline-block;">
                <button id="notifyBtn" class="btn-ghost" style="position: relative;">
                    🔔
                    <span id="notifyBadge" style="position: absolute; top: -2px; right: -2px; background: var(--danger, #ff4d4d); color: white; font-size: 10px; width: 16px; height: 16px; border-radius: 50%; display: none; align-items: center; justify-content: center; font-weight: bold;">0</span>
                </button>

                <div id="notifyDropdown" style="position: absolute; right: 0; top: 45px; width: 280px; background: var(--surface, #222); border: 1px solid rgba(255,255,255,.1); border-radius: 12px; box-shadow: var(--shadow, 0 4px 12px rgba(0,0,0,0.5)); display: none; z-index: 1000; padding: 10px;">
                    <div style="font-size: 12px; font-weight: bold; padding-bottom: 8px; border-bottom: 1px solid rgba(255,255,255,.05); margin-bottom: 8px; color: var(--primary, #00bcd4);">Notificações</div>
                    <ul id="notifyList" style="list-style: none; padding: 0; margin: 0; max-height: 200px; overflow-y: auto; font-size: 13px; color: var(--text, #fff);">
                        <li style="padding: 8px; text-align: center; color: var(--muted, #888);">Nenhuma notificação nova</li>
                    </ul>
                </div>
            </div>
           
            <?php if(isset($_SESSION['usuario_nome'])): ?>
                <span style="margin-right: 10px; font-size: 14px;">Olá, <?= explode(' ', $_SESSION['usuario_nome'])[0] ?></span>
                <a href="?rota=logout" class="btn-ghost">Sair</a>
            <?php else: ?>
                <a href="?rota=login" class="btn">Entrar</a>
            <?php endif; ?>
        </div>
    </header>

    <div class="layout">
    <aside class="sidebar">
        <?php
            // Função rápida para marcar o link ativo
            $current = $_GET['rota'] ?? 'home';
            function isActive($route, $current) { echo ($route === $current) ? 'active' : ''; }
        ?>

        <a href="?rota=empresas" class="navlink <?php isActive('empresas', $current); ?>">🏢  <span>Cadastrar empresa</span></a>
        
        <?php if (!isset($_SESSION['usuario_nome'])): ?>
            <a href="?rota=login" class="navlink <?php isActive('login', $current); ?>">🔒 <span>Login & Cadastro</span></a>
        <?php endif; ?>

        <a href="?rota=sobre" class="navlink <?php isActive('sobre', $current); ?>">🏷️ <span>Sobre Nós</span></a>
        <a href="?rota=politica" class="navlink <?php isActive('politica', $current); ?>">🛡️ <span>Política de Privacidade</span></a>
        <a href="?rota=ajuda" class="navlink <?php isActive('ajuda', $current); ?>">🆘 <span>Ajuda / Suporte</span></a>
        <a href="?rota=assinatura" class="navlink <?php isActive('assinatura', $current); ?>">💳 <span>Planos de Assinatura</span></a>
        
        <hr style="opacity: 0.1; margin: 10px 0;">
       
        <a href="?rota=search" class="navlink <?php isActive('search', $current); ?>">🧰 <span>Pesquisa</span></a>
        <a href="?rota=rastreio" class="navlink <?php isActive('rastreio', $current); ?>">🗺️ <span>Rastrear Item</span></a>
        <a href="?rota=manutencao" class="navlink <?php isActive('manutencao', $current); ?>">📅 <span>Agenda de Manutenção</span></a>
        <a href="?rota=tool-manager" class="navlink <?php isActive('tool-manager', $current); ?>">🛠️ <span>Gerenciar Ferramentas</span></a>
       
        <hr style="opacity: 0.1; margin: 10px 0;">

        <a href="?rota=notificacoes" class="navlink <?php isActive('notificacoes', $current); ?>">📅 <span>Minhas Notificações</span></a>
        <?php if (isset($_SESSION['usuario_plano']) && ($_SESSION['usuario_plano'] === 'prata' || $_SESSION['usuario_plano'] === 'ouro')): ?>
    <a href="?rota=dashboard_premium" class="navlink <?php isActive('dashboard_premium', $current); ?>">📊 <span>Indicadores Pro</span></a>
<?php endif; ?>
        <a href="?rota=profile" class="navlink <?php isActive('profile', $current); ?>">👤 <span>Perfil do Usuário</span></a>
        <a href="?rota=config" class="navlink <?php isActive('config', $current); ?>">⚙️ <span>Configurações</span></a>
        <a href="?rota=desenvolvedores" class="navlink <?php isActive('desenvolvedores', $current); ?>">🧑‍💻 <span>Desenvolvedores</span></a>
    </aside>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const notifyBtn = document.getElementById('notifyBtn');
    const notifyDropdown = document.getElementById('notifyDropdown');
    const notifyBadge = document.getElementById('notifyBadge');
    const notifyList = document.getElementById('notifyList');

    if (!notifyBtn) return;

    function carregarNotificacoes() {
        fetch('?rota=buscar-notificacoes')
            .then(res => res.json())
            .then(dados => {
                if (dados && dados.length > 0) {
                    notifyBadge.innerText = dados.length;
                    notifyBadge.style.display = 'flex';
                    
                    notifyList.innerHTML = '';
                    dados.forEach(item => {
                        notifyList.innerHTML += `<li style="padding: 8px; border-bottom: 1px solid rgba(255,255,255,.03); line-height: 1.4; color: var(--text, #fff);">🔹 ${item.mensagem}</li>`;
                    });
                } else {
                    notifyBadge.style.display = 'none';
                    notifyList.innerHTML = '<li style="padding: 8px; text-align: center; color: var(--muted, #888);">Nenhuma notificação nova</li>';
                }
            }).catch(() => console.log("Usuário deslogado ou sem conexão"));
    }

    // Gerencia o clique do sininho
    notifyBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        const estaAberto = notifyDropdown.style.display === 'block';
        notifyDropdown.style.display = estaAberto ? 'none' : 'block';

        // Se abriu a caixinha, oculta o balão e avisa o PHP para limpar
        if (!estaAberto) {
            notifyBadge.style.display = 'none';
            fetch('?rota=marcar-notificacoes-lidas');
        }
    });

    // Fecha a caixinha se clicar fora dela
    document.addEventListener('click', function() {
        if (notifyDropdown) notifyDropdown.style.display = 'none';
    });

    // Roda ao carregar a página e atualiza a cada 10 segundos de forma leve
    carregarNotificacoes();
    setInterval(carregarNotificacoes, 10000);
});
</script>
