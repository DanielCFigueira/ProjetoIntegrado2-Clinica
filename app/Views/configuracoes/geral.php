<?php require_once 'views/header.php'; ?>

<div class="card">
    <h2>Configurações Gerais (Taxas e Comissões)</h2>
    <p class="text-muted">Ajuste as taxas da maquininha e as comissões pagas aos dentistas.</p>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'sucesso'): ?>
        <div style="background-color: #d4edda; color: #155724; padding: 1rem; border-radius: 5px; margin-bottom: 1rem; border: 1px solid #c3e6cb;">
            Configurações atualizadas com sucesso!
        </div>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>configuracoes_gerais/salvar" method="POST" style="margin-top: 1.5rem;">
        

        <h3 style="border-bottom: 1px solid #eee; padding-bottom: 0.5rem; margin-top: 1rem;">🏢 Dados da Clínica</h3>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 2rem; margin-top: 1rem;">
            <div class="form-group">
                <label>Nome da Clínica</label>
                <input type="text" name="clinica_nome" value="<?= htmlspecialchars($configuracoes['clinica_nome'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>CNPJ</label>
                <input type="text" name="clinica_cnpj" value="<?= htmlspecialchars($configuracoes['clinica_cnpj'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Telefone</label>
                <input type="text" name="clinica_telefone" value="<?= htmlspecialchars($configuracoes['clinica_telefone'] ?? '') ?>" required>
            </div>
            <div class="form-group" style="grid-column: 1 / -1;">
                <label>Endereço Completo</label>
                <input type="text" name="clinica_endereco" value="<?= htmlspecialchars($configuracoes['clinica_endereco'] ?? '') ?>" required style="width: 100%;">
            </div>
        </div>

        <h3 style="border-bottom: 1px solid #eee; padding-bottom: 0.5rem; margin-top: 2rem;">💳 Taxas da Maquininha (%)</h3>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; margin-top: 1rem;">
            <div class="form-group">
                <label>Taxa Débito (%)</label>
                <input type="number" step="0.0001" name="taxa_debito" value="<?= floatval($configuracoes['taxa_debito']) * 100 ?>" required>
            </div>
            <div class="form-group">
                <label>Crédito à Vista (%)</label>
                <input type="number" step="0.0001" name="taxa_credito_avista" value="<?= floatval($configuracoes['taxa_credito_avista']) * 100 ?>" required>
            </div>
            <?php for ($i = 2; $i <= 12; $i++): ?>
            <div class="form-group">
                <label>Crédito <?= $i ?>x (%)</label>
                <input type="number" step="0.0001" name="taxa_credito_<?= $i ?>" value="<?= floatval($configuracoes['taxa_credito_'.$i]) * 100 ?>" required>
            </div>
            <?php endfor; ?>
        </div>

        <h3 style="border-bottom: 1px solid #eee; padding-bottom: 0.5rem;">🧑‍⚕️ Comissões dos Dentistas (%)</h3>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 2rem; margin-top: 1rem;">
            <div class="form-group">
                <label>Clínico Geral Base (%)</label>
                <input type="number" step="0.01" name="comissao_geral_base" value="<?= floatval($configuracoes['comissao_geral_base']) * 100 ?>" required>
            </div>
            <div class="form-group">
                <label>Clínico Geral Bônus (%)</label>
                <input type="number" step="0.01" name="comissao_geral_bonus" value="<?= floatval($configuracoes['comissao_geral_bonus']) * 100 ?>" required>
            </div>
            <div class="form-group">
                <label>Meta p/ Bônus Geral (R$)</label>
                <input type="number" step="0.01" name="meta_faturamento_geral" value="<?= floatval($configuracoes['meta_faturamento_geral']) ?>" required>
            </div>
            <div class="form-group">
                <label>Especializado (Orto/Padrão) (%)</label>
                <input type="number" step="0.01" name="comissao_especializado" value="<?= floatval($configuracoes['comissao_especializado']) * 100 ?>" required>
            </div>
            <div class="form-group">
                <label>Especializado (Canal/Cirurgias) (%)</label>
                <input type="number" step="0.01" name="comissao_canal" value="<?= floatval($configuracoes['comissao_canal']) * 100 ?>" required>
            </div>
            <div class="form-group">
                <label>Prótese (%)</label>
                <input type="number" step="0.01" name="comissao_protese" value="<?= floatval($configuracoes['comissao_protese']) * 100 ?>" required>
            </div>
        </div>

        <div style="margin-top: 2rem;">
            <button type="submit" class="btn btn-primary">Salvar Configurações</button>
            <a href="<?= BASE_URL ?>index.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php require_once 'views/footer.php'; ?>