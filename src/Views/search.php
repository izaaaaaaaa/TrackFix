<?php
include 'header.php';

// 1. CAPTURA DOS FILTROS
$termo     = $_GET['q'] ?? '';
$categoria = $_GET['cat'] ?? 'Todos';

try {

    $sql = "SELECT i.id, i.descricao, c.categoria
            FROM itens i
            LEFT JOIN categorias_item c ON i.categoria_id = c.id
            WHERE 1=1";

    $params = [];

    if (!empty($termo)) {
        $sql .= " AND i.descricao LIKE :termo";
        $params['termo'] = "%$termo%";
    }

    if ($categoria !== 'Todos') {
        $sql .= " AND c.categoria = :categoria";
        $params['categoria'] = $categoria;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $ferramentas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmtCat = $pdo->query("SELECT categoria FROM categorias_item");
    $listaCategorias = $stmtCat->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {

    $ferramentas = [];
    $listaCategorias = [];
}
?>

<main class="layout">
    <section class="content tool-manager-section">

        <!-- HEADER -->
        <div class="card" style="padding:25px;">
            <div class="row" style="justify-content: space-between; align-items:center; flex-wrap:wrap; gap:15px;">
                <div>
                    <h2 style="margin:0; color:var(--primary);">
                        🔎 Pesquisa de Ferramentas
                    </h2>

                    <p class="muted" style="margin-top:6px;">
                        Consulte ferramentas cadastradas no sistema
                    </p>
                </div>

                <div class="badge ok" style="font-size:14px;">
                    <?= count($ferramentas) ?> resultado(s)
                </div>
            </div>
        </div>

        <!-- FILTROS -->
        <form id="filterForm" method="GET" action="index.php" class="card">
            <input type="hidden" name="rota" value="search">

            <div class="grid cols-2">

                <!-- CATEGORIA -->
                <div class="field">
                    <label>Categoria</label>

                    <select name="cat" class="input-field">
                        <option value="Todos">Todas as Categorias</option>

                        <?php foreach ($listaCategorias as $catNome): ?>
                            <option
                                value="<?= $catNome ?>"
                                <?= $categoria == $catNome ? 'selected' : '' ?>>
                                <?= $catNome ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- PESQUISA -->
                <div class="field">
                    <label>Buscar Ferramenta</label>

                    <input
                        name="q"
                        type="text"
                        placeholder="Ex: Furadeira..."
                        value="<?= htmlspecialchars($termo) ?>"
                        class="input-field">
                </div>

            </div>

            <div style="margin-top:20px; display:flex; gap:12px; flex-wrap:wrap;">

                <button type="submit" class="btn">
                    🔍 Pesquisar
                </button>

                <a href="index.php?rota=search" class="btn btn-ghost">
                    Limpar filtros
                </a>

            </div>
        </form>

        <!-- TABELA -->
        <div class="card">

            <div class="row" style="margin-bottom:18px;">
                <h3 style="margin:0;">
                    📦 Ferramentas Encontradas
                </h3>

                <span class="right muted">
                    Atualizado em tempo real
                </span>
            </div>

            <div style="overflow-x:auto;">

                <table>

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ferramenta</th>
                            <th>Categoria</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (empty($ferramentas)): ?>

                        <tr>
                            <td colspan="4">

                                <div style="
                                    padding:50px 20px;
                                    text-align:center;
                                ">

                                    <div style="font-size:60px; margin-bottom:10px;">
                                        🔍
                                    </div>

                                    <h3 style="margin-bottom:8px;">
                                        Nenhuma ferramenta encontrada
                                    </h3>

                                    <p class="muted">
                                        Tente alterar os filtros da pesquisa
                                    </p>

                                </div>

                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($ferramentas as $f): ?>

                            <tr class="table-row">

                                <td>
                                    <span class="muted">
                                        #<?= $f['id'] ?>
                                    </span>
                                </td>

                                <td>

                                    <div style="
                                        display:flex;
                                        flex-direction:column;
                                        gap:4px;
                                    ">

                                        <strong style="font-size:15px;">
                                            <?= htmlspecialchars($f['descricao']) ?>
                                        </strong>

                                        <small class="muted">
                                            Código interno #<?= $f['id'] ?>
                                        </small>

                                    </div>

                                </td>

                                <td>

                                    <span style="
                                        background: rgba(122,162,255,.15);
                                        color: var(--primary);
                                        padding: 6px 14px;
                                        border-radius: 999px;
                                        font-size: 13px;
                                        font-weight: 600;
                                        display:inline-block;
                                    ">
                                        <?= htmlspecialchars($f['categoria'] ?? 'Sem Categoria') ?>
                                    </span>

                                </td>

                                <td>

                                    <span class="badge ok">
                                        ✔ Disponível
                                    </span>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>
        </div>

    </section>
</main>

<?php include 'footer.php'; ?>
