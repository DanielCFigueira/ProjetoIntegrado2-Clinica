<?php
namespace App\Controllers;

use App\Models\Paciente;

class PacienteController {
    
    public function index() {
        require_once 'config/session.php';
        require_once 'config/seguranca.php';
        require_once 'config/controle_acesso.php';
        
        if (!is_admin() && !is_recepcionista() && !is_dentista()) {
            header("Location: " . BASE_URL . "index.php");
            exit;
        }

        $busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        if ($pagina < 1) $pagina = 1;
        $itensPorPagina = 10;
        $offset = ($pagina - 1) * $itensPorPagina;

        $pacienteModel = new Paciente();
        
        $totalRegistros = $pacienteModel->contarTodos($busca);
        // Traz todos os registros de uma vez (até 10 mil) para deixar o DataTables cuidar da paginação e pesquisa no frontend
        $pacientes = $pacienteModel->listarComPaginacao($busca, 10000, 0);

        // Chama a View
        require_once 'app/Views/pacientes/index.php';
    }

    public function salvar() {

        require_once 'config/session.php';
        require_once 'config/seguranca.php';
        require_once 'config/controle_acesso.php';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = [
                'nome' => $_POST['paciente_nome'] ?? '',
                'cpf' => $_POST['paciente_cpf'] ?? '',
                'data_nascimento' => !empty($_POST['paciente_data_nascimento']) ? $_POST['paciente_data_nascimento'] : null,
                'telefone' => $_POST['paciente_telefone'] ?? '',
                'email' => $_POST['paciente_email'] ?? '',
                'cep' => $_POST['paciente_cep'] ?? '',
                'endereco' => $_POST['paciente_endereco'] ?? '',
                'numero' => $_POST['paciente_numero'] ?? '',
                'bairro' => $_POST['paciente_bairro'] ?? '',
                'cidade' => $_POST['paciente_cidade'] ?? '',
                'estado' => $_POST['paciente_estado'] ?? ''
            ];

            $pacienteModel = new Paciente();
            $pacienteModel->salvar($dados);
            
            header('Location: ' . BASE_URL . 'pacientes?msg=sucesso');
            exit;
        }
    }

    public function excluir() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $pacienteModel = new Paciente();
            $sucesso = $pacienteModel->excluir($id);
            
            if ($sucesso) {
                header('Location: ' . BASE_URL . 'pacientes?msg=excluido');
            } else {
                header('Location: ' . BASE_URL . 'pacientes?erro=conflito_atendimento');
            }
            exit;
        }
    }

    public function editar() {
        require_once 'config/session.php';
        require_once 'config/seguranca.php';
        require_once 'config/controle_acesso.php';
        
        if (!is_admin() && !is_recepcionista() && !is_dentista()) {
            header("Location: " . BASE_URL . "index.php");
            exit;
        }

        $id = $_GET['id'] ?? null;
        if (!$id) {
            header("Location: " . BASE_URL . "pacientes");
            exit;
        }

        $pacienteModel = new \App\Models\Paciente();
        $paciente = $pacienteModel->getById($id);

        if (!$paciente) {
            $erro = "Paciente não encontrado.";
        }

        require_once 'app/Views/pacientes/editar.php';
    }

    public function atualizar() {
        
        require_once 'config/session.php';
        require_once 'config/seguranca.php';
        require_once 'config/controle_acesso.php';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['paciente_id'] ?? null;
            if ($id) {
                $dados = [
                    'nome' => $_POST['paciente_nome'] ?? '',
                    'cpf' => $_POST['paciente_cpf'] ?? '',
                    'telefone' => $_POST['paciente_telefone'] ?? '',
                    'email' => $_POST['paciente_email'] ?? '',
                    'cep' => $_POST['paciente_cep'] ?? '',
                    'data_nascimento' => $_POST['paciente_data_nascimento'] ?? '',
                    'endereco' => $_POST['paciente_endereco'] ?? '',
                    'numero' => $_POST['paciente_numero'] ?? '',
                    'bairro' => $_POST['paciente_bairro'] ?? '',
                    'cidade' => $_POST['paciente_cidade'] ?? '',
                    'estado' => $_POST['paciente_estado'] ?? ''
                ];

                $pacienteModel = new \App\Models\Paciente();
                $pacienteModel->atualizar($id, $dados);
            }
            header("Location: " . BASE_URL . "pacientes?msg=sucesso");
            exit;
        }
    }

        public function buscarAjax() {
        header('Content-Type: application/json');
        if (!isset($_GET['term']) || strlen(trim($_GET['term'])) < 2) {
            echo json_encode([]);
            exit;
        }
        $term = '%' . trim($_GET['term']) . '%';
        try {
            $pdo = \App\Core\Database::getInstance();
            $stmt = $pdo->prepare(
                "SELECT id, nome, cpf, telefone, email FROM pacientes
                 WHERE nome LIKE ? OR cpf LIKE ? ORDER BY nome ASC LIMIT 10"
            );
            $stmt->execute([$term, $term]);
            echo json_encode($stmt->fetchAll(\PDO::FETCH_ASSOC));
        } catch (\Exception $e) {
            echo json_encode([]);
        }
        exit;
    }

    public function buscarProcedimentosPendentesAjax() {
        require_once 'config/session.php';
        header('Content-Type: application/json');
        if (!isset($_SESSION['usuario_id']) || !isset($_GET['paciente_id'])) {
            echo json_encode([]);
            exit;
        }
        try {
            $pdo = \App\Core\Database::getInstance();
            $stmt = $pdo->prepare("
                SELECT ap.id as atendimento_procedimento_id, ap.id_procedimento, p.nome as procedimento_nome,
                       p.categoria, ap.quantidade, ap.valor_procedimento, ap.local, ap.custo_auxiliar, ap.descricao, ap.natureza
                FROM atendimento_procedimentos ap
                JOIN atendimentos a ON ap.id_atendimento = a.id
                JOIN procedimentos p ON ap.id_procedimento = p.id
                WHERE a.paciente_id = ? AND ap.status_execucao = 'pendente'
            ");
            $stmt->execute([$_GET['paciente_id']]);
            echo json_encode($stmt->fetchAll(\PDO::FETCH_ASSOC));
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao buscar procedimentos.']);
        }
        exit;
    }

    public function buscarHistoricoAjax() {
        require_once 'config/session.php';
        header('Content-Type: application/json');
        if (!isset($_SESSION['usuario_id']) || !isset($_GET['paciente_id'])) {
            echo json_encode(['erro' => 'Não autorizado ou sem ID.']);
            exit;
        }
        try {
            $pdo = \App\Core\Database::getInstance();
            $stmt = $pdo->prepare("
                 SELECT ap.id, p.nome as procedimento_nome, ap.local, ap.descricao, ap.status_execucao, a.data_atendimento, a.status_pagamento
                FROM atendimento_procedimentos ap
                JOIN atendimentos a ON ap.id_atendimento = a.id
                JOIN procedimentos p ON ap.id_procedimento = p.id
                WHERE a.paciente_id = ? AND (ap.status_execucao = 'feito' OR ap.status_execucao = 'pendente')
                ORDER BY a.data_atendimento DESC
            ");
            $stmt->execute([(int)$_GET['paciente_id']]);
            $procedimentos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $realizados = []; $pendentes = [];
            foreach ($procedimentos as $proc) {
                if ($proc['status_execucao'] === 'feito') $realizados[] = $proc;
                else $pendentes[] = $proc;
            }
            echo json_encode(['realizados' => $realizados, 'pendentes' => $pendentes]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro interno.']);
        }
        exit;
    }
}