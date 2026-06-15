<?php
namespace App\Controllers;

class ConfiguracaoController {
    
    public function index() {
        require_once 'config/session.php';
        require_once 'config/seguranca.php';
        require_once 'config/controle_acesso.php';
        $pdo = \App\Core\Database::getInstance();

        try {
            $stmt = $pdo->prepare("SELECT nome, login FROM usuarios WHERE id = ?");
            $stmt->execute([$_SESSION['usuario_id']]);
            $usuario = $stmt->fetch();

            if (!$usuario) {
                session_destroy();
                header("Location: " . BASE_URL . "login.php");
                exit;
            }
        } catch (\Exception $e) {
            echo "<p class='error'>Erro ao carregar dados: " . $e->getMessage() . "</p>";
            exit;
        }

        require_once 'app/Views/configuracoes/configuracoes.php';
    }

    public function salvar() {
        require_once 'config/session.php';
        require_once 'config/seguranca.php';
        $pdo = \App\Core\Database::getInstance();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario_id = $_SESSION['usuario_id'];
            $nome = trim($_POST['nome']);
            
            $senha_antiga = $_POST['senha_antiga'] ?? '';
            $nova_senha = $_POST['nova_senha'] ?? '';
            $confirmar_senha = $_POST['confirmar_senha'] ?? '';

            if (empty($nome)) {
                header("Location: " . BASE_URL . "configuracoes?erro=geral");
                exit;
            }

            try {
                $stmt = $pdo->prepare("SELECT senha FROM usuarios WHERE id = ?");
                $stmt->execute([$usuario_id]);
                $usuario_atual = $stmt->fetch();

                if (!$usuario_atual) {
                    session_destroy();
                    header("Location: " . BASE_URL . "login.php");
                    exit;
                }

                $sql = "UPDATE usuarios SET nome = ?";
                $params = [$nome];

                if (!empty($senha_antiga) || !empty($nova_senha) || !empty($confirmar_senha)) {
                    if (empty($senha_antiga) || empty($nova_senha) || empty($confirmar_senha)) {
                        header("Location: " . BASE_URL . "configuracoes?erro=campos_vazios");
                        exit;
                    }
                    if (!password_verify($senha_antiga, $usuario_atual['senha'])) {
                        header("Location: " . BASE_URL . "configuracoes?erro=senha_incorreta");
                        exit;
                    }
                    if ($nova_senha !== $confirmar_senha) {
                        header("Location: " . BASE_URL . "configuracoes?erro=senhas_nao_coincidem");
                        exit;
                    }
                    $sql .= ", senha = ?";
                    $params[] = password_hash($nova_senha, PASSWORD_BCRYPT);
                }

                $sql .= " WHERE id = ?";
                $params[] = $usuario_id;

                $stmtUpdate = $pdo->prepare($sql);
                $stmtUpdate->execute($params);

                $_SESSION['usuario_nome'] = $nome;
                header("Location: " . BASE_URL . "configuracoes?msg=sucesso");
                exit;

            } catch (\Exception $e) {
                error_log("Erro ao salvar configurações: " . $e->getMessage());
                header("Location: " . BASE_URL . "configuracoes?erro=geral");
                exit;
            }
        }
    }
}