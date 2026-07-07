<?php
/**
 * ARQUIVO: public/index.php
 */

// 1. Configura o fuso horário correto para o PHP (Horário de Brasília)
date_default_timezone_set('America/Sao_Paulo');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// CORREÇÃO PARA HOSTEGEM COMPARTILHADA (InfinityFree):
// Omitir avisos simples (E_NOTICE) para evitar quebras visuais devido a limpezas de sessão do servidor
error_reporting(E_ALL & ~E_NOTICE);

// Configura um caminho local seguro para as sessões, evitando o erro de permissão em /php_sessions
$sessionPath = dirname(__DIR__) . '/session_tmp';
if (!file_exists($sessionPath)) {
    @mkdir($sessionPath, 0700, true);
}
if (is_writable($sessionPath)) {
    session_save_path($sessionPath);
}

session_start();

$baseDir = dirname(__DIR__);
$configPath = $baseDir . '/config/database.php';

if (file_exists($configPath)) {
    require_once $configPath;
    
    // 2. Sincroniza o fuso horário também na conexão activa do banco de dados (MySQL)
    if (isset($pdo)) {
        try {
            $pdo->exec("SET time_zone = '-03:00';");
        } catch (Exception $e) {
            // Silencioso caso o driver não suporte, evitando quebras
        }
    }
} else {
    die("Erro Crítico: Ficheiro database.php não encontrado.");
}

$rota = $_GET['rota'] ?? 'home';

// Função auxiliar para verificar se o usuário está logado
function verificarAcesso() {
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: ?rota=login");
        exit();
    }
}

switch ($rota) {
    case 'home':
        include $baseDir . '/src/Views/principal.php';
        break;

   case 'login':
        if (isset($_SESSION['usuario_id'])) {
            header("Location: ?rota=profile");
            exit();
        }

        include $baseDir . '/src/Views/login.php';
        if (isset($_GET['cadastro']) && $_GET['cadastro'] === 'sucesso') {
            echo "<script>window.onload = () => window.toast('Conta criada! Faça seu login.', 'success');</script>";
        }
        // Exibe o Toast caso a conta tenha sido excluída com sucesso
        if (isset($_GET['excluido']) && $_GET['excluido'] === 'sucesso') {
            echo "<script>window.onload = () => window.toast('Sua conta foi excluída permanentemente.', 'success');</script>";
        }
        break;

    case 'assinatura':
        verificarAcesso(); 
        include $baseDir . '/src/Views/assinatura.php';
        break;
        
    case 'processar-assinatura':
        verificarAcesso();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $plano = $_POST['plano'] ?? '';
            if (in_array($plano, ['prata', 'ouro'])) {
                header("Location: ?rota=checkout&plano=" . $plano);
                exit();
            }
        }
        header("Location: ?rota=assinatura");
        exit();
        break;
        
    case 'atualizar-plano':
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $planoGet = $_GET['plano'] ?? 'bronze';
        $planoBD = 'basico';
        
        if ($planoGet === 'prata') {
            $planoBD = 'premium';
        } elseif ($planoGet === 'ouro') {
            $planoBD = 'enterprise';
        }

        $empresaId = $_SESSION['empresa_id'] ?? $_SESSION['usuario_id'] ?? $_SESSION['id'] ?? null;

        if (!$empresaId) {
            die("Erro: Identificador da empresa/utilizador não encontrado na sessão.");
        }

        try {
            $stmt = $pdo->prepare("UPDATE assinaturas SET plano = :plano WHERE empresa_id = :empresa_id OR id = :empresa_id");
            $stmt->execute([
                ':plano'      => $planoBD,
                ':empresa_id' => $empresaId
            ]);

            $_SESSION['usuario_plano'] = $planoGet; 

            header("Location: ?rota=assinatura&sucesso=1");
            exit();

        } catch (PDOException $e) {
            die("Erro ao atualizar banco de dados: " . $e->getMessage());
        }
        break;
        
    // CORRIGIDO: Agora usa 'dashboard_premium' idêntico ao link do seu menu
    case 'dashboard_premium':
        verificarAcesso();
        
        // Bloqueia quem não for Prata ou Ouro
        $planoAtual = $_SESSION['usuario_plano'] ?? 'bronze';
        if ($planoAtual !== 'prata' && $planoAtual !== 'ouro') {
            header("Location: ?rota=assinatura&erro=premium_required");
            exit();
        }
        
        include $baseDir . '/src/Views/dashboard_premium.php';
        break;

    // ========================================================
    // ROTAS DE NOTIFICAÇÕES (SISTEMA DO SININHO 🔔)
    // ========================================================
    case 'notificacoes':
        verificarAcesso();
        include $baseDir . '/src/Views/notificacoes.php';
        break;

    case 'buscar-notificacoes':
        if (!isset($_SESSION['usuario_id'])) {
            echo json_encode([]);
            exit();
        }
        try {
            $stmt = $pdo->prepare("SELECT id, mensagem FROM notificacoes WHERE (usuario_id = ? OR usuario_id = 0) AND lida = 0 ORDER BY data_criacao DESC");
            $stmt->execute([$_SESSION['usuario_id']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            echo json_encode([]);
        }
        exit();
        break;

    case 'marcar-notificacoes-lidas':
        if (isset($_SESSION['usuario_id'])) {
            try {
                $stmt = $pdo->prepare("UPDATE notificacoes SET lida = 1 WHERE usuario_id = ? OR usuario_id = 0");
                $stmt->execute([$_SESSION['usuario_id']]);
            } catch (Exception $e) {}
        }
        exit();
        break;

    case 'notificacoes-acao':
        verificarAcesso();
        $acao = $_GET['acao'] ?? '';
        $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        $usuario_id = $_SESSION['usuario_id'];

        if ($acao === 'ler' && $id) {
            $stmt = $pdo->prepare("UPDATE notificacoes SET lida = 1 WHERE id = ? AND (usuario_id = ? OR usuario_id = 0)");
            $stmt->execute([$id, $usuario_id]);
        } 
        elseif ($acao === 'ler_todas') {
            $stmt = $pdo->prepare("UPDATE notificacoes SET lida = 1 WHERE usuario_id = ? OR usuario_id = 0");
            $stmt->execute([$usuario_id]);
        } 
        elseif ($acao === 'excluir' && $id) {
            $stmt = $pdo->prepare("DELETE FROM notificacoes WHERE id = ? AND (usuario_id = ? OR usuario_id = 0)");
            $stmt->execute([$id, $usuario_id]);
        }

        header("Location: ?rota=notificacoes");
        exit();
        break;

    // ========================================================
    // ROTA DO GOOGLE LEVE E RÁPIDA
    // ========================================================
    case 'login-google-rapido':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['credential'])) {
            $jwt = $_POST['credential'];
            $partes = explode('.', $jwt);
            
            if (count($partes) === 3) {
                $dadosUsuario = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $partes[1])), true);
                
                $email = $dadosUsuario['email'] ?? '';
                $nomeCompleto = $dadosUsuario['name'] ?? '';
                
                if (!empty($email)) {
                    $nomeArray = explode(' ', trim($nomeCompleto));
                    $primeiroNome = $nomeArray[0] ?? 'Usuário';

                    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
                    $stmt->execute([$email]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($user) {
                        $_SESSION['usuario_id'] = $user['id'];
                        $_SESSION['usuario_nome'] = $user['primeiro_nome'];
                        $_SESSION['tipo_id'] = $user['tipo_id']; 
                    } else {
                        $senhaHashProvisoria = password_hash(uniqid(), PASSWORD_DEFAULT);
                        $tipoPadrao = 2; 

                        $sqlInsert = "INSERT INTO usuarios (primeiro_nome, email, senha, tipo_id) VALUES (?, ?, ?, ?)";
                        $stmtInsert = $pdo->prepare($sqlInsert);
                        $stmtInsert->execute([$primeiroNome, $email, $senhaHashProvisoria, $tipoPadrao]);

                        $_SESSION['usuario_id'] = $pdo->lastInsertId();
                        $_SESSION['usuario_nome'] = $primeiroNome;
                        $_SESSION['tipo_id'] = $tipoPadrao;
                    }

                    header("Location: ?rota=manutencao");
                    exit();
                }
            }
        }
        header("Location: ?rota=login&erro=google_failed");
        exit();
        break;

    case 'cadastrar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome  = trim($_POST['nome'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $senha = trim($_POST['senha'] ?? '');
            $tipo  = $_POST['tipo_id'] ?? 2;

            if (!empty($nome) && !empty($email) && !empty($senha)) {
                try {
                    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                    $sql = "INSERT INTO usuarios (primeiro_nome, email, senha, tipo_id) VALUES (?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$nome, $email, $senhaHash, $tipo]);
                    header("Location: ?rota=login&cadastro=sucesso");
                    exit();
                } catch (PDOException $e) {
                    $msg = ($e->getCode() == 23000) ? 'E-mail já cadastrado!' : 'Erro ao cadastrar.';
                    include $baseDir . '/src/Views/login.php';
                    echo "<script>window.onload = () => window.toast('$msg', 'error');</script>";
                    exit();
                }
            }
        }
        break;

    case 'recuperar-senha':
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['passo'])) {
            unset($_SESSION['recuperar_passo'], $_SESSION['recuperar_codigo'], $_SESSION['recuperar_email']);
        }
        include $baseDir . '/src/Views/recuperar.php';
        break;

    case 'processar-recuperar':
        $acao = $_GET['acao'] ?? '';
        
        if ($acao === 'gerar-codigo' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                $_SESSION['recuperar_codigo'] = 'TRK-' . rand(1000, 9999);
                $_SESSION['recuperar_email'] = $email;
                $_SESSION['recuperar_passo'] = 2;
                
                header("Location: ?rota=recuperar-senha&passo=2");
                exit();
            } else {
                include $baseDir . '/src/Views/recuperar.php';
                echo "<script>window.onload = () => window.toast('E-mail não encontrado no sistema.', 'error');</script>";
                exit();
            }
        }
        
        if ($acao === 'alterar-senha' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $codigoDigitado = trim($_POST['codigo_confirmacao'] ?? '');
            $novaSenha = trim($_POST['nova_senha'] ?? '');
            $emailSessao = $_SESSION['recuperar_email'] ?? '';
            $codigoSessao = $_SESSION['recuperar_codigo'] ?? '';
            
            if ($codigoDigitado === $codigoSessao && !empty($emailSessao) && !empty($novaSenha)) {
                $novaSenhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
                
                $stmtUpdate = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE email = ?");
                $stmtUpdate->execute([$novaSenhaHash, $emailSessao]);
                
                unset($_SESSION['recuperar_passo'], $_SESSION['recuperar_codigo'], $_SESSION['recuperar_email']);
                
                header("Location: ?rota=login&cadastro=sucesso"); 
                exit();
            } else {
                $_SESSION['recuperar_passo'] = 2; 
                include $baseDir . '/src/Views/recuperar.php';
                echo "<script>window.onload = () => window.toast('Código de confirmação incorreto!', 'error');</script>";
                exit();
            }
        }
        break;

    case 'logar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $senha = trim($_POST['senha'] ?? '');
            try {
                $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user && password_verify($senha, $user['senha'])) {
                    $_SESSION['usuario_id'] = $user['id'];
                    $_SESSION['usuario_nome'] = $user['primeiro_nome'];
                    $_SESSION['tipo_id'] = $user['tipo_id']; 
                    header("Location: ?rota=manutencao");
                    exit();
                } else {
                    include $baseDir . '/src/Views/login.php';
                    echo "<script>window.onload = () => window.toast('E-mail ou senha incorretos!', 'error');</script>";
                    exit();
                }
            } catch (PDOException $e) { die("Erro: " . $e->getMessage()); }
        }
        break;
       
    case 'checkout':
        verificarAcesso();
        $plano = $_GET['plano'] ?? 'prata';
        include $baseDir . '/src/Views/checkout.php';
        break;

    case 'profile':
        verificarAcesso();
        include $baseDir . '/src/Views/profile.php';
        break;

    case 'search':
        verificarAcesso();
        include $baseDir . '/src/Views/search.php';
        break;

    case 'rastreio':
        verificarAcesso();
        include $baseDir . '/src/Views/rastreio.php';
        break;

    case 'tool-manager':
        verificarAcesso();
        include $baseDir . '/src/Views/tool-manager.php';
        break;
        
    case 'processar-ferramenta':
        verificarAcesso();
        require_once $baseDir . '/src/Views/processar_ferramenta.php';
        break;
        
    case 'ajuda':
        include $baseDir . '/src/Views/help.php';
        break;

    case 'sobre':
        include $baseDir . '/src/Views/sobre.php';
        break;

    case 'politica':
        include $baseDir . '/src/Views/politica.php';
        break;

    case 'desenvolvedores':
        include $baseDir . '/src/Views/desenvolvedores.php';
        break;

    case 'config':
        verificarAcesso();
        include $baseDir . '/src/Views/config.php';
        break;

   case 'salvar-manutencao':
        verificarAcesso();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_editar = $_POST['id_editar'] ?? '';
            $item = $_POST['item_do_inventario'] ?? '';
            $data_m = $_POST['data_manutencao'] ?? '';
            $prox_m = !empty($_POST['proxima_manutencao']) ? $_POST['proxima_manutencao'] : null;
            $descricao = $_POST['descricao_servico'] ?? '';

            try {
                if (!empty($id_editar)) {
                    $stmt = $pdo->prepare("UPDATE historico_manutencao SET item_do_inventario = ?, data_manutencao = ?, proxima_manutencao = ?, descricao_servico = ? WHERE id = ?");
                    $stmt->execute([$item, $data_m, $prox_m, $descricao, $id_editar]);
                    
                    // 🔔 NOTIFICAÇÃO REAL: Edição
                    $msgNotif = "🛠️ Manutenção alterada para o Item #{$item} ({$descricao}).";
                    $stmtNotif = $pdo->prepare("INSERT INTO notificacoes (usuario_id, mensagem) VALUES (?, ?)");
                    $stmtNotif->execute([$_SESSION['usuario_id'], $msgNotif]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO historico_manutencao (item_do_inventario, data_manutencao, proxima_manutencao, descricao_servico) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$item, $data_m, $prox_m, $descricao]);
                    
                    // 🔔 NOTIFICAÇÃO REAL: Nova Manutenção
                    $dataFmt = date('d/m/Y', strtotime($data_m));
                    $msgNotif = "🛠️ Nova manutenção agendada para o Item #{$item} — Serviço: {$descricao} para o dia {$dataFmt}.";
                    $stmtNotif = $pdo->prepare("INSERT INTO notificacoes (usuario_id, mensagem) VALUES (?, ?)");
                    $stmtNotif->execute([$_SESSION['usuario_id'], $msgNotif]);
                }
            } catch (Exception $e) { }
        }
        header("Location: ?rota=manutencao");
        exit();
        break;

    case 'remover-manutencao':
        verificarAcesso();
        $id_remover = $_GET['id'] ?? '';
        if (!empty($id_remover)) {
            try {
                $stmtFind = $pdo->prepare("SELECT item_do_inventario, descricao_servico FROM historico_manutencao WHERE id = ?");
                $stmtFind->execute([$id_remover]);
                $mOld = $stmtFind->fetch(PDO::FETCH_ASSOC);

                $stmt = $pdo->prepare("DELETE FROM historico_manutencao WHERE id = ?");
                $stmt->execute([$id_remover]);

                if ($mOld) {
                    // 🔔 NOTIFICAÇÃO REAL: Remoção
                    $msgNotif = "❌ A manutenção do Item #{$mOld['item_do_inventario']} ({$mOld['descricao_servico']}) foi cancelada.";
                    $stmtNotif = $pdo->prepare("INSERT INTO notificacoes (usuario_id, mensagem) VALUES (?, ?)");
                    $stmtNotif->execute([$_SESSION['usuario_id'], $msgNotif]);
                }
            } catch (Exception $e) { }
        }
        header("Location: ?rota=manutencao");
        exit();
        break;
        
    case 'manutencao':
        verificarAcesso();
        try {
            $stmt_m = $pdo->query("SELECT * FROM historico_manutencao ORDER BY data_manutencao DESC");
            $manutencoes = $stmt_m->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { $manutencoes = []; }
        include $baseDir . '/src/Views/manutencao.php';
        break;

    case 'empresas':
        verificarAcesso();
        include $baseDir . '/src/Views/empresas.php';
        break;

    case 'processar-empresa':
        verificarAcesso();
        $acao = $_GET['acao'] ?? '';
        if ($acao === 'cadastrar_completo' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmtCheck = $pdo->prepare("SELECT empresa_id FROM usuarios WHERE id = ?");
            $stmtCheck->execute([$_SESSION['usuario_id']]);
            $u = $stmtCheck->fetch();
            if (!empty($u['empresa_id'])) {
                include $baseDir . '/src/Views/profile.php';
                echo "<script>window.onload = () => window.toast('Você já está vinculado a uma unidade!', 'warn');</script>";
                exit();
            }
            try {
                $pdo->beginTransaction();
                $stmtEnd = $pdo->prepare("INSERT INTO endereco (cep, estado, cidade, bairro, logradouro, numero) VALUES (?, ?, ?, ?, ?, ?)");
                $stmtEnd->execute([$_POST['cep'], $_POST['estado'], $_POST['cidade'], $_POST['bairro'], $_POST['logradouro'], $_POST['numero']]);
                $endereco_id = $pdo->lastInsertId();

                $stmtEmp = $pdo->prepare("INSERT INTO empresas (razao_social, endereco_id) VALUES (?, ?)");
                $stmtEmp->execute([$_POST['razao_social'], $endereco_id]);
                $empresa_id = $pdo->lastInsertId();

                $pdo->prepare("UPDATE usuarios SET empresa_id = ? WHERE id = ?")->execute([$empresa_id, $_SESSION['usuario_id']]);
                $pdo->commit();

                // 🔔 NOTIFICAÇÃO REAL: Unidade/Empresa Criada e Vinculada
                $msgNotif = "Sua conta foi vinculada com sucesso à empresa " . htmlspecialchars($_POST['razao_social']) . ".";
                $stmtNotif = $pdo->prepare("INSERT INTO notificacoes (usuario_id, mensagem, tipo) VALUES (?, ?, 'sistema')");
                $stmtNotif->execute([$_SESSION['usuario_id'], $msgNotif]);

                header("Location: ?rota=profile&msg=empresa_vinculada");
                exit();
            } catch (Exception $e) {
                $pdo->rollBack();
                include $baseDir . '/src/Views/empresas.php';
                echo "<script>window.onload = () => window.toast('Erro ao cadastrar.', 'error');</script>";
                exit();
            }
        }
        break;

    case 'excluir-conta':
        verificarAcesso();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuarioId = $_SESSION['usuario_id'];
            
            try {
                $pdo->beginTransaction();
                
                // 1. Remove as notificações atreladas ao usuário (Evita falha de integridade restritiva)
                $stmtNotif = $pdo->prepare("DELETE FROM notificacoes WHERE usuario_id = ?");
                $stmtNotif->execute([$usuarioId]);
                
                // 2. Remove assinaturas atreladas a este ID de usuário ou empresa caso existam
                $stmtAssin = $pdo->prepare("DELETE FROM assinaturas WHERE id = ? OR empresa_id = ?");
                $stmtAssin->execute([$usuarioId, $usuarioId]);

                // 3. Remove os vínculos de empresa na tabela usuários antes de apagar o usuário definitivo
                $stmtUserUpdate = $pdo->prepare("UPDATE usuarios SET empresa_id = NULL WHERE id = ?");
                $stmtUserUpdate->execute([$usuarioId]);

                // 4. Apaga definitivamente o registro do usuário
                $stmtUserDelete = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
                $stmtUserDelete->execute([$usuarioId]);
                
                $pdo->commit();
                
                // Finaliza a sessão completamente
                session_destroy();
                
                // Redireciona com flag de sucesso
                header("Location: ?rota=login&excluido=sucesso");
                exit();
                
            } catch (Exception $e) {
                $pdo->rollBack();
                die("Erro crítico ao realizar a exclusão total dos dados do banco: " . $e->getMessage());
            }
        }
        
        header("Location: ?rota=profile");
        exit();
        break;

    case 'logout':
        session_destroy();
        header("Location: ?rota=login");
        exit();

    default:
        include $baseDir . '/src/Views/login.php';
        break;
}
