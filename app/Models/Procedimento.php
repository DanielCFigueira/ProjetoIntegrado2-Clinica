<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Procedimento {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function listarTodos() {
        $stmt = $this->pdo->query("SELECT * FROM procedimentos ORDER BY nome ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function salvar($nome, $categoria, $tipo, $valor_base) {
        $stmt = $this->pdo->prepare("INSERT INTO procedimentos (nome, categoria, tipo, valor_base) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$nome, $categoria, $tipo, $valor_base]);
    }

    public function excluir($id) {
        // Verifica conflitos com atendimentos
        $stmtCheck = $this->pdo->prepare("SELECT COUNT(*) FROM atendimento_procedimentos WHERE id_procedimento = ?");
        $stmtCheck->execute([$id]);
        if ($stmtCheck->fetchColumn() > 0) {
            return false;
        }

        $stmt = $this->pdo->prepare("DELETE FROM procedimentos WHERE id = ?");
        return $stmt->execute([$id]);
    }
}