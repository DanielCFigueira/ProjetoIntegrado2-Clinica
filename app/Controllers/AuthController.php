<?php
namespace App\Controllers;

use App\Models\Auth;

class AuthController {
    
    public function processarLogin() {
        require_once 'config/session.php';
        require_once 'config/app.php';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $login = $_POST['login'] ?? '';
            $senha = $_POST['senha'] ?? '';

            $authModel = new Auth();
            $usuarioLogado = $authModel->validarLogin($login, $senha);

            if ($usuarioLogado) {
                $_SESSION['usuario_id'] = $usuarioLogado['id'];
                $_SESSION['usuario_nome'] = $usuarioLogado['nome'];
                $_SESSION['usuario_perfil'] = $usuarioLogado['perfil'];
                header("Location: " . BASE_URL . "index.php");
                exit;
            } else {
                header("Location: " . BASE_URL . "login.php?erro=1");
                exit;
            }
        }
    }

    public function logout() {
        require_once 'config/session.php';
        require_once 'config/app.php';
        session_destroy();
        header("Location: " . BASE_URL . "login.php");
        exit;
    }
}