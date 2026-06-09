<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Auth {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function validarLogin($login, $senha) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE login = ?");
        $stmt->execute([$login]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            return $usuario;
        }
        return false;
    }
}