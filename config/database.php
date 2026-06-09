<?php
// config/database.php - ARQUIVO DE TRANSIÇÃO (Será apagado no futuro)

// Carrega o nosso autoloader
require_once __DIR__ . '/../autoload.php';

use App\Core\Database;

// Disponibiliza a variável $pdo da forma exata como o sistema antigo espera
$pdo = Database::getInstance();
?>