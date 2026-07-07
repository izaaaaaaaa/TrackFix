<?php
include 'header.php';

/* =========================================
   EDITAR
========================================= */
$editando = false;
$itemEditar = null;

if (isset($_GET['editar'])) {

    $editando = true;
    $idEditar = (int) $_GET['editar'];

    try {
        $sqlEdit = "
            SELECT
                i.id, i.descricao, i.categoria_id,
                i.status, i.proxima_manutencao, i.manutencao_pendente,
                l.armazem, l.estante, l.caixa
            FROM itens i
            LEFT JOIN inventario inv ON i.id = inv.item_id
            LEFT JOIN locais l ON inv.local_id = l.id
            WHERE i.id = ? LIMIT 1
        ";
        $stmtEdit = $pdo->prepare($sqlEdit);
        $stmtEdit->execute([$idEditar]);
        $itemEditar = $stmtEdit->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $sqlEdit = "
            SELECT i.id, i.descricao, i.categoria_id, l.armazem, l.estante, l.caixa
            FROM itens i
            LEFT JOIN inventario inv ON i.id = inv.item_id
            LEFT JOIN locais l ON inv.local_id = l.id
            WHERE i.id = ? LIMIT 1
        ";
        $stmtEdit = $pdo->prepare($sqlEdit);
        $stmtEdit->execute([$idEditar]);
        $itemEditar = $stmtEdit->fetch(PDO::FETCH_ASSOC);
    }
}

/* =========================================
   BUSCAR CATEGORIAS
========================================= */
$stmtCat = $pdo->query("SELECT id, categoria FROM categorias_item");
$categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

/* =========================================
   BUSCAR ITENS + LOCALIZAÇÃO
========================================= */
try {
    $sql = "
        SELECT
            i.id, i.descricao, i.status, i.proxima_manutencao, i.manutencao_pendente,
            c.categoria AS nome_categoria, l.armazem, l.estante, l.caixa
        FROM itens i
        LEFT JOIN categorias_item c ON i.categoria_id = c.id
        LEFT JOIN inventario inv ON i.id = inv.item_id
        LEFT JOIN locais l ON inv.local_id = l.id
        ORDER BY i.id DESC
    ";
    $itens = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $sqlFallback = "
        SELECT
            i.id, i.descricao, c.categoria AS nome_categoria,
            l.armazem, l.estante, l.caixa
        FROM itens i
        LEFT JOIN categorias_item c ON i.categoria_id = c.id
        LEFT JOIN inventario inv ON i.id = inv.item_id
        LEFT JOIN locais l ON inv.local_id = l.id
        ORDER BY i.id DESC
    ";
    $itens = $pdo->query($sqlFallback)->fetchAll(PDO::FETCH_ASSOC);
}
?>

<style>
* { box-sizing: border-box; }

.tool-manager-section {
    width: 100%; min-height: 100vh; padding: 30px;
    background: linear-gradient(135deg, #090d1d, #111933) !important;
    border-radius: 22px; color: #fff; font-family: Arial, sans-serif;
}
.tool-manager-title { font-size: 1.6rem; font-weight: 700; margin-bottom: 25px; color: #ffffff; display: flex; align-items: center; gap: 10px; }
.tool-form-card {
    background: rgba(18, 24, 51, 0.65) !important; backdrop-filter: blur(16px) !important; -webkit-backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important; border-radius: 20px !important; padding: 30px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}
.tool-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
.field { display: flex; flex-direction: column; gap: 8px; }
.field label { font-size: 0.85rem; font-weight: 600; color: #a6aed0; letter-spacing: 0.5px; }
.tool-input, .tool-select {
    width: 100%; height: 46px; border: 1px solid rgba(255, 255, 255, 0.12) !important; outline: none !important;
    border-radius: 12px !important; background: rgba(255, 255, 255, 0.03) !important; color: #fff !important;
    padding: 0 16px !important; font-size: 0.9rem !important; transition: all 0.25s cubic-bezier(.2,.6,.2,1);
}
.tool-input:focus, .tool-select:focus {
    border-color: #7da5fb !important; background: rgba(255, 255, 255, 0.06) !important;
    box-shadow: 0 0 0 3px rgba(125, 165, 251, 0.25), 0 0 12px rgba(125, 165, 251, 0.2) !important;
}
.tool-select option { background-color: #121833 !important; color: #ffffff !important; }
.tool-buttons { grid-column: 1 / -1; display: flex; justify-content: flex-start; gap: 12px; margin-top: 10px; border-top: 1px solid rgba(255, 255, 255, 0.05); padding-top: 20px; }
.btn-save { border: none; background: #7da5fb !important; color: #0b1020 !important; padding: 12px 28px; border-radius: 50px; font-size: 0.9rem; font-weight: 700; cursor: pointer; transition: all 0.3s ease-in-out !important; }
.btn-save:hover { filter: brightness(1.1); transform: translateY(-2px); box-shadow: 0 0 20px rgba(125, 165, 251, 0.45) !important; }
.btn-cancel { border: 1px solid rgba(255, 255, 255, 0.15) !important; background: rgba(255, 255, 255, 0.05) !important; color: #fff !important; padding: 12px 28px; border-radius: 50px; font-size: 0.9rem; font-weight: 600; cursor: pointer; text-decoration: none !important; display: inline-flex; align-items: center; justify-content: center; transition: all 0.3s ease; }
.btn-cancel:hover { background: rgba(255, 255, 255, 0.1) !important; border-color: rgba(255, 255, 255, 0.3) !important; }

.table-wrapper { overflow-x: auto; border-radius: 16px; background: rgba(18, 24, 51, 0.4) !important; border: 1px solid rgba(255, 255, 255, 0.06); }
table { width: 100%; border-collapse: collapse; }
thead tr { background: rgba(255, 255, 255, 0.05) !important; border-bottom: 1px solid rgba(255, 255, 255, 0.08); }
thead th { padding: 16px 18px; text-align: left; color: #7da5fb; font-size: 0.85rem; font-weight: 700; letter-spacing: 0.5px; }
tbody tr { border-bottom: 1px solid rgba(255, 255, 255, 0.04); transition: background 0.2s ease; }
tbody tr:hover { background: rgba(125, 165, 251, 0.04) !important; }
tbody td { padding: 16px 18px; font-size: 0.9rem; color: #e9edf8; vertical-align: middle; }

.badge-category { display: inline-block; padding: 6px 14px; border-radius: 20px; background: rgba(125, 165, 251, 0.12) !important; color: #7da5fb; font-size: 0.75rem; font-weight: 600; border: 1px solid rgba(125, 165, 251, 0.15); }
.status-badge { display: inline-block; padding: 6px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; text-align: center; }
.status-disponivel { background: rgba(74, 222, 128, 0.12) !important; color: #4ade80; border: 1px solid rgba(74, 222, 128, 0.2); }
.status-emprestada { background: rgba(255, 209, 102, 0.12) !important; color: #ffd166; border: 1px solid rgba(255, 209, 102, 0.2); }
.status-manutencao { background: rgba(255, 107, 128, 0.12) !important; color: #ff6b80; border: 1px solid rgba(255, 107, 128, 0.2); }

/* Badges para sinalizar a manutenção pendente nas colunas */
.badge-maint-yes { background: rgba(255, 107, 128, 0.15) !important; color: #ff6b80; padding: 4px 12px; border-radius: 12px; font-weight: 700; font-size: 0.8rem; border: 1px solid rgba(255, 107, 128, 0.25); }
.badge-maint-no { background: rgba(74, 222, 128, 0.15) !important; color: #4ade80; padding: 4px 12px; border-radius: 12px; font-weight: 700; font-size: 0.8rem; border: 1px solid rgba(74, 222, 128, 0.25); }

.location-box { font-size: 0.8rem; line-height: 1.6; color: #a6aed0; }
.location-title { color: rgba(255, 255, 255, 0.4); font-weight: 600; }
.btn-edit { color: #7da5fb !important; text-decoration: none !important; font-size: 0.85rem; font-weight: 600; margin-right: 15px; }
.btn-edit:hover { color: #ffffff !important; text-shadow: 0 0 8px rgba(125, 165, 251, 0.6); }
.btn-delete { color: #ff5c7c !important; text-decoration: none !important; font-size: 0.85rem; font-weight: 600; }
.btn-delete:hover { color: #ff859a !important; text-shadow: 0 0 8px rgba(255, 92, 124, 0.6); }

@media(max-width: 768px) { .tool-form { grid-template-columns: 1fr; } .tool-buttons { flex-direction: column; } .btn-save, .btn-cancel { width: 100%; text-align: center; } }
</style>

<section class="tool-manager-section">

    <h2 class="tool-manager-title">💡 Gerenciar Ferramentas</h2>

    <div class="tool-form-card">
        <form action="index.php?rota=processar-ferramenta&acao=<?= $editando ? 'editar' : 'salvar' ?>" method="POST" class="tool-form">

            <?php if($editando): ?>
                <input type="hidden" name="id" value="<?= $itemEditar['id'] ?>">
            <?php endif; ?>

            <div class="field">
                <label>Nome</label>
                <input type="text" name="descricao" class="tool-input" placeholder="Nome da ferramenta" required value="<?= $editando ? htmlspecialchars($itemEditar['descricao'] ?? '') : '' ?>">
            </div>

            <div class="field">
                <label>Categoria</label>
                <select name="categoria_id" class="tool-select" required>
                    <option value="">Selecione</option>
                    <?php foreach($categorias as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($editando && ($itemEditar['categoria_id'] ?? null) == $cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['categoria']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <label>Armazém</label>
                <input type="text" name="armazem" class="tool-input" placeholder="Setor/Local" value="<?= $editando ? htmlspecialchars($itemEditar['armazem'] ?? '') : '' ?>">
            </div>

            <div class="field">
                <label>Estante</label>
                <input type="text" name="estante" class="tool-input" placeholder="Número/Letra" value="<?= $editando ? htmlspecialchars($itemEditar['estante'] ?? '') : '' ?>">
            </div>

            <div class="field">
                <label>Caixa</label>
                <input type="text" name="caixa" class="tool-input" placeholder="Identificação da caixa" value="<?= $editando ? htmlspecialchars($itemEditar['caixa'] ?? '') : '' ?>">
            </div>

            <div class="field">
                <label>Status</label>
                <select name="status" class="tool-select" required>
                    <option value="">Selecione</option>
                    <option value="Disponível" <?= ($editando && ($itemEditar['status'] ?? '') == 'Disponível') ? 'selected' : '' ?>>Disponível</option>
                    <option value="Emprestada" <?= ($editando && ($itemEditar['status'] ?? '') == 'Emprestada') ? 'selected' : '' ?>>Emprestada</option>
                    <option value="Em manutenção" <?= ($editando && ($itemEditar['status'] ?? '') == 'Em manutenção') ? 'selected' : '' ?>>Em manutenção</option>
                </select>
            </div>

            <div class="field">
                <label>Próxima manutenção</label>
                <input type="date" name="proxima_manutencao" class="tool-input" value="<?= $editando ? htmlspecialchars($itemEditar['proxima_manutencao'] ?? '') : '' ?>">
            </div>

            <div class="field">
                <label>Manutenção pendente</label>
                <select name="manutencao_pendente" class="tool-select">
                    <option value="">Selecione</option>
                    <option value="Não" <?= ($editando && ($itemEditar['manutencao_pendente'] ?? '') == 'Não') ? 'selected' : '' ?>>Não</option>
                    <option value="Sim" <?= ($editando && ($itemEditar['manutencao_pendente'] ?? '') == 'Sim') ? 'selected' : '' ?>>Sim</option>
                </select>
            </div>

            <div class="tool-buttons">
                <button type="submit" class="btn-save"><?= $editando ? 'Salvar Alterações' : 'Adicionar' ?></button>
                <a href="index.php?rota=tool-manager" class="btn-cancel">Cancelar</a>
            </div>
        </form>
    </div>

    <div class="tools-table-area">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ferramenta</th>
                        <th>Categoria</th>
                        <th>Localização</th>
                        <th>Status</th>
                        <th>Próx. Manutenção</th>
                        <th>Maint. Pendente</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($itens)): ?>
                        <?php foreach($itens as $item): ?>
                            <tr>
                                <td>#<?= $item['id'] ?></td>
                                <td style="font-weight: 600;"><?= htmlspecialchars($item['descricao']) ?></td>
                                <td><span class="badge-category"><?= htmlspecialchars($item['nome_categoria'] ?? 'Sem Categoria') ?></span></td>
                                <td>
                                    <div class="location-box">
                                        <span class="location-title">Arm:</span> <?= htmlspecialchars($item['armazem'] ?? '---') ?><br>
                                        <span class="location-title">Est:</span> <?= htmlspecialchars($item['estante'] ?? '---') ?><br>
                                        <span class="location-title">Cx:</span> <?= htmlspecialchars($item['caixa'] ?? '---') ?>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                        $statusAtual = $item['status'] ?? 'Disponível';
                                        $classeStatus = 'status-disponivel';
                                        if ($statusAtual == 'Emprestada') { $classeStatus = 'status-emprestada'; } 
                                        elseif ($statusAtual == 'Em manutenção') { $classeStatus = 'status-manutencao'; }
                                    ?>
                                    <span class="status-badge <?= $classeStatus ?>"><?= htmlspecialchars($statusAtual) ?></span>
                                </td>
                                
                                <td>
                                    <?php if(!empty($item['proxima_manutencao']) && $item['proxima_manutencao'] !== '0000-00-00'): ?>
                                        <a href="index.php?rota=manutencao&item_id=<?= $item['id'] ?>" style="color: #7da5fb; font-weight: 600; text-decoration: none;">
                                            📅 <?= date('d/m/Y', strtotime($item['proxima_manutencao'])) ?>
                                        </a>
                                    <?php else: ?>
                                        <a href="index.php?rota=manutencao&item_id=<?= $item['id'] ?>" style="color: rgba(255,255,255,0.3); font-size: 0.8rem; text-decoration: underline;">
                                            Agendar
                                        </a>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php 
                                        $pendente = $item['manutencao_pendente'] ?? 'Não';
                                        // Força "Sim" se o status geral for "Em manutenção" para manter a coerência
                                        if($statusAtual == 'Em manutenção') { $pendente = 'Sim'; }
                                    ?>
                                    <span class="<?= $pendente == 'Sim' ? 'badge-maint-yes' : 'badge-maint-no' ?>">
                                        <?= htmlspecialchars($pendente) ?>
                                    </span>
                                </td>

                                <td>
                                    <a href="index.php?rota=tool-manager&editar=<?= $item['id'] ?>" class="btn-edit">✏ Editar</a>
                                    <a href="index.php?rota=processar-ferramenta&acao=excluir&id=<?= $item['id'] ?>" class="btn-delete" onclick="return confirm('Deseja remover esta ferramenta?')">🗑 Remover</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" style="text-align:center; padding:30px; color:#a6aed0;">Nenhuma ferramenta cadastrada.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
