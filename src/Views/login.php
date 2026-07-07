<?php
// ATIVAÇÃO DE DIAGNÓSTICO (Caso o PHP esteja silenciando erros críticos)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Variáveis para controle do modal e tempo restante
$mostrar_modal_bloqueio = false;
$segundos_restantes = 0;

// PARTE 1: Lógica de Processamento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['rota'])) {
    $rota = $_GET['rota'];

    // LÓGICA DE LOGIN COM RATE LIMITING
    if ($rota === 'logar') {
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';

        if (!empty($email) && !empty($senha)) {
            try {
                if (!isset($pdo) && isset($GLOBALS['pdo'])) {
                    $pdo = $GLOBALS['pdo'];
                }

                if (!isset($pdo)) {
                    throw new Exception("A variável de conexão com o banco de dados (\$pdo) não foi encontrada.");
                }

                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Garante a existência da tabela de tentativas
                $pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    email VARCHAR(255) NOT NULL,
                    ip_address VARCHAR(45) NOT NULL,
                    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

                $limite_tentativas = 5;
                $tempo_bloqueio_minutos = 15;
                $ip_usuario = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

                // 1. Verificar tentativas falhas recentes
                $sql_check = "SELECT COUNT(*), MAX(attempted_at) as ultimo_erro FROM login_attempts 
                              WHERE email = ? 
                              AND attempted_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)";
                
                $stmt_check = $pdo->prepare($sql_check);
                $stmt_check->execute([$email, $tempo_bloqueio_minutos]);
                $resultado_check = $stmt_check->fetch(PDO::FETCH_ASSOC);
                $tentativas_falhas = (int)($resultado_check['COUNT(*)'] ?? 0);

                if ($tentativas_falhas >= $limite_tentativas) {
                    $mostrar_modal_bloqueio = true;
                    
                    // Calcula exatamente quantos segundos restam com base no carimbo do último erro
                    $ultimo_erro_timestamp = strtotime($resultado_check['ultimo_erro']);
                    $momento_liberacao = $ultimo_erro_timestamp + ($tempo_bloqueio_minutos * 60);
                    $segundos_restantes = $momento_liberacao - time();
                    
                    if ($segundos_restantes < 0) $segundos_restantes = 0;
                } else {
                    // 2. Localiza o usuário
                    $stmt_user = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? LIMIT 1");
                    $stmt_user->execute([$email]);
                    $usuario = $stmt_user->fetch(PDO::FETCH_ASSOC);

                    if ($usuario && ($senha === $usuario['senha'])) {
                        // LOGIN CORRETO: Limpa erros
                        $stmt_clear = $pdo->prepare("DELETE FROM login_attempts WHERE email = ?");
                        $stmt_clear->execute([$email]);

                        if (session_status() === PHP_SESSION_NONE) {
                            session_start();
                        }

                        $_SESSION['usuario_id'] = $usuario['id'];
                        $_SESSION['usuario_nome'] = $usuario['primeiro_nome'] ?? $usuario['nome'] ?? 'Usuário';

                        echo "<script>
                            document.addEventListener('DOMContentLoaded', function() {
                                if(typeof window.toast === 'function') window.toast('Login realizado com sucesso!', 'success');
                                setTimeout(function(){ window.location.href = '?rota=search'; }, 1500);
                            });
                        </script>";
                    } else {
                        // LOGIN INCORRETO: Registra falha
                        $stmt_fail = $pdo->prepare("INSERT INTO login_attempts (email, ip_address) VALUES (?, ?)");
                        $stmt_fail->execute([$email, $ip_usuario]);

                        $restantes = $limite_tentativas - ($tentativas_falhas + 1);

                        if ($restantes <= 0) {
                            $mostrar_modal_bloqueio = true;
                            $segundos_restantes = $tempo_bloqueio_minutos * 60; // 15 minutos cheios no primeiro bloqueio
                        } else {
                            echo "<script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    if(typeof window.toast === 'function') {
                                        window.toast('Credenciais incorretas! Restam {$restantes} tentativas.', 'error');
                                    }
                                });
                            </script>";
                        }
                    }
                }
            } catch (Exception $e) {
                $erro_detalhado = addslashes($e->getMessage());
                echo "<div style='background: #ff4d4d; color: white; padding: 15px; margin: 10px; border-radius: 5px; font-family: monospace; z-index: 10000; position: relative;'>";
                echo "<strong>[Erro de Execução TrackFix]:</strong> " . htmlspecialchars($erro_detalhado);
                echo "</div>";
            }
        }
    }

    // LÓGICA DE CADASTRO
    if ($rota === 'cadastrar') {
        $nome  = $_POST['nome'] ?? '';
        $email = $_POST['email'] ?? '';
        $cargo = $_POST['tipo_id'] ?? '';
        $senha = $_POST['senha'] ?? '';

        if (!empty($nome) && !empty($email) && !empty($senha)) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    if(typeof window.toast === 'function') window.toast('Conta criada com sucesso! Redirecionando...', 'success');
                    setTimeout(function(){ window.location.href = '?rota=search'; }, 2000);
                });
            </script>";
        }
    }
}

include 'header.php';

// Toast de exclusão de conta bem-sucedida
if (isset($_GET['excluido']) && $_GET['excluido'] === 'sucesso') {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            if(typeof window.toast === 'function') window.toast('Sua conta foi excluída com sucesso!', 'success');
        });
    </script>";
}
?>

<script src="https://accounts.google.com/gsi/client" async defer></script>

<main class="layout">
    <div class="login-cadastro-container">
        
        <div class="card-auth">
            <h3>Login</h3>
            <p>Acesse com e-mail/senha ou utilize o login social.</p>
            
            <form action="?rota=logar" method="POST">
                <div class="campo">
                    <label>E-mail</label>
                    <input type="email" name="email" placeholder="voce@exemplo.com" value="<?= htmlspecialchars($email ?? '') ?>" required>
                </div>
                
                <div class="campo">
                    <label>Senha</label>
                    <input type="password" name="senha" placeholder="********" required>
                </div>
                
                <div class="opcoes-extras">
                    <div class="check-group">
                        <input type="checkbox" id="manter" name="manter">
                        <label for="manter">Manter conectado</label>
                    </div>
                    <a href="?rota=recuperar-senha" class="link-secundario">Esqueci a senha</a>
                </div>

                <div class="opcoes-2fa">
                    <div class="check-group">
                        <input type="checkbox" id="ativar2fa" name="2fa">
                        <label for="ativar2fa">Ativar 2FA</label>
                    </div>
                    <select name="meio_2fa" class="select-track">
                        <option value="email">E-mail</option>
                        <option value="sms">SMS</option>
                    </select>
                </div>

                <button type="submit" class="btn-principal">Entrar</button>
            </form>
        </div>

        <div class="card-auth">
            <div class="social-login-area">
                <h3>Entrar com</h3>
                <div id="g_id_onload"
                     data-client_id="<?php echo defined('GOOGLE_CLIENT_ID') ? GOOGLE_CLIENT_ID : ''; ?>"
                     data-login_uri="https://marileal.page.gd//?rota=login-google-rapido"
                     data-auto_prompt="false">
                </div>
                <div class="g_id_signin" data-type="standard" data-size="large" data-theme="outline" data-text="sign_in_with" data-shape="rounded" data-logo_alignment="left" data-width="100%"></div>
            </div>

            <hr class="divisor">

            <h3>Criar conta</h3>
            <form action="?rota=cadastrar" method="POST">
                <div class="grid-cadastro">
                    <div class="campo">
                        <label>Nome</label>
                        <input type="text" name="nome" placeholder="Seu nome completo" required>
                    </div>
                    
                    <div class="campo">
                        <label>E-mail</label>
                        <input type="email" name="email" placeholder="voce@exemplo.com" required>
                    </div>

                    <div class="campo">
                        <label>Cargo</label>
                        <select name="tipo_id" class="select-track" required>
                            <option value="2">Usuário</option>
                            <option value="3">Gerente</option>
                            <option value="1">Administrador</option>
                        </select>
                    </div>

                    <div class="campo">
                        <label>Senha</label>
                        <input type="password" name="senha" placeholder="********" required>
                    </div>
                </div>
                <button type="submit" class="btn-secundario">Cadastrar</button>
            </form>
        </div>

    </div>
</main>

<div id="modalBloqueioRate" style="display: <?= $mostrar_modal_bloqueio ? 'flex' : 'none' ?>; position: fixed; z-index: 99999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.85); backdrop-filter: blur(6px); align-items: center; justify-content: center;">
    <div style="background: #18191a; border: 1px solid #ff4d4d; padding: 40px 30px; border-radius: 12px; max-width: 440px; width: 90%; text-align: center; box-shadow: 0 20px 50px rgba(0,0,0,0.7);">
        <div style="font-size: 3.5rem; margin-bottom: 15px; color: #ff4d4d;">🔒</div>
        <h3 style="color: #fff; margin-bottom: 12px; font-size: 1.5rem; font-weight: bold;">Acesso Bloqueado</h3>
        <p style="color: #b0b3b8; font-size: 0.95rem; line-height: 1.6; margin-bottom: 25px;">
            Detectamos múltiplas tentativas incorretas de login. Para garantir a integridade do sistema, suspendemos novas tentativas para este usuário.
        </p>
        
        <div style="background: rgba(255, 77, 77, 0.08); border: 1px solid rgba(255, 77, 77, 0.2); padding: 15px; border-radius: 6px; margin-bottom: 25px;">
            <span style="color: #ff4d4d; font-weight: bold; font-size: 0.9rem; display: block; margin-bottom: 6px;">Tempo restante de bloqueio:</span>
            <span id="cronometroBloqueio" style="color: #fff; font-size: 1.5rem; font-family: 'Courier New', Courier, monospace; font-weight: bold; letter-spacing: 2px;">15:00</span>
        </div>

        <button type="button" onclick="document.getElementById('modalBloqueioRate').style.display='none';" style="background: #3a3b3c; color: #e4e6eb; border: none; padding: 12px 30px; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 0.95rem; width: 100%; transition: background 0.2s;" onmouseover="this.style.background='#4e4f50'" onmouseout="this.style.background='#3a3b3c'">
            Fechar Aviso
        </button>
    </div>
</div>

<script>
// Inicializa os segundos restantes injetados de forma dinâmica pelo PHP
let tempoRestanteSegundos = <?= (int)$segundos_restantes ?>;

function atualizarCronometro() {
    if (tempoRestanteSegundos <= 0) {
        document.getElementById('cronometroBloqueio').innerText = "Liberado!";
        document.getElementById('cronometroBloqueio').style.color = "#4edf7d";
        return;
    }

    // Formata os segundos em MM:SS
    let minutos = Math.floor(tempoRestanteSegundos / 60);
    let segundos = tempoRestanteSegundos % 60;

    minutos = minutos < 10 ? '0' + minutos : minutos;
    segundos = segundos < 10 ? '0' + segundos : segundos;

    document.getElementById('cronometroBloqueio').innerText = minutos + ':' + segundos;
    
    // Decrementa o tempo e agenda o próximo segundo
    tempoRestanteSegundos--;
    setTimeout(atualizarCronometro, 1000);
}

// Inicia o cronômetro assim que a página renderizar o aviso aberto
if (tempoRestanteSegundos > 0) {
    atualizarCronometro();
}
</script>

<?php include 'footer.php'; ?>
