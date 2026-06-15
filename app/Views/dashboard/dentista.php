<?php require_once 'views/header.php'; ?>

<div class="card">
    <h2>Agenda de Hoje (<?= date('d/m/Y') ?>)</h2>
    <p>Olá, <?= htmlspecialchars($_SESSION['usuario_nome']) ?>! Aqui estão os seus atendimentos agendados para hoje.</p>

    <table class="mobile-card-table table" style="margin-top: 1rem; width: 100%;">
        <thead>
            <tr>
                <th>Horário</th>
                <th>Paciente</th>
                <th>Procedimento</th>
                <th>Status</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($atendimentosHoje) > 0): ?>
                <?php foreach ($atendimentosHoje as $atendimento): ?>
                    <tr>
                        <td data-label="Horário"><?= date('H:i', strtotime($atendimento['data_atendimento'])) ?></td>
                        <td data-label="Paciente"><?= htmlspecialchars($atendimento['paciente_nome']) ?><br><small><?= htmlspecialchars($atendimento['telefone']) ?></small></td>
                        <td data-label="Procedimento"><?= htmlspecialchars($atendimento['procedimento_nome'] ?? 'Consulta Geral') ?></td>
                        <td data-label="Status">
                            <?php if ($atendimento['status_execucao'] === 'feito' || $atendimento['status_execucao'] === 'finalizado'): ?>
                                <span style="color: green; font-weight: bold;">Concluído</span>
                            <?php else: ?>
                                <span style="color: orange; font-weight: bold;">Pendente</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Ação">
                            <?php if ($atendimento['status_execucao'] === 'pendente' && !empty($atendimento['procedimento_id'])): ?>
                                <form action="<?= BASE_URL ?>atendimentos/concluirProcedimento" method="POST" style="display:inline;">
                                    <input type="hidden" name="procedimento_id" value="<?= $atendimento['procedimento_id'] ?>">
                                    <button type="submit" class="btn btn-success btn-sm">Marcar Concluído</button>
                                </form>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center;">Nenhum atendimento agendado para você hoje.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'views/footer.php'; ?>