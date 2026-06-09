<?php
// autoload.php

spl_autoload_register(function ($class) {
    // Transforma o namespace da classe no caminho da pasta
    // Ex: App\Core\Database vira app/Core/Database.php
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/app/';

    // Verifica se a classe usa o nosso prefixo "App\"
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return; // Se não for da nossa estrutura, ignora
    }

    // Pega o nome relativo da classe
    $relative_class = substr($class, $len);

    // Substitui as barras do namespace por barras de diretório e adiciona .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // Se o arquivo existir, faz o require
    if (file_exists($file)) {
        require $file;
    }
});