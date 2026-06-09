<?php require_once 'views/header.php'; ?>

<div class="card">
    <h2>Fluxo de Atendimentos de Hoje (<?= date('d/m/Y') ?>)</h2>
    <p>Visão geral de todos os pacientes agendados para a clínica no dia de hoje.</p>

    <div style="margin-top: 1rem; display: flex; gap: 1rem;">
        <a href="<?= BASE_URL ?>atendimentos/novo" class="btn btn-primary">Adicionar Novo Atendimento</a>
        <a href="<?= BASE_URL ?>pacientes" class="btn btn-secondary">Gerenciar Pacientes</a>
    </div>

    <table class="mobile-card-table table" style="margin-top: 2rem; width: 100%;">
        <thead>
            <tr>
                <th>Horário</th>
                <th>Paciente</th>
                <th>Dentista Responsável</th>
                <th>Valor Total</th>
                <th>Status Pgto.</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($atendimentosHoje) > 0): ?>
                <?php foreach ($atendimentosHoje as $atendimento): ?>
                    <tr>
                        <td data-label="Horário"><?= date('H:i', strtotime($atendimento['data_atendimento'])) ?></td>
                        <td data-label="Paciente"><?= htmlspecialchars($atendimento['paciente_nome']) ?><br><small><?= htmlspecialchars($atendimento['telefone']) ?></small></td>
                        <td data-label="Dentista"><?= htmlspecialchars($atendimento['dentista_nome'] ?? 'Não informado') ?></td>
                        <td data-label="Valor Total">R$ <?= number_format($atendimento['valor_total'], 2, ',', '.') ?></td>
                        <td data-label="Status Pgto.">
                            <?php if ($atendimento['status_pagamento'] === 'pago'): ?>
                                <span style="color: green; font-weight: bold;">Pago</span>
                            <?php else: ?>
                                <span style="color: red; font-weight: bold;">Pendente</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Ação">
                            <a href="<?= BASE_URL ?>atendimentos/editar?id=<?= $atendimento['atendimento_id'] ?>" class="btn btn-secondary btn-sm">Editar</a>
                            <?php if ($atendimento['status_pagamento'] !== 'pago'): ?>
                                <a href="<?= BASE_URL ?>atendimentos/confirmarPagamento?id=<?= $atendimento['atendimento_id'] ?>" class="btn btn-success btn-sm">Receber</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center;">Nenhum atendimento agendado para hoje.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'views/footer.php'; ?>