<?php 
include 'header.php'; 

// Garante que a listagem de manutenções existe para o JavaScript ler
$manutencoesLista = $manutencoes ?? [];
?>

<style>
    /* Container principal da Agenda */
    .maint-calendar-card {
        background: #1a1d29; 
        border: 1px solid #2d3142; 
        border-radius: 15px; 
        padding: 25px;
        margin-bottom: 25px;
        width: 100%;
    }

    .calendar-grid { 
        display: grid; 
        grid-template-columns: repeat(7, 1fr); 
        gap: 12px; 
    }
    
    .weekday { 
        text-align: center; 
        font-weight: bold; 
        color: #7da5fb; 
        padding-bottom: 12px; 
        text-transform: uppercase;
        font-size: 0.9rem;
    }
    
    .day {
        min-height: 95px; 
        border: 1px solid rgba(255, 255, 255, 0.05);
        background: rgba(255, 255, 255, 0.02); 
        border-radius: 12px;
        display: flex; 
        flex-direction: column; 
        align-items: center; 
        justify-content: center;
        cursor: pointer; 
        transition: all 0.2s ease;
        position: relative;
    }
    
    .day:hover { 
        border-color: #7da5fb; 
        background: rgba(125, 165, 251, 0.08); 
    }

    .day.has-event {
        border-color: #7da5fb;
        box-shadow: inset 0 0 8px rgba(125, 165, 251, 0.1);
    }

    .is-today {
        background: #7da5fb !important; 
        color: #000 !important;
        width: 36px; 
        height: 36px; 
        border-radius: 50%;
        display: flex; 
        align-items: center; 
        justify-content: center;
        box-shadow: 0 0 12px #7da5fb; 
        font-weight: bold;
    }

    .event-dot { 
        width: 8px; 
        height: 8px; 
        background: #7da5fb; 
        border-radius: 50%; 
        margin-top: 6px; 
        box-shadow: 0 0 8px #7da5fb; 
    }
    
    /* Grid inferior */
    .maint-card {
        background: #1a1d29;
        border: 1px solid #2d3142;
        border-radius: 15px;
        padding: 25px;
        height: 100%;
    }

    .maint-input {
        width: 100%;
        padding: 12px;
        border-radius: 8px;
        border: 1px solid #2d3142;
        background: #0f111a;
        color: #fff;
        margin-top: 5px;
    }

    .maint-input:focus {
        border-color: #7da5fb;
        outline: none;
    }

    /* MODAL ISOLADO E CENTRALIZADO */
    .custom-modal-override {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        background: rgba(0, 0, 0, 0.75) !important;
        z-index: 99999 !important;
        display: none;
        align-items: center !important;
        justify-content: center !important;
    }

    .custom-modal-override.show {
        display: flex !important;
    }

    .custom-modal-box {
        background: #1a1d29 !important;
        border: 1px solid #2d3142;
        border-radius: 16px !important;
        width: 100% !important;
        max-width: 500px !important;
        padding: 24px !important;
        box-shadow: 0 10px 30px rgba(0,0,0,0.6) !important;
        animation: modalFadeIn 0.2s ease-out;
    }

    @keyframes modalFadeIn {
        from { transform: scale(0.95); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    
    .event-item {
        background: #0f111a; 
        border: 1px solid #2d3142; 
        padding: 14px;
        border-radius: 10px; 
        margin-bottom: 10px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .event-actions {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
    }
</style>

<main class="layout">
    <div class="content" style="padding: 20px; animation: fadeIn 0.8s ease-out;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="color: #7da5fb; font-family: Verdana; margin: 0; font-weight: bold;">📅 Agenda de Manutenção</h2>
            <div style="background: rgba(125, 165, 251, 0.1); border: 1px solid #7da5fb; color: #7da5fb; padding: 8px 16px; border-radius: 8px; font-weight: bold; font-size: 0.9rem;">
                HOJE: <?= date('d/m/Y') ?>
            </div>
        </div>

        <div class="maint-calendar-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <button type="button" style="background: transparent; border: 1px solid #2d3142; color: #7da5fb; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: bold;" onclick="mudarMes(-1)">❮ Anterior</button>
                <h3 id="mesAno" style="margin: 0; color: #fff; font-family: Verdana; font-weight: bold; font-size: 1.3rem; letter-spacing: 1px;"></h3>
                <button type="button" style="background: transparent; border: 1px solid #2d3142; color: #7da5fb; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: bold;" onclick="mudarMes(1)">Próximo ❯</button>
            </div>

            <div class="calendar-grid" id="grid"></div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; align-items: start;">
            
            <div class="maint-card">
                <h3 id="labelForm" style="margin-bottom: 20px; color: #fff; font-size: 1.15rem; font-weight: bold; font-family: Verdana;">Registrar Novo Serviço</h3>
                <form action="?rota=salvar-manutencao" method="POST">
                    
                    <input type="hidden" name="id_editar" id="id_editar" value="">

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="field">
                            <label style="color: #8a8f9d; font-size: 0.85rem; font-weight: 500;">ID do Item</label>
                            <input type="number" name="item_do_inventario" id="form_item" class="maint-input" required placeholder="Ex: 101">
                        </div>
                        <div class="field">
                            <label style="color: #8a8f9d; font-size: 0.85rem; font-weight: 500;">Data Realizada</label>
                            <input type="date" name="data_manutencao" id="ini" class="maint-input" required>
                        </div>
                        <div class="field">
                            <label style="color: #8a8f9d; font-size: 0.85rem; font-weight: 500;">Próxima Manutenção</label>
                            <input type="date" name="proxima_manutencao" id="fim" class="maint-input">
                        </div>
                        <div class="field">
                            <label style="color: #8a8f9d; font-size: 0.85rem; font-weight: 500;">Tipo de Serviço</label>
                            <input type="text" name="descricao_servico" id="form_titulo" class="maint-input" placeholder="Ex: Calibração" required>
                        </div>
                    </div>
                    
                    <button type="submit" id="btnEnviar" style="width: 100%; margin-top: 25px; border: none; background: #7da5fb; color: #000; font-weight: bold; padding: 13px; border-radius: 8px; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 8px; font-size: 0.95rem;">
                        <i class="fas fa-save"></i> Agendar Agora
                    </button>
                    
                    <button type="button" id="btnCancelarEdicao" style="width: 100%; margin-top: 10px; border: 1px solid #ef4444; background: transparent; color: #ef4444; font-weight: bold; padding: 10px; border-radius: 8px; cursor: pointer; display: none; justify-content: center; align-items: center;" onclick="resetarFormulario()">
                        Cancelar Edição
                    </button>
                </form>
            </div>

            <div class="maint-card">
                <h3 style="margin-bottom: 20px; color: #fff; font-size: 1.15rem; font-weight: bold; font-family: Verdana;">Histórico Recente</h3>
                <div style="max-height: 255px; overflow-y: auto; padding-right: 5px;">
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <?php if(empty($manutencoesLista)): ?>
                            <li style="color: #8a8f9d; font-size: 0.9rem;">Nenhuma manutenção registrada.</li>
                        <?php else: ?>
                            <?php foreach($manutencoesLista as $m): ?>
                                <li style="display: flex; justify-content: space-between; align-items: center; padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05); background: rgba(0,0,0,0.1); border-radius: 8px; margin-bottom: 8px;">
                                    <div>
                                        <span style="color: #7da5fb; font-size: 0.8rem; font-weight: bold;">
                                            Próxima: <?= !empty($m['proxima_manutencao']) ? date('d/m/Y', strtotime($m['proxima_manutencao'])) : 'Não agendada' ?>
                                        </span>
                                        <div style="margin-top: 4px; color: #fff; font-size: 0.9rem;">
                                            <strong>Item #<?= $m['item_do_inventario'] ?? 'N/A' ?></strong> 
                                            <span style="margin-left: 5px; color: #8a8f9d;">— <?= htmlspecialchars($m['descricao_servico'] ?? '') ?></span>
                                        </div>
                                        <div style="color: #626775; font-size: 0.75rem; margin-top: 2px;">
                                            Realizada em: <?= !empty($m['data_manutencao']) ? date('d/m/Y', strtotime($m['data_manutencao'])) : '' ?>
                                        </div>
                                    </div>
                                    <span style="font-size: 1.1rem; cursor: pointer;" onclick="prepararEdicaoDirect('<?= $m['id'] ?>', '<?= addslashes($m['descricao_servico']) ?>', '<?= $m['item_do_inventario'] ?>', '<?= substr($m['data_manutencao'], 0, 10) ?>', '<?= !empty($m['proxima_manutencao']) ? substr($m['proxima_manutencao'], 0, 10) : '' ?>')">🛠️</span>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</main>

<div id="customModal" class="custom-modal-override">
    <div class="custom-modal-box">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h5 style="color: #7da5fb; font-weight: bold; margin: 0; font-family: Verdana; font-size: 1.1rem;">Manutenções Agendadas</h5>
            <button type="button" style="background: transparent; border: none; color: #fff; font-size: 1.4rem; cursor: pointer; line-height: 1;" onclick="fecharModal()">×</button>
        </div>
        <div id="modalListaContent" style="max-height: 300px; overflow-y: auto; padding-right: 3px;"></div>
    </div>
</div>

<script>
    let dAtual = new Date();
    const evs = <?php echo json_encode(array_values($manutencoesLista)); ?>;

    function render() {
        const grid = document.getElementById('grid');
        const hoje = new Date();
        const mes = dAtual.getMonth(), ano = dAtual.getFullYear();
        
        const mesesNome = ["JANEIRO", "FEVEREIRO", "MARÇO", "ABRIL", "MAIO", "JUNHO", "JULHO", "AGOSTO", "SETEMBRO", "OUTUBRO", "NOVEMBRO", "DEZEMBRO"];
        document.getElementById('mesAno').innerText = `${mesesNome[mes]} DE ${ano}`;
       
        grid.innerHTML = '';
        ['DOM','SEG','TER','QUA','QUI','SEX','SÁB'].forEach(d => grid.innerHTML += `<div class="weekday">${d}</div>`);

        const priDia = new Date(ano, mes, 1).getDay();
        const ultDia = new Date(ano, mes + 1, 0).getDate();

        for(let i=0; i<priDia; i++) grid.innerHTML += '<div></div>';

        for(let d=1; d<=ultDia; d++) {
            const dataStr = `${ano}-${String(mes+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
            
            const diaEvs = evs.filter(e => {
                if(!e.proxima_manutencao) return false;
                return e.proxima_manutencao.substring(0, 10) === dataStr;
            });
            
            const ehHoje = (d===hoje.getDate() && mes===hoje.getMonth() && ano===hoje.getFullYear());

            const div = document.createElement('div');
            div.className = `day ${diaEvs.length ? 'has-event' : ''}`;
            
            div.onclick = (e) => {
                if(e.target.tagName === 'A' || e.target.tagName === 'BUTTON') return;

                document.getElementById('fim').value = dataStr;
                
                const l = document.getElementById('modalListaContent');
                l.innerHTML = diaEvs.map(eventObj => `
                    <div class="event-item">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                            <div>
                                <div style="color: #7da5fb; font-weight: bold; font-size: 0.85rem; margin-bottom: 3px;">ITEM #${eventObj.item_do_inventario}</div>
                                <div style="color: #fff; font-size: 0.9rem; line-height: 1.4;">${eventObj.descricao_servico}</div>
                                <div style="color: #8a8f9d; font-size: 0.75rem; margin-top: 4px;">Realizada em: ${eventObj.data_manutencao ? eventObj.data_manutencao.substring(0,10).split('-').reverse().join('/') : 'N/A'}</div>
                            </div>
                        </div>
                        <div class="event-actions">
                            <button type="button" class="btn" style="background:#7da5fb; color:#000; border:none; padding:4px 10px; font-size:11px; font-weight:bold; border-radius:4px; cursor:pointer;" onclick="prepararEdicao('${eventObj.id}', '${escapeJs(eventObj.descricao_servico)}', '${eventObj.item_do_inventario}', '${eventObj.data_manutencao.substring(0,10)}', '${eventObj.proxima_manutencao ? eventObj.proxima_manutencao.substring(0,10) : ''}')">EDITAR</button>
                            <a href="?remover=${eventObj.id}" class="btn" style="background:#ef4444; color:#fff; padding:4px 10px; font-size:11px; font-weight:bold; border-radius:4px; text-decoration:none;" onclick="return confirm('Deseja remover esta manutenção?')">REMOVER</a>
                        </div>
                    </div>
                `).join('') || '<p class="text-center text-muted small" style="margin: 15px 0; color: #8a8f9d !important;">Nenhuma manutenção programada para este dia.</p>';
                
                document.getElementById('customModal').classList.add('show');
            };

            div.innerHTML = `<span class="${ehHoje ? 'is-today' : ''}">${d}</span>`;
            if(diaEvs.length && !ehHoje) div.innerHTML += `<div class="event-dot"></div>`;
            grid.appendChild(div);
        }
    }

    function prepararEdicao(id, descricao, item, inicio, fim) {
        fecharModal();
        prepararEdicaoDirect(id, descricao, item, inicio, fim);
    }

    function prepararEdicaoDirect(id, descricao, item, inicio, fim) {
        document.getElementById('id_editar').value = id;
        document.getElementById('form_titulo').value = descricao;
        document.getElementById('form_item').value = item;
        document.getElementById('ini').value = inicio;
        document.getElementById('fim').value = fim;
       
        document.getElementById('labelForm').innerText = "Editar Evento Selecionado";
        document.getElementById('btnEnviar').innerText = "Salvar Alterações";
        document.getElementById('btnEnviar').style.backgroundColor = "#7da5fb";
        document.getElementById('btnCancelarEdicao').style.display = "flex";
        
        document.getElementById('labelForm').scrollIntoView({ behavior: 'smooth' });
    }

    function resetarFormulario() {
        document.getElementById('id_editar').value = "";
        document.getElementById('form_titulo').value = "";
        document.getElementById('form_item').value = "";
        document.getElementById('ini').value = "";
        document.getElementById('fim').value = "";
        document.getElementById('labelForm').innerText = "Registrar Novo Serviço";
        document.getElementById('btnEnviar').innerText = "Agendar Agora";
        document.getElementById('btnEnviar').style.backgroundColor = "#7da5fb";
        document.getElementById('btnCancelarEdicao').style.display = "none";
    }

    function fecharModal() {
        document.getElementById('customModal').classList.remove('show');
    }

    window.onclick = function(event) {
        const modal = document.getElementById('customModal');
        if (event.target == modal) {
            fecharModal();
        }
    }

    function escapeJs(str) {
        return str.replace(/'/g, "\\'").replace(/"/g, '\\"');
    }

    function mudarMes(v) { dAtual.setMonth(dAtual.getMonth()+v); render(); }
    window.onload = render;
</script>

<?php include 'footer.php'; ?>
