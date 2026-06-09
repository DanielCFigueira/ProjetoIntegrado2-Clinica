<?php
require_once 'config/session.php';
require_once 'config/seguranca.php';
require_once 'config/database.php';
require_once 'views/header.php';
require_once 'config/controle_acesso.php';

if (!is_admin()) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

// Filtro de data: O período vem preenchido por padrão com o mês atual
$data_inicio = isset($_GET['inicio']) ? $_GET['inicio'] : date('Y-m-01');
$data_fim = isset($_GET['fim']) ? $_GET['fim'] : date('Y-m-t');

try {
    // Buscar total de procedimentos executados no período para calcular a porcentagem
    $stmtTotal = $pdo->prepare("
        SELECT SUM(ap.quantidade) 
        FROM atendimento_procedimentos ap
        JOIN atendimentos a ON ap.id_atendimento = a.id
        WHERE a.data_atendimento BETWEEN ? AND ? 
        AND ap.status_execucao = 'feito'
        AND a.status_pagamento = 'pago'
    ");
    $stmtTotal->execute([$data_inicio . ' 00:00:00', $data_fim . ' 23:59:59']);
    $totalProcedimentos = $stmtTotal->fetchColumn() ?? 0;

    // Buscar a lista de procedimentos individuais executados
    $stmtProc = $pdo->prepare("
        SELECT 
            a.data_atendimento,
            pac.nome as paciente_nome,
            usu.nome as dentista_nome,
            p.nome as procedimento_nome,
            ap.quantidade as quantidade_executada,
            ap.valor_procedimento as valor_bruto,
            ap.custo_auxiliar
        FROM atendimento_procedimentos ap
        JOIN atendimentos a ON ap.id_atendimento = a.id
        JOIN procedimentos p ON ap.id_procedimento = p.id
        JOIN pacientes pac ON a.paciente_id = pac.id
        JOIN usuarios usu ON a.id_dentista = usu.id
        WHERE a.data_atendimento BETWEEN ? AND ? 
        AND ap.status_execucao = 'feito'
        AND a.status_pagamento = 'pago'
        ORDER BY a.data_atendimento DESC
    ");
    $stmtProc->execute([$data_inicio . ' 00:00:00', $data_fim . ' 23:59:59']);
    $procedimentos_relatorio = $stmtProc->fetchAll();

} catch (Exception $e) {
    echo "<p class='error'>Erro ao gerar relatório: " . $e->getMessage() . "</p>";
    $procedimentos_relatorio = [];
    $totalProcedimentos = 0;
}
?>

<div class="card">
    <h2>Relatório por Procedimentos</h2>

    <form method="GET" action="<?= BASE_URL ?>relatorios/procedimentos" class="card" style="margin-top: 1rem;">
        <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            <div class="form-group">
                <label for="inicio">Data Início</label>
                <input type="date" name="inicio" id="inicio" value="<?= htmlspecialchars($data_inicio) ?>">
            </div>
            <div class="form-group">
                <label for="fim">Data Fim</label>
                <input type="date" name="fim" id="fim" value="<?= htmlspecialchars($data_fim) ?>">
            </div>
            <button type="submit" class="btn btn-primary">Filtrar</button>
        </div>
    </form>

    <div style="margin-top: 2rem;">
        <table class="mobile-card-table" style="margin-top: 1rem;">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Paciente</th>
                    <th>Dentista</th>
                    <th>Procedimento</th>
                    <th>Qtd</th>
                    <th>Valor Bruto</th>
                    <th>Custo Protético</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($procedimentos_relatorio) > 0): ?>
                    <?php 
                    $somaQtd = 0;
                    $somaBruto = 0;
                    $somaProtetico = 0;
                    foreach($procedimentos_relatorio as $proc): 
                        $somaQtd += $proc['quantidade_executada'];
                        $somaBruto += $proc['valor_bruto'];
                        $somaProtetico += $proc['custo_auxiliar'];
                    ?>
                    <tr>
                        <td data-label="Data" data-sort="<?= date('Y-m-d', strtotime($proc['data_atendimento'])) ?>"><?= date('d/m/Y', strtotime($proc['data_atendimento'])) ?></td>
                        <td data-label="Paciente"><?= htmlspecialchars($proc['paciente_nome']) ?></td>
                        <td data-label="Dentista"><?= htmlspecialchars($proc['dentista_nome']) ?></td>
                        <td data-label="Procedimento"><?= htmlspecialchars($proc['procedimento_nome']) ?></td>
                        <td data-label="Qtd"><?= htmlspecialchars($proc['quantidade_executada']) ?></td>
                        <td data-label="Valor Bruto" style="color: var(--success-color); font-weight: bold;">R$ <?= number_format($proc['valor_bruto'], 2, ',', '.') ?></td>
                        <td data-label="Custo Protético">R$ <?= number_format($proc['custo_auxiliar'], 2, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 20px;">Nenhum procedimento encontrado para o período selecionado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <?php if (count($procedimentos_relatorio) > 0): ?>
            <tfoot>
                <tr style="font-weight: bold; background-color: #f8f9fa;">
                    <td colspan="4" style="text-align: right;">Totais:</td>
                    <td data-label="Total Qtd"><?= $somaQtd ?></td>
                    <td data-label="Total Bruto" style="color: var(--success-color);">R$ <?= number_format($somaBruto, 2, ',', '.') ?></td>
                    <td data-label="Total Protético">R$ <?= number_format($somaProtetico, 2, ',', '.') ?></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>

<?php require_once 'views/footer.php'; ?>