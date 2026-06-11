<?php
require 'C:/xampp/htdocs/ProjetoIntegrado2/app/Core/Database.php';
try {
    $pdo = \App\Core\Database::getInstance();
    $stmt = $pdo->query('SELECT * FROM configuracoes_gerais');
    print_r($stmt->fetch());
} catch(Exception $e) {
    echo $e->getMessage();
}
