<?php require_once 'views/header.php'; ?>

<!-- Importa o Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="card">
    <h2>Dashboard Geral</h2>
    
    <?php
        $custosOperacionais = ($faturamentoBruto ?? 0) - ($lucroLiquido ?? 0);
    ?>
    <div class="dashboard-grid" style="margin-top: 1.5rem;">
        <div class="stat-card">
            <h3>Faturamento Mensal</h3>
            <div class="stat-value">R$ <?= number_format($faturamentoBruto ?? 0, 2, ',', '.') ?></div>
        </div>
        <div class="stat-card" style="border-left-color: #f39c12;">
            <h3>Custos Operacionais</h3>
            <div class="stat-value">R$ <?= number_format((($faturamentoBruto ?? 0) - ($lucroLiquido ?? 0)), 2, ',', '.') ?></div>
            <small style="color: #95a5a6;">(Comissões, Cartão, Protético)</small>
        </div>
        <div class="stat-card" style="border-left-color: var(--danger-color);">
            <h3>Despesas Fixas</h3>
            <div class="stat-value">R$ <?= number_format($totalDespesas ?? 0, 2, ',', '.') ?></div>
            <small style="color: #95a5a6;">(Água, Luz, Aluguel)</small>
        </div>
        <div class="stat-card" style="border-left-color: var(--success-color);">
            <h3>Lucro Líquido</h3>
            <div class="stat-value">R$ <?= number_format(($lucroLiquido ?? 0) - ($totalDespesas ?? 0), 2, ',', '.') ?></div>
        </div>
    </div>

    <!-- Container do Gráfico Anual -->
    <div style="margin-top: 3rem; background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <h3 style="text-align: center; margin-bottom: 1rem;">Faturamento Anual (<?= date('Y') ?>)</h3>
        <canvas id="graficoAnual" style="max-height: 350px;"></canvas>
    </div>

    <h3 style="margin-top: 3rem;">Últimos Atendimentos (Mês Atual)</h3>
    <table class="mobile-card-table table" style="margin-top: 1rem; width: 100%;">
        <thead>
            <tr>
                <th>Data</th>
                <th>Paciente</th>
                <th>Dentista</th>
                <th>Status</th>
                <th>Taxa Cartão</th>
                <th>Comissão</th>
                <th>Valor Líquido</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($ultimosAtendimentos as $at): ?>
            <tr>
                <td data-label="Data" data-sort="<?= date('Y-m-d', strtotime($at['data_atendimento'])) ?>"><?= date('d/m/Y', strtotime($at['data_atendimento'])) ?></td>
                <td data-label="Paciente"><?= htmlspecialchars($at['paciente_nome']) ?></td>
                <td data-label="Dentista"><?= htmlspecialchars($at['dentista']) ?></td>
                <td data-label="Status">
                    <?php if($at['status_pagamento'] === 'pago'): ?>
                        <span style="color: var(--success-color); font-weight: bold;">Pago</span>
                    <?php else: ?>
                        <span style="color: var(--danger-color); font-weight: bold;">Pendente</span>
                    <?php endif; ?>
                </td>
                <td data-label="Taxa Cartão">R$ <?= number_format($at['taxa_cartao'] ?? 0, 2, ',', '.') ?></td>
                <td data-label="Comissão">R$ <?= number_format($at['comissao_dentista'] ?? 0, 2, ',', '.') ?></td>
                <td data-label="Valor Líquido">R$ <?= number_format($at['valor_liquido_clinica'], 2, ',', '.') ?></td>
                <td data-label="Ações">
                    <a href="<?= BASE_URL ?>relatorios" class="btn btn-secondary">Ver Detalhes</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    const ctx = document.getElementById('graficoAnual').getContext('2d');
    
    // Dados passados do PHP para o Javascript
    const dadosBrutos = <?= json_encode($dadosGrafico ?? []) ?>;
    
    const labels = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
    const dadosLucro = [];
    const dadosTaxas = [];

    // Organiza os dados em arrays para os 12 meses
    for (let i = 1; i <= 12; i++) {
        if (dadosBrutos[i]) {
            dadosLucro.push(dadosBrutos[i].lucro);
            dadosTaxas.push(dadosBrutos[i].taxas);
        } else {
            dadosLucro.push(0);
            dadosTaxas.push(0);
        }
    }

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Lucro da Clínica (R$)',
                    data: dadosLucro,
                    backgroundColor: 'rgba(46, 204, 113, 0.7)', // Verde
                    borderColor: 'rgba(39, 174, 96, 1)',
                    borderWidth: 1,
                    borderRadius: 4
                },
                {
                    label: 'Custos Operacionais (R$)',
                    data: dadosTaxas,
                    backgroundColor: 'rgba(52, 152, 219, 0.7)', // Azul
                    borderColor: 'rgba(41, 128, 185, 1)',
                    borderWidth: 1,
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR');
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': R$ ' + context.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                        }
                    }
                }
            }
        }
    });
});
</script>

<?php require_once 'views/footer.php'; ?>