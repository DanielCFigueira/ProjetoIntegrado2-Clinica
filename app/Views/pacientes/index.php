<?php require_once 'views/header.php'; ?>

<div class="card">
    <h2>Gestão de Pacientes</h2>

    <?php if (isset($_GET['erro'])):
        $erro = $_GET['erro'];
        if ($erro === 'conflito_atendimento') {
            echo "<p class='error'>Não é possível excluir o paciente, pois ele está vinculado a um ou mais atendimentos.</p>";
        }
    endif; ?>
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'sucesso'): ?>
        <p class="success">Paciente salvo com sucesso!</p>
    <?php endif; ?>
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'excluido'): ?>
        <p class="success">Paciente excluído com sucesso!</p>
    <?php endif; ?>

    <!-- Formulário para Adicionar Paciente (Oculto por padrão) -->
    <div class="card" style="margin-top: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; cursor: pointer;" onclick="toggleFormulario()">
            <h3 style="margin: 0;">Novo Paciente</h3>
            <button class="btn btn-secondary" type="button" id="btn-toggle-form">▼ Expandir Cadastro</button>
        </div>
        
        <form action="<?= BASE_URL ?>pacientes/salvar" method="POST" id="form-novo-paciente" style="display: none; margin-top: 1.5rem;">
             <div class="grid-container">
                <div class="form-group grid-col-6">
                    <label for="paciente_nome">Nome Completo</label>
                    <input type="text" name="paciente_nome" id="paciente_nome" required>
                </div>
                <div class="form-group grid-col-3">
                    <label for="paciente_cpf">CPF</label>
                    <input type="text" name="paciente_cpf" id="paciente_cpf" maxlength="14" oninput="mascaraCPF(this)">
                </div>
                 <div class="form-group grid-col-3">
                    <label for="paciente_data_nascimento">Data de Nascimento</label>
                    <input type="date" name="paciente_data_nascimento" id="paciente_data_nascimento">
                </div>
                <div class="form-group grid-col-3">
                    <label for="paciente_telefone">Telefone</label>
                    <input type="text" name="paciente_telefone" id="paciente_telefone" maxlength="15" oninput="mascaraTelefone(this)">
                </div>
                <div class="form-group grid-col-3">
                    <label for="paciente_email">E-mail</label>
                    <input type="email" name="paciente_email" id="paciente_email" onblur="validarEmail(this)">
                    <span id="email-error" style="color: red; font-size: 0.8em; display: none;">E-mail inválido</span>
                </div>
                <div class="form-group grid-col-2">
                    <label for="paciente_cep">CEP</label>
                    <input type="text" name="paciente_cep" id="paciente_cep" maxlength="9" oninput="mascaraCEP(this)">
                </div>
                <div class="form-group grid-col-4">
                    <label for="paciente_endereco">Endereço</label>
                    <input type="text" name="paciente_endereco" id="paciente_endereco">
                </div>
                <div class="form-group grid-col-2">
                    <label for="paciente_numero">Número</label>
                    <input type="text" name="paciente_numero" id="paciente_numero">
                </div>
                <div class="form-group grid-col-4">
                    <label for="paciente_bairro">Bairro</label>
                    <input type="text" name="paciente_bairro" id="paciente_bairro">
                </div>
                <div class="form-group grid-col-4">
                    <label for="paciente_cidade">Cidade</label>
                    <input type="text" name="paciente_cidade" id="paciente_cidade">
                </div>
                <div class="form-group grid-col-2">
                    <label for="paciente_estado">Estado</label>
                    <input type="text" name="paciente_estado" id="paciente_estado" maxlength="2">
                </div>
            </div>
            <button type="submit" class="btn btn-success">Salvar Novo Paciente</button>
        </form>
    </div>

    <!-- Tabela de Pacientes -->
    <h3 style="margin-top: 2rem;">Pacientes Cadastrados</h3>

    <!-- Barra de Busca -->
    <form method="GET" action="<?= BASE_URL ?>pacientes" style="margin-top: 1rem; margin-bottom: 1rem; display: flex; gap: 1rem;">
        <input type="text" name="busca" placeholder="Buscar por Nome ou CPF" value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>" style="flex-grow: 1; padding: 8px;">
        <button type="submit" class="btn btn-primary">Buscar</button>
        <a href="<?= BASE_URL ?>pacientes" class="btn btn-secondary">Limpar</a>
    </form>
    
    <table class="mobile-card-table table" style="margin-top: 1rem; width: 100%;">
        <thead>
            <tr>
                <th>Nome</th>
                <th>CPF</th>
                <th>Telefone</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($pacientes) > 0): ?>
                <?php foreach ($pacientes as $paciente): ?>
                    <tr>
                        <td data-label="Nome"><?= htmlspecialchars($paciente['nome']) ?></td>
                        <td data-label="CPF"><?= htmlspecialchars($paciente['cpf'] ?? '') ?></td>
                        <td data-label="Telefone"><?= htmlspecialchars($paciente['telefone'] ?? '') ?></td>
                        <td data-label="Ações" style="display: flex; gap: 0.5rem;">
                            <a href="<?= BASE_URL ?>pacientes/editar?id=<?= $paciente['id'] ?>" class="btn btn-primary">Editar</a>
                            <a href="<?= BASE_URL ?>pacientes/excluir?id=<?= $paciente['id'] ?>" class="btn btn-danger btn-delete">Remover</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align: center;">Nenhum paciente encontrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Paginação -->
    <?php if (isset($totalPaginas) && $totalPaginas > 1): ?>
    <div style="display: flex; justify-content: flex-end; margin-top: 1rem; gap: 0.5rem;">
        <?php for ($i = 1; $i <= $totalPaginas; $i++): 
            $queryParams = $_GET;
            $queryParams['pagina'] = $i;
            $url = '?' . http_build_query($queryParams);
            $activeClass = (isset($_GET['pagina']) && $_GET['pagina'] == $i) || (!isset($_GET['pagina']) && $i == 1) ? 'btn-primary' : 'btn-secondary';
        ?>
            <a href="<?= $url ?>" class="btn <?= $activeClass ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>

</div>

<style>
/* Oculta a barra de busca nativa do DataTables nesta view */
.dataTables_filter { display: none; }

.success { color: green; background: #e8f5e9; padding: 1rem; border-radius: 6px; }
.grid-container { display: grid; grid-template-columns: repeat(6, 1fr); gap: 1rem; margin-bottom: 1rem; }
.grid-col-2 { grid-column: span 2; }
.grid-col-3 { grid-column: span 3; }
.grid-col-4 { grid-column: span 4; }
.grid-col-6 { grid-column: span 6; }
@media (max-width: 768px) {
    .grid-col-2, .grid-col-3, .grid-col-4, .grid-col-6 { grid-column: span 6; }
}
</style>

<script>
function mascaraCPF(i) {
    var v = i.value;
    v = v.replace(/\D/g, ""); //Remove tudo o que não é dígito
    v = v.replace(/(\d{3})(\d)/, "$1.$2"); //Coloca um ponto entre o terceiro e o quarto dígitos
    v = v.replace(/(\d{3})(\d)/, "$1.$2"); //Coloca um ponto entre o terceiro e o quarto dígitos
    v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2"); //Coloca um hífen entre o terceiro e o quarto dígitos
    i.value = v;
}

function mascaraTelefone(i) {
    var v = i.value;
    v = v.replace(/\D/g, ""); //Remove tudo o que não é dígito
    v = v.replace(/^(\d{2})(\d)/g, "($1) $2"); //Coloca parênteses em volta dos dois primeiros dígitos
    v = v.replace(/(\d)(\d{4})$/, "$1-$2"); //Coloca hífen entre o quarto e o quinto dígitos
    i.value = v;
}

function mascaraCEP(i) {
    var v = i.value;
    v = v.replace(/\D/g, ""); //Remove tudo o que não é dígito
    v = v.replace(/^(\d{5})(\d)/, "$1-$2"); //Coloca hífen entre o quinto e o sexto dígitos
    i.value = v;
}

function validarEmail(field) {
    const usuario = field.value.substring(0, field.value.indexOf("@"));
    const dominio = field.value.substring(field.value.indexOf("@")+ 1, field.value.length);
    const errorSpan = document.getElementById('email-error');

    field.style.borderColor = '';

    if (field.value === '') {
        errorSpan.style.display = 'none';
        return;
    }

    if ((usuario.length >=1) && (dominio.length >=3) && (usuario.search("@")==-1) && (dominio.search("@")==-1) && (usuario.search(" ")==-1) && (dominio.search(" ")==-1) && (dominio.search(".")!=-1) && (dominio.indexOf(".") >=1)&& (dominio.lastIndexOf(".") < dominio.length - 1)) {
        errorSpan.style.display = 'none';
    } else {
        errorSpan.style.display = 'block';
    }
}

function toggleFormulario() {
    const form = document.getElementById('form-novo-paciente');
    const btn = document.getElementById('btn-toggle-form');
    
    if (form.style.display === 'none' || form.style.display === '') {
        form.style.display = 'block';
        btn.innerHTML = '▲ Recolher Cadastro';
    } else {
        form.style.display = 'none';
        btn.innerHTML = '▼ Expandir Cadastro';
    }
}

</script>

<?php require_once 'views/footer.php'; ?>