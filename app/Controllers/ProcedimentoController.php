<?php
namespace App\Controllers;

use App\Models\Procedimento;

class ProcedimentoController {
    public function index() {
        require_once 'config/session.php';
        require_once 'config/seguranca.php';
        require_once 'config/controle_acesso.php';
        
        if (!is_admin()) {
            header('Location: ' . BASE_URL . 'index.php');
            exit;
        }

        $procedimentoModel = new Procedimento();
        $procedimentos = $procedimentoModel->listarTodos();
        require_once 'app/Views/procedimentos/index.php';
    }

    public function salvar() {

        require_once 'config/session.php';
        require_once 'config/seguranca.php';
        require_once 'config/controle_acesso.php';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome = $_POST['nome'] ?? '';
            $categoria = $_POST['categoria'] ?? 'geral';
            $tipo = $_POST['tipo'] ?? 0;
            $valor_base = $_POST['valor_base'] ?? 0;

            $procedimentoModel = new Procedimento();
            $procedimentoModel->salvar($nome, $categoria, $tipo, $valor_base);
            
            header('Location: ' . BASE_URL . 'procedimentos?msg=sucesso');
            exit;
        }
    }

    public function excluir() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $procedimentoModel = new Procedimento();
            if ($procedimentoModel->excluir($id)) {
                header('Location: ' . BASE_URL . 'procedimentos?msg=excluido');
            } else {
                header('Location: ' . BASE_URL . 'procedimentos?erro=conflito');
            }
        } else {
            header('Location: ' . BASE_URL . 'procedimentos');
        }
        exit;
    }

    public function salvarArquivo() {
        require_once 'config/session.php';
        require_once 'config/seguranca.php';
        require_once 'config/controle_acesso.php';
        
        if (!is_admin() && !is_dentista()) {
            $_SESSION['toast'] = ['type' => 'error', 'message' => 'Acesso negado.'];
            header('Location: ' . BASE_URL . 'index.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $atendimento_procedimento_id = $_POST['atendimento_procedimento_id'] ?? null;
            $paciente_nome_redirect = $_POST['paciente_nome_redirect'] ?? '';

            if (!$atendimento_procedimento_id || !isset($_FILES['arquivo_procedimento']) || $_FILES['arquivo_procedimento']['error'] !== UPLOAD_ERR_OK) {
                header("Location: " . BASE_URL . 'relatorios/paciente?paciente_nome=' . urlencode($paciente_nome_redirect) . '&erro=' . urlencode('Falha no upload do arquivo.'));
                exit;
            }

            try {
                $uploadDir = 'uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->file($_FILES['arquivo_procedimento']['tmp_name']);
                $allowedMimeTypes = ['image/jpeg' => 'jpg', 'image/png'  => 'png', 'application/pdf' => 'pdf'];

                if (!array_key_exists($mimeType, $allowedMimeTypes)) {
                    header("Location: " . BASE_URL . 'relatorios/paciente?paciente_nome=' . urlencode($paciente_nome_redirect) . '&erro=' . urlencode('Formato não permitido.'));
                    exit;
                }

                $fileName = 'proc_' . $atendimento_procedimento_id . '_' . uniqid() . '.' . $allowedMimeTypes[$mimeType];
                if (!move_uploaded_file($_FILES['arquivo_procedimento']['tmp_name'], $uploadDir . $fileName)) throw new \Exception();

                $pdo = \App\Core\Database::getInstance();
                $stmt = $pdo->prepare("UPDATE atendimento_procedimentos SET url_arquivo = ? WHERE id = ?");
                $stmt->execute(['uploads/' . $fileName, $atendimento_procedimento_id]);

                header("Location: " . BASE_URL . 'relatorios/paciente?paciente_nome=' . urlencode($paciente_nome_redirect) . '&msg=upload_sucesso');
            } catch (\Exception $e) {
                header("Location: " . BASE_URL . 'relatorios/paciente?paciente_nome=' . urlencode($paciente_nome_redirect) . '&erro=' . urlencode('Erro ao salvar.'));
            }
        }
        exit;
    }

    public function removerAnexoAjax() {
        require_once 'config/session.php';
        require_once 'config/controle_acesso.php';
        header('Content-Type: application/json');

        if (!is_admin() && !is_dentista()) {
            echo json_encode(['status' => 'error', 'message' => 'Acesso negado.']);
            exit;
        }

        $id = filter_input(INPUT_POST, 'id_procedimento', FILTER_SANITIZE_NUMBER_INT);
        if (empty($id)) {
            echo json_encode(['status' => 'error', 'message' => 'ID não fornecido.']);
            exit;
        }

        $pdo = \App\Core\Database::getInstance();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("SELECT url_arquivo FROM atendimento_procedimentos WHERE id = ?");
            $stmt->execute([$id]);
            $caminho = $stmt->fetchColumn();

            if (!empty($caminho)) {
                $stmt = $pdo->prepare("UPDATE atendimento_procedimentos SET url_arquivo = NULL WHERE id = ?");
                $stmt->execute([$id]);
                $caminho_absoluto = realpath(__DIR__ . '/../../' . $caminho);
                if ($caminho_absoluto && file_exists($caminho_absoluto)) @unlink($caminho_absoluto);
            }
            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'Arquivo removido.']);
        } catch (\Exception $e) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Erro interno.']);
        }
        exit;
    }

    public function removerDeAtendimentoAjax() {
        require_once 'config/session.php';
        require_once 'config/controle_acesso.php';
        header('Content-Type: application/json');

        if (!is_admin() && !is_dentista()) {
            echo json_encode(['status' => 'error', 'message' => 'Acesso negado.']);
            exit;
        }

        $id = filter_input(INPUT_POST, 'id_procedimento', FILTER_SANITIZE_NUMBER_INT);
        if (empty($id)) {
            echo json_encode(['status' => 'error', 'message' => 'ID não fornecido.']);
            exit;
        }

        $pdo = \App\Core\Database::getInstance();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("SELECT ap.status_execucao, a.status_pagamento, ap.url_arquivo FROM atendimento_procedimentos ap JOIN atendimentos a ON ap.id_atendimento = a.id WHERE ap.id = ?");
            $stmt->execute([$id]);
            $proc = $stmt->fetch();

            if (!$proc || $proc['status_execucao'] !== 'pendente' || $proc['status_pagamento'] !== 'nao_aplicavel') {
                echo json_encode(['status' => 'error', 'message' => 'Não autorizado.']);
                exit;
            }

            if (!empty($proc['url_arquivo'])) {
                $caminho = realpath(__DIR__ . '/../../' . $proc['url_arquivo']);
                if ($caminho && file_exists($caminho)) @unlink($caminho);
            }

            $pdo->prepare("DELETE FROM atendimento_procedimentos WHERE id = ?")->execute([$id]);
            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'Removido.']);
        } catch (\Exception $e) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Erro interno.']);
        }
        exit;
    }
}