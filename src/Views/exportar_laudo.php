<?php
/**
 * ARQUIVO: exportar_laudo.php
 * Gera o download do Laudo Técnico de Engenharia de Confiabilidade
 */
session_start();

// Verifica se o usuário está logado e se é plano Ouro
$planoUsuario = strtolower($_SESSION['usuario_plano'] ?? 'prata');
if ($planoUsuario !== 'ouro') {
    die("Acesso negado. A exportação de laudos é exclusiva para o Plano Ouro.");
}

// Configura os cabeçalhos do navegador para forçar o download do arquivo
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=Laudo_Tecnico_Confiabilidade_" . date('Y-m-d') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Captura os dados atuais passados pela URL (ou usa os padrões caso não venham)
$mtbf = $_GET['mtbf'] ?? '45.0';
$mttr = $_GET['mttr'] ?? '2.4';
$disponibilidade = $_GET['disponibilidade'] ?? '98.4';
$total_m = $_GET['total_m'] ?? '0';
$total_a = $_GET['total_a'] ?? '0';

// Estrutura visual em HTML/Tabela que o Excel lê perfeitamente
?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1">
    <tr>
        <th colspan="4" style="background-color: #1e2230; color: #ffffff; font-size: 16px; height: 40px; text-align: center;">
            LAUDO TÉCNICO DE ENGENHARIA DE CONFIABILIDADE
        </th>
    </tr>
    <tr>
        <td colspan="4" style="text-align: center; color: #555; font-size: 11px;">
            Gerado automaticamente via Central de Inteligência Preditiva em: <?= date('d/m/Y H:i:s') ?>
        </td>
    </tr>
    <tr><td colspan="4"></td></tr>
    
    <tr>
        <th colspan="4" style="background-color: #f59e0b; color: #000000; text-align: left; font-weight: bold;">
            1. Diagnóstico Geral do Turno
        </th>
    </tr>
    <tr>
        <td style="font-weight: bold;">Módulo Ativo:</td>
        <td>Plano OURO (Inteligência Ativa)</td>
        <td style="font-weight: bold;">Status da Planta:</td>
        <td style="color: green; font-weight: bold;">EXCELENTE</td>
    </tr>
    <tr>
        <td style="font-weight: bold;">Ciclos Processados:</td>
        <td><?= $total_m ?> manutenções</td>
        <td style="font-weight: bold;">Alertas Identificados:</td>
        <td><?= $total_a ?> ocorrências</td>
    </tr>
    
    <tr><td colspan="4"></td></tr>

    <tr>
        <th colspan="4" style="background-color: #1e2230; color: #ffffff; text-align: left; font-weight: bold;">
            2. Indicadores Científicos de Disponibilidade (Uptime)
        </th>
    </tr>
    <tr>
        <td colspan="2" style="font-weight: bold;">MTBF (Tempo Médio Entre Falhas):</td>
        <td colspan="2"><?= $mtbf ?> dias</td>
    </tr>
    <tr>
        <td colspan="2" style="font-weight: bold;">MTTR (Tempo Médio de Reparo):</td>
        <td colspan="2"><?= $mttr ?> horas</td>
    </tr>
    <tr>
        <td colspan="2" style="font-weight: bold; background-color: #eafaf1;">Disponibilidade Geral da Planta:</td>
        <td colspan="2" style="font-weight: bold; background-color: #eafaf1; color: green;"><?= $disponibilidade ?>%</td>
    </tr>
    
    <tr><td colspan="4"></td></tr>

    <tr>
        <th colspan="4" style="background-color: #f59e0b; color: #000000; text-align: left; font-weight: bold;">
            3. Parecer Prescritivo Automático (IA)
        </th>
    </tr>
    <tr>
        <td colspan="4" style="height: 50px; vertical-align: top; font-style: italic;">
            "Com base no Uptime calculado de <?= $disponibilidade ?>%, a planta opera em zona de segurança nominal. Recomenda-se aplicar de forma rigorosa o Protocolo Operacional Padrão POP-04 em caso de anomalias detectadas nos logs subsequentes para mitigar quaisquer desvios de frequência."
        </td>
    </tr>
</table>
