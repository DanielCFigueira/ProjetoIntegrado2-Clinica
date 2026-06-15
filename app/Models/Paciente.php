<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Paciente {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function contarTodos($busca = '') {
        $sql = "SELECT COUNT(id) FROM pacientes WHERE 1=1";
        $params = [];
        if (!empty($busca)) {
            $sql .= " AND (nome LIKE :busca OR cpf LIKE :busca)";
            $params[':busca'] = "%$busca%";
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public function listarComPaginacao($busca = '', $limit = 10, $offset = 0) {
        $sql = "SELECT * FROM pacientes WHERE 1=1";
        if (!empty($busca)) {
            $sql .= " AND (nome LIKE :busca OR cpf LIKE :busca)";
        }
        $sql .= " ORDER BY nome ASC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->pdo->prepare($sql);
        if (!empty($busca)) {
            $stmt->bindValue(':busca', "%$busca%", PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function salvar($dados) {
        $sql = "INSERT INTO pacientes (nome, cpf, data_nascimento, telefone, email, cep, endereco, numero, bairro, cidade, estado) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $dados['nome'], $dados['cpf'], $dados['data_nascimento'], $dados['telefone'], 
            $dados['email'], $dados['cep'], $dados['endereco'], $dados['numero'], 
            $dados['bairro'], $dados['cidade'], $dados['estado']
        ]);
    }

    public function excluir($id) {
        // Verifica se o paciente tem atendimentos atrelados antes de excluir
        $stmtCheck = $this->pdo->prepare("SELECT COUNT(*) FROM atendimentos WHERE paciente_id = ?");
        $stmtCheck->execute([$id]);
        if ($stmtCheck->fetchColumn() > 0) {
            return false; // Conflito, não pode excluir
        }

        $stmt = $this->pdo->prepare("DELETE FROM pacientes WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM pacientes WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizar($id, $dados) {
        $sql = "UPDATE pacientes SET nome = ?, cpf = ?, data_nascimento = ?, telefone = ?, email = ?, cep = ?, endereco = ?, numero = ?, bairro = ?, cidade = ?, estado = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $dados['nome'], $dados['cpf'], $dados['data_nascimento'], $dados['telefone'], 
            $dados['email'], $dados['cep'], $dados['endereco'], $dados['numero'], 
            $dados['bairro'], $dados['cidade'], $dados['estado'], $id
        ]);
    }
}