<?php 
include $baseDir . '/src/Views/header.php'; 

// ========================================================
// CONFIGURAÇÃO DA SUA CONTA REAL (PREENCHA AQUI)
// ========================================================
$chavePix   = "marialaura123fernandes@gmail.com"; // Insira aqui seu E-mail, CPF, Celular ou Chave Aleatória
$nomeTitular = "Maria Laura Leal Fernandes "; // Máximo 25 caracteres (Sem acentos, ex: JOAO SILVA)
$cidadeBanco = "Uberaba";      // Máximo 15 caracteres (Sem acentos, ex: SAO PAULO)

// Identifica o plano e define o preço para mostrar na tela
$planoSelecionado = $_GET['plano'] ?? 'prata';
$nomePlano = ($planoSelecionado === 'ouro') ? 'Ouro 👑' : 'Prata 🌟';
$precoPlano = ($planoSelecionado === 'ouro') ? '99,90' : '49.90';
$valorFormatado = ($planoSelecionado === 'ouro') ? '99.90' : '49.90';

// ========================================================
// GERADOR NATIVO DO PADRÃO PIX (BR CODE BRASIL)
// ========================================================
if (!function_exists('montarBlocoPix')) {
    function montarBlocoPix($id, $valor) {
        return str_pad($id, 2, "0", STR_PAD_LEFT) . str_pad(strlen($valor), 2, "0", STR_PAD_LEFT) . $valor;
    }
}

$merchantAccount = montarBlocoPix(00, "br.gov.bcb.pix") . montarBlocoPix(01, $chavePix);

$dadosPix = "000201" 
          . montarBlocoPix(26, $merchantAccount) 
          . "52040000" 
          . "5303986" 
          . montarBlocoPix(54, $valorFormatado) 
          . "5802BR" 
          . montarBlocoPix(59, substr($nomeTitular, 0, 25)) 
          . montarBlocoPix(60, substr($cidadeBanco, 0, 15)) 
          . "62070503***";

// Cálculo do Checksum CRC16 (Regra de validação dos aplicativos bancários)
$dadosPix .= "6304";
$crc = 0xFFFF;
for ($c = 0; $c < strlen($dadosPix); $c++) {
    $crc ^= (ord($dadosPix[$c]) << 8);
    for ($i = 0; $i < 8; $i++) {
        if ($crc & 0x8000) { $crc = ($crc << 1) ^ 0x1021; } 
        else { $crc <<= 1; }
    }
}
$crc16 = strtoupper(str_pad(dechex($crc & 0xFFFF), 4, "0", STR_PAD_LEFT));
$pixCopiaColaReal = $dadosPix . $crc16;

// Linha digitável estruturada para o boleto (Simulação)
$linhaDigitavel = "34191.79001 01043.513184 91020.150008 7 983200000" . str_replace(['.', ','], '', $precoPlano);
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<main class="layout">
    <div class="content" style="padding: 40px 20px; max-width: 1000px; margin: 0 auto;">
        
        <div style="margin-bottom: 30px;">
            <a href="?rota=assinatura" style="color: var(--primary); text-decoration: none; font-size: 0.9rem;">← Voltar para planos</a>
            <h2 style="font-size: 2rem; color: var(--text); margin-top: 10px;">💳 Tela de Pagamento</h2>
        </div>

        <div class="grid cols-2" style="align-items: start; gap: 30px;">
            
            <div class="card" style="padding: 30px !important;">
                <h3 style="margin-bottom: 20px; font-size: 1.3rem;">Escolha como pagar:</h3>
                
                <div class="abas-pagamento" style="display: flex; gap: 10px; margin-bottom: 25px;">
                    <button type="button" class="btn-aba active" onclick="mudarMetodo('pix')">⚡ Pix Real</button>
                    <button type="button" class="btn-aba" onclick="mudarMetodo('cartao')">💳 Cartão</button>
                    <button type="button" class="btn-aba" onclick="mudarMetodo('boleto')">📄 Boleto</button>
                </div>

                <div id="metodo-pix" class="conteudo-metodo active" style="text-align: center; padding: 10px 0;">
                    <p style="color: var(--muted); font-size: 0.95rem; margin-bottom: 20px;">Abra o app do seu banco e escaneie o código. O dinheiro irá direto para a conta configurada!</p>
                    
                    <div style="background: white; padding: 15px; display: inline-block; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                        <div id="qrcode"></div>
                    </div>

                    <div style="margin-bottom: 20px; text-align: left;">
                        <label style="font-size: 0.85rem; color: var(--muted); display: block; margin-bottom: 5px;">Pix Copia e Cola Real:</label>
                        <div style="display: flex; gap: 10px;">
                            <input type="text" id="pixCode" value="<?php echo $pixCopiaColaReal; ?>" readonly style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: var(--text); padding: 10px; border-radius: 6px; flex: 1; font-size: 0.85rem;">
                            <button type="button" class="btn" style="padding: 10px 15px;" onclick="copiarTexto('pixCode')">📋</button>
                        </div>
                    </div>

                    <button type="button" class="btn btn-ghost w-100" onclick="simularConfirmacao()">🔄 Já paguei, liberar meu acesso</button>
                </div>

                <div id="metodo-cartao" class="conteudo-metodo" style="display: none;">
                    <form onsubmit="event.preventDefault(); simularConfirmacaoCartao();">
                        <div class="campo" style="margin-bottom: 15px;">
                            <label>Número do Cartão</label>
                            <input type="text" placeholder="0000 0000 0000 0000" required style="width:100%; box-sizing:border-box;">
                        </div>
                        <div class="grid cols-2" style="gap: 15px; margin-bottom: 15px;">
                            <div class="campo"><label>Validade</label><input type="text" placeholder="MM/AA" required style="width:100%; box-sizing:border-box;"></div>
                            <div class="campo"><label>CVC</label><input type="text" placeholder="123" required style="width:100%; box-sizing:border-box;"></div>
                        </div>
                        <div class="campo" style="margin-bottom: 20px;">
                            <label>Nome no Cartão</label>
                            <input type="text" placeholder="Como está impresso" required style="width:100%; box-sizing:border-box;">
                        </div>
                        <button type="submit" class="btn w-100">Pagar R$ <?php echo $precoPlano; ?></button>
                    </form>
                </div>

                <div id="metodo-boleto" class="conteudo-metodo" style="display: none;">
                    <p style="color: var(--muted); font-size: 0.95rem; text-align: center; margin-bottom: 20px;">O boleto bancário leva até 3 dias úteis para compensar.</p>
                    <div style="margin-bottom: 25px;">
                        <label style="font-size: 0.85rem; color: var(--muted); display: block; margin-bottom: 5px;">Linha Digitável:</label>
                        <div style="display: flex; gap: 10px;">
                            <input type="text" id="boletoCode" value="<?php echo $linhaDigitavel; ?>" readonly style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: var(--text); padding: 10px; border-radius: 6px; flex: 1; font-size: 0.85rem; font-family: monospace;">
                            <button type="button" class="btn" style="padding: 10px 15px;" onclick="copiarTexto('boletoCode')">📋</button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-ghost w-100" onclick="window.print()">🖨️ Imprimir Boleto Bancário</button>
                </div>

            </div>

            <div class="card" style="padding: 25px !important; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                <h3 style="font-size: 1.2rem; margin-bottom: 15px;">Resumo do plano</h3>
                <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                    <span style="color: var(--muted);">Plano Selecionado:</span>
                    <strong style="color: var(--text);"><?php echo $nomePlano; ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                    <span style="color: var(--muted);">Ciclo de Cobrança:</span>
                    <span style="color: var(--text);">Mensal</span>
                </div>
                <hr style="opacity: 0.1; margin: 15px 0;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 600;">Total a pagar:</span>
                    <span style="font-size: 1.6rem; font-weight: 800; color: var(--accent);">R$ <?php echo $precoPlano; ?></span>
                </div>
            </div>

        </div>
    </div>
</main>

<script>
document.addEventListener("DOMContentLoaded", function() {
    new QRCode(document.getElementById("qrcode"), {
        text: document.getElementById("pixCode").value,
        width: 180,
        height: 180,
        colorDark : "#000000",
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.M
    });
});

function mudarMetodo(metodo) {
    document.querySelectorAll('.conteudo-metodo').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.btn-aba').forEach(el => el.classList.remove('active'));
    if (metodo === 'pix') document.getElementById('metodo-pix').style.display = 'block';
    if (metodo === 'cartao') document.getElementById('metodo-cartao').style.display = 'block';
    if (metodo === 'boleto') document.getElementById('metodo-boleto').style.display = 'block';
    event.currentTarget.classList.add('active');
}

function copiarTexto(idElemento) {
    var copyText = document.getElementById(idElemento);
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(copyText.value);
    if(window.toast) window.toast('Código copiado! 📋', 'success');
}

// Redireciona para o PHP persistir a mudança no banco de dados
function simularConfirmacao() {
    if(window.toast) window.toast('Verificando recebimento do Pix...', 'info');
    setTimeout(() => {
        if(window.toast) window.toast('Pagamento aprovado! Atualizando seu plano... 🚀', 'success');
        setTimeout(() => { 
            window.location.href = '?rota=atualizar-plano&plano=<?php echo $planoSelecionado; ?>'; 
        }, 1500);
    }, 1500);
}

// Redireciona para o PHP persistir a mudança no banco de dados
function simularConfirmacaoCartao() {
    if(window.toast) window.toast('Processando cartão...', 'info');
    setTimeout(() => {
        if(window.toast) window.toast('Cartão Autorizado! Salvando assinatura... 🚀', 'success');
        setTimeout(() => { 
            window.location.href = '?rota=atualizar-plano&plano=<?php echo $planoSelecionado; ?>'; 
        }, 1500);
    }, 1500);
}
</script>

<style>
.btn-aba { flex: 1; padding: 10px; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); color: var(--muted); border-radius: 6px; cursor: pointer; font-weight: 600; transition: all var(--trans); }
.btn-aba:hover { background: rgba(255, 255, 255, 0.07); color: var(--text); }
.btn-aba.active { background: var(--primary) !important; border-color: var(--primary) !important; color: #fff !important; }
.w-100 { width: 100%; box-sizing: border-box; }
</style>

<?php 
include $baseDir . '/src/Views/footer.php'; 
?>
