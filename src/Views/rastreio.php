<?php 
include 'header.php'; 

// O index.php já fornece a conexão $pdo
$resultado = null;
$erro = "";

if (isset($_GET['q']) && !empty($_GET['q'])) {
    $termo = trim($_GET['q']);
    
    try {
        // SQL ajustado: Removido 'data_registro' que não existe na sua tabela inventario
        $sql = "SELECT i.id, i.descricao, c.categoria, l.armazem, l.estante, l.caixa
                FROM itens i
                LEFT JOIN categorias_item c ON i.categoria_id = c.id
                LEFT JOIN inventario inv ON i.id = inv.item_id
                LEFT JOIN locais l ON inv.local_id = l.id
                WHERE i.id = :id OR i.descricao LIKE :desc
                LIMIT 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'id' => is_numeric($termo) ? $termo : 0,
            'desc' => "%$termo%"
        ]);
        
        $resultado = $stmt->fetch();
        
        if (!$resultado) {
            $erro = "Nenhum item encontrado para: " . htmlspecialchars($termo);
        }
    } catch (PDOException $e) {
        $erro = "Erro técnico: " . $e->getMessage();
    }
}
?>

<main class="layout" style="background-color: #0f111a; color: #fff; padding: 20px; min-height: 100vh;">
    <section class="content">
        <header style="text-align: center; margin-bottom: 40px;">
            <h1 style="color: #7da5fb; font-size: 2.2rem; font-weight: 800; font-family: Verdana, sans-serif;">📍 Rastreio de Ativos</h1>
            <p class="muted">Localize itens no armazém em tempo real.</p>
        </header>

        <div class="card" style="background: #1a1d29; border-radius: 12px; padding: 30px; max-width: 600px; margin: 0 auto 30px auto; border: 1px solid #2d3142;">
            <form action="index.php" method="GET">
                <input type="hidden" name="rota" value="rastreio">
                <div style="display: flex; gap: 10px;">
                    <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" 
                           placeholder="ID ou Nome do item..." required
                           style="flex: 1; padding: 12px; border-radius: 8px; border: 1px solid #2d3142; background: #0f111a; color: #fff;">
                    <button type="submit" style="background: #7da5fb; color: #fff; border: none; padding: 0 25px; border-radius: 25px; cursor: pointer; font-weight: bold;">
                        Rastrear
                    </button>
                </div>
            </form>
        </div>

        <?php if ($erro): ?>
            <div style="background: rgba(255, 92, 124, 0.1); color: #ff5c7c; padding: 15px; border-radius: 8px; text-align: center; max-width: 600px; margin: 0 auto; border: 1px solid #ff5c7c;">
                <?= $erro ?>
            </div>
        <?php endif; ?>

        <?php if ($resultado): ?>
            <div class="card" style="background: #1a1d29; border-radius: 12px; max-width: 700px; margin: 0 auto; border: 1px solid #7da5fb; overflow: hidden;">
                
                <div style="background: rgba(125, 165, 251, 0.1); padding: 20px; border-bottom: 1px solid #2d3142;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h2 style="margin: 0; color: #7da5fb; font-family: Verdana;">#<?= $resultado['id'] ?> - <?= htmlspecialchars($resultado['descricao']) ?></h2>
                        <span style="background: #4ade80; color: #000; padding: 4px 12px; border-radius: 15px; font-size: 11px; font-weight: bold;">LOCALIZADO</span>
                    </div>
                </div>

                <div style="padding: 30px;">
                    <p style="color: #8a8f9d; margin-bottom: 5px;">Categoria</p>
                    <p style="font-weight: bold; font-size: 1.1rem;"><?= htmlspecialchars($resultado['categoria'] ?? 'Sem Categoria') ?></p>

                    <hr style="border: 0; border-top: 1px solid #2d3142; margin: 25px 0;">

                    <h3 style="margin-bottom: 20px; font-size: 1rem; color: #7da5fb; text-transform: uppercase; letter-spacing: 1px;">Endereço Físico</h3>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                        <div style="background: #0f111a; padding: 15px; border-radius: 10px; border: 1px solid #2d3142; text-align: center;">
                            <small style="color: #8a8f9d; display: block;">Armazém</small>
                            <span style="font-size: 1.2rem; font-weight: bold;"><?= htmlspecialchars($resultado['armazem'] ?? '---') ?></span>
                        </div>
                        <div style="background: #0f111a; padding: 15px; border-radius: 10px; border: 1px solid #2d3142; text-align: center;">
                            <small style="color: #8a8f9d; display: block;">Estante</small>
                            <span style="font-size: 1.2rem; font-weight: bold;"><?= htmlspecialchars($resultado['estante'] ?? '---') ?></span>
                        </div>
                        <div style="background: #0f111a; padding: 15px; border-radius: 10px; border: 1px solid #2d3142; text-align: center;">
                            <small style="color: #8a8f9d; display: block;">Caixa</small>
                            <span style="font-size: 1.2rem; font-weight: bold;"><?= htmlspecialchars($resultado['caixa'] ?? '---') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 40px;">
            <a href="index.php?rota=search" style="color: #8a8f9d; text-decoration: none; font-size: 0.9rem;">← Voltar para Pesquisa</a>
        </div>
    </section>
</main>

<?php include 'footer.php'; ?>
