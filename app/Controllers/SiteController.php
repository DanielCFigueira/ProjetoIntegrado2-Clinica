<?php
namespace App\Controllers;

class SiteController {
    public function index() {
        require_once 'config/session.php';
        require_once 'config/app.php';
        
        // Se já estiver logado, não precisa ver a página pública, vai pro Dashboard
        if(isset($_SESSION['usuario_id'])) { 
            header("Location: " . BASE_URL . "index.php"); 
            exit; 
        }

        require_once 'app/Views/site/index.php';
    }
}