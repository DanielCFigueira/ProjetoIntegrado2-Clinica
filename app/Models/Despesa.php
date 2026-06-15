<?php
namespace App\Models;

use App\Core\Database;
use PDO;
use Exception;

class Despesa {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function listarTodas() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM despesas ORDER BY data_despesa DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function salvar($descricao, $valor, $tipo, $data_despesa) {
        $stmt = $this->pdo->prepare("INSERT INTO despesas (descricao, valor, tipo, data_despesa) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$descricao, $valor, $tipo, $data_despesa]);
    }

    public function excluir($id) {
        $stmt = $this->pdo->prepare("DELETE FROM despesas WHERE id = ?");
        return $stmt->execute([$id]);
    }
}