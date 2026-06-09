<?php
// index.php - Ponto de entrada único do sistema (Front Controller)

// 1. Carrega as configurações essenciais e autoload
require_once 'autoload.php';
require_once 'config/session.php';
require_once 'config/app.php';
require_once 'config/controle_acesso.php';
require_once 'config/seguranca.php';

use App\Core\Router;

// 2. Inicia o motor principal do sistema (Router)
// A partir desta linha, o Router vai analisar a URL, descobrir qual Controller e método chamar, e passar os parâmetros
$app = new Router();
?>