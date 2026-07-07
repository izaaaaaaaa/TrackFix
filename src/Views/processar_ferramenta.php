<?php
/**
 * ARQUIVO: src/Views/processar_ferramenta.php
 */

if (!isset($pdo)) {
    die("Erro: Conexão com o banco de dados não disponível.");
}

$acao = $_GET['acao'] ?? '';

// =========================================
// AÇÃO: SALVAR (NOVO CADASTRO)
// =========================================
if ($acao === 'salvar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Captura e higieniza os dados do formulário
    $descricao           = trim($_POST['descricao'] ?? '');
    $categoria_id        = !empty($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : null;
    $status              = trim($_POST['status'] ?? 'Disponível');
    $proxima_manutencao  = !empty($_POST['proxima_manutencao']) ? $_POST['proxima_manutencao'] : null;
    $manutencao_pendente = trim($_POST['manutencao_pendente'] ?? 'Não');

    // Regra inteligente: Se o status for "Em manutenção", força a pendência para "Sim"
    if ($status === 'Em manutenção') {
        $manutencao_pendente = 'Sim';
    }

    if (!empty($descricao) && !empty($categoria_id)) {
        try {
            $pdo->beginTransaction();

            // 2. Insere os dados na tabela 'itens' (incluindo as colunas novas de controle)
            $sqlItem = "INSERT INTO itens (descricao, categoria_id, status, proxima_manutencao, manutencao_pendente) VALUES (?, ?, ?, ?, ?)";
            $stmtItem = $pdo->prepare($sqlItem);
            $stmtItem->execute([$descricao, $categoria_id, $status, $proxima_manutencao, $manutencao_pendente]);
            $item_id = $pdo->lastInsertId();

            // 3. Insere a localização física na tabela 'locais'
            $armazem = trim($_POST['armazem'] ?? '');
            $estante = trim($_POST['estante'] ?? '');
            $caixa   = trim($_POST['caixa'] ?? '');

            $sqlLocal = "INSERT INTO locais (armazem, estante, caixa) VALUES (?, ?, ?)";
            $stmtLocal = $pdo->prepare($sqlLocal);
            $stmtLocal->execute([$armazem, $estante, $caixa]);
            $local_id = $pdo->lastInsertId();

            // 4. Vincula o item ao local na tabela pivot 'inventario'
            $sqlInv = "INSERT INTO inventario (item_id, local_id) VALUES (?, ?)";
            $pdo->prepare($sqlInv)->execute([$item_id, $local_id]);

            // 🔔 Gravar a notificação real atrelada ao usuário logado ANTES do commit
            $msgNotif = "🔧 Nova ferramenta cadastrada: \"{$descricao}\" com status atual \"{$status}\".";
            $stmtNotif = $pdo->prepare("INSERT INTO notificacoes (usuario_id, mensagem) VALUES (?, ?)");
            $stmtNotif->execute([$_SESSION['usuario_id'], $msgNotif]);

            $pdo->commit();
            header("Location: ?rota=tool-manager");
            exit();

        } catch (Exception $e) {
            $pdo->rollBack();
            die("Erro ao salvar ferramenta: " . $e->getMessage());
        }
    }
}

// =========================================
// AÇÃO: EDITAR (ATUALIZAR EXISTENTE)
// =========================================
if ($acao === 'editar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $id                  = (int)($_POST['id'] ?? 0);
    $descricao           = trim($_POST['descricao'] ?? '');
    $categoria_id        = !empty($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : null;
    $status              = trim($_POST['status'] ?? 'Disponível');
    $proxima_manutencao  = !empty($_POST['proxima_manutencao']) ? $_POST['proxima_manutencao'] : null;
    $manutencao_pendente = trim($_POST['manutencao_pendente'] ?? 'Não');

    // Regra inteligente: Se o status for "Em manutenção", força a pendência para "Sim"
    if ($status === 'Em manutenção') {
        $manutencao_pendente = 'Sim';
    }

    if ($id > 0 && !empty($descricao) && !empty($categoria_id)) {
        try {
            $pdo->beginTransaction();

            // 1. Atualiza as colunas principais na tabela 'itens'
            $sqlUpdateItem = "UPDATE itens SET descricao = ?, categoria_id = ?, status = ?, proxima_manutencao = ?, manutencao_pendente = ? WHERE id = ?";
            $pdo->prepare($sqlUpdateItem)->execute([$descricao, $categoria_id, $status, $proxima_manutencao, $manutencao_pendente, $id]);

            // 2. Localiza qual id da tabela 'locais' pertence a este item através do inventário
            $armazem = trim($_POST['armazem'] ?? '');
            $estante = trim($_POST['estante'] ?? '');
            $caixa   = trim($_POST['caixa'] ?? '');

            $stmtLoc = $pdo->prepare("SELECT local_id FROM inventario WHERE item_id = ?");
            $stmtLoc->execute([$id]);
            $inv = $stmtLoc->fetch(PDO::FETCH_ASSOC);

            if ($inv && !empty($inv['local_id'])) {
                // Atualiza o endereço antigo da ferramenta
                $sqlUpdateLocal = "UPDATE locais SET armazem = ?, estante = ?, caixa = ? WHERE id = ?";
                $pdo->prepare($sqlUpdateLocal)->execute([$armazem, $estante, $caixa, $inv['local_id']]);
            } else {
                // Caso por algum motivo o item não tivesse local, cria um novo agora
                $sqlLocal = "INSERT INTO locais (armazem, estante, caixa) VALUES (?, ?, ?)";
                $stmtLocal = $pdo->prepare($sqlLocal);
                $stmtLocal->execute([$armazem, $estante, $caixa]);
                $local_id = $pdo->lastInsertId();

                $sqlInv = "INSERT INTO inventario (item_id, local_id) VALUES (?, ?)";
                $pdo->prepare($sqlInv)->execute([$id, $local_id]);
            }

            // 🔔 Gravar a notificação real de edição ANTES do commit
            $msgNotif = "🔧 Ferramenta Editada: \"{$descricao}\" foi modificada no inventário.";
          $stmtNotif = $pdo->prepare("INSERT INTO notificacoes (usuario_id, mensagem) VALUES (?, ?)");
           $stmtNotif->execute([$_SESSION['usuario_id'], $msgNotif]);

            $pdo->commit();
            header("Location: ?rota=tool-manager");
            exit();

        } catch (Exception $e) {
            $pdo->rollBack();
            die("Erro ao atualizar ferramenta: " . $e->getMessage());
        }
    }
}

// =========================================
// AÇÃO: EXCLUIR
// =========================================
if ($acao === 'excluir') {
    $id = (int)($_GET['id'] ?? 0);

    if ($id > 0) {
        try {
            $pdo->beginTransaction();

            // Busca a descrição da ferramenta antes de deletar para constar no alerta
            $stmtFind = $pdo->prepare("SELECT descricao FROM itens WHERE id = ?");
            $stmtFind->execute([$id]);
            $ferramentaVelha = $stmtFind->fetch(PDO::FETCH_ASSOC);

            // Busca o local associado para deletar das duas tabelas e não deixar lixo no banco
            $stmtLoc = $pdo->prepare("SELECT local_id FROM inventario WHERE item_id = ?");
            $stmtLoc->execute([$id]);
            $inv = $stmtLoc->fetch(PDO::FETCH_ASSOC);

            // Deleta o vínculo do inventário primeiro (Foreign Key constraint prevention)
            $pdo->prepare("DELETE FROM inventario WHERE item_id = ?")->execute([$id]);
            
            // Deleta a ferramenta
            $pdo->prepare("DELETE FROM itens WHERE id = ?")->execute([$id]);

            // Deleta o local físico se ele existia
            if ($inv && !empty($inv['local_id'])) {
                $pdo->prepare("DELETE FROM locais WHERE id = ?")->execute([$inv['local_id']]);
            }

            // 🔔 Gravar a notificação real de exclusão ANTES do commit
            if ($ferramentaVelha) {
                $msgNotif = "🗑️ A ferramenta \"{$ferramentaVelha['descricao']}\" foi removida do sistema.";
                $stmtNotif = $pdo->prepare("INSERT INTO notificacoes (usuario_id, mensagem) VALUES (?, ?)");
                $stmtNotif->execute([$_SESSION['usuario_id'], $msgNotif]);
            }

            $pdo->commit();

        } catch (Exception $e) {
            $pdo->rollBack();
            die("Erro ao excluir ferramenta: " . $e->getMessage());
        }
    }
    header("Location: ?rota=tool-manager");
    exit();
}
