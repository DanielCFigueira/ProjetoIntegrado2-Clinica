<?php
namespace App\Controllers;

use App\Models\Despesa;

class DespesaController {
    
    public function index() {
        require_once 'config/session.php';
        require_once 'config/seguranca.php';
        require_once 'config/controle_acesso.php';
        
        if (!is_admin()) {
            header('Location: ' . BASE_URL . 'index.php');
            exit;
        }

        $despesaModel = new Despesa();
        $despesas = $despesaModel->listarTodas();
        require_once 'app/Views/despesas/index.php';
    }

    public function salvar() {

        require_once 'config/session.php';
        require_once 'config/seguranca.php';
        require_once 'config/controle_acesso.php';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $descricao = $_POST['descricao'] ?? '';
            $valor = $_POST['valor'] ?? 0;
            $tipo = $_POST['tipo'] ?? 'fixa';
            $data_despesa = $_POST['data_despesa'] ?? date('Y-m-d');

            $despesaModel = new Despesa();
            $despesaModel->salvar($descricao, $valor, $tipo, $data_despesa);
            
            header('Location: ' . BASE_URL . 'despesas');
            exit;
        }
    }

    public function excluir() {

        require_once 'config/session.php';
        require_once 'config/seguranca.php';
        require_once 'config/controle_acesso.php';

        $id = $_GET['id'] ?? null;
        if ($id) {
            $despesaModel = new Despesa();
            $despesaModel->excluir($id);
        }
        
        header('Location: ' . BASE_URL . 'despesas');
        exit;
    }
}