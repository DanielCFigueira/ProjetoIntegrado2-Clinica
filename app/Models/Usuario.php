<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Usuario {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function listarTodos() {
        $stmt = $this->pdo->query("SELECT id, nome, login, perfil FROM usuarios ORDER BY nome ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function salvar($nome, $login, $senha, $perfil) {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        try {
            $stmt = $this->pdo->prepare("INSERT INTO usuarios (nome, login, senha, perfil) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nome, $login, $senhaHash, $perfil]);
            return 'sucesso';
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) { // Erro de duplicidade do MySQL
                return 'login_duplicado';
            }
            throw $e;
        }
    }

    public function excluir($id) {
        // Verifica se é o próprio usuário (não pode se excluir)
        if ($id == $_SESSION['usuario_id']) {
            return 'autoexclusao';
        }

        // Verifica vínculos com atendimentos (para dentistas/recepcionistas)
        $stmtCheck = $this->pdo->prepare("SELECT COUNT(*) FROM atendimentos WHERE id_dentista = ?");
        $stmtCheck->execute([$id]);
        if ($stmtCheck->fetchColumn() > 0) {
            return 'conflito';
        }

        $stmt = $this->pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return 'sucesso';
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT id, nome, login, perfil FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizar($id, $nome, $login, $senha, $perfil) {
        try {
            if (empty($senha)) {
                $stmt = $this->pdo->prepare("UPDATE usuarios SET nome = ?, login = ?, perfil = ? WHERE id = ?");
                $stmt->execute([$nome, $login, $perfil, $id]);
            } else {
                $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $this->pdo->prepare("UPDATE usuarios SET nome = ?, login = ?, senha = ?, perfil = ? WHERE id = ?");
                $stmt->execute([$nome, $login, $senhaHash, $perfil, $id]);
            }
            return 'sucesso';
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) {
                return 'login_duplicado';
            }
            throw $e;
        }
    }
}
