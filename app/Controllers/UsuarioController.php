<?php
namespace App\Controllers;

use App\Models\Usuario;

class UsuarioController {
    public function index() {
        require_once 'config/session.php';
        require_once 'config/seguranca.php';
        require_once 'config/controle_acesso.php';
        
        if (!is_admin()) {
            header('Location: ' . BASE_URL . 'index.php');
            exit;
        }

        $usuarioModel = new Usuario();
        $usuarios = $usuarioModel->listarTodos();
        require_once 'app/Views/usuarios/index.php';
    }

    public function salvar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome = $_POST['nome'] ?? '';
            $login = $_POST['login'] ?? '';
            $senha = $_POST['senha'] ?? '';
            $perfil = $_POST['perfil'] ?? 'recepcionista';

            $usuarioModel = new Usuario();
            $resultado = $usuarioModel->salvar($nome, $login, $senha, $perfil);
            
            if ($resultado === 'login_duplicado') {
                header('Location: ' . BASE_URL . 'usuarios?erro=login_duplicado');
            } else {
                header('Location: ' . BASE_URL . 'usuarios?msg=sucesso');
            }
            exit;
        }
    }

    public function excluir() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $id = $_GET['id'] ?? null;
        if ($id) {
            $usuarioModel = new Usuario();
            $resultado = $usuarioModel->excluir($id);
            
            if ($resultado === 'autoexclusao') {
                header('Location: ' . BASE_URL . 'usuarios?erro=autoexclusao');
            } elseif ($resultado === 'conflito') {
                header('Location: ' . BASE_URL . 'usuarios?erro=conflito_atendimento');
            } else {
                header('Location: ' . BASE_URL . 'usuarios?msg=excluido');
            }
        } else {
            header('Location: ' . BASE_URL . 'usuarios');
        }
        exit;
    }

    public function editar() {
        require_once 'config/session.php';
        require_once 'config/seguranca.php';
        require_once 'config/controle_acesso.php';
        
        if (!is_admin()) {
            header("Location: " . BASE_URL . "index.php");
            exit;
        }

        $id = $_GET['id'] ?? null;
        if (!$id) {
            header("Location: " . BASE_URL . "usuarios");
            exit;
        }

        $usuarioModel = new \App\Models\Usuario();
        $usuario = $usuarioModel->getById($id);

        if (!$usuario) {
            $erro = "Usuário não encontrado.";
        }

        require_once 'app/Views/usuarios/editar.php';
    }

    public function atualizar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            if ($id) {
                $nome = $_POST['nome'] ?? '';
                $login = $_POST['login'] ?? '';
                $senha = $_POST['senha'] ?? '';
                $perfil = $_POST['perfil'] ?? '';

                $usuarioModel = new \App\Models\Usuario();
                $resultado = $usuarioModel->atualizar($id, $nome, $login, $senha, $perfil);

                if ($resultado === 'login_duplicado') {
                    header("Location: " . BASE_URL . "usuarios/editar/" . $id . "&erro=login_duplicado");
                    exit;
                }
            }
            header("Location: " . BASE_URL . "usuarios?msg=atualizado");
            exit;
        }
    }
}