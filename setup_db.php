<?php
require 'C:/xampp/htdocs/ProjetoIntegrado2/app/Core/Database.php';
try {
    $pdo = \App\Core\Database::getInstance();
    $sql = "CREATE TABLE IF NOT EXISTS `configuracoes_gerais` (
      `id` int NOT NULL AUTO_INCREMENT,
      `taxa_debito` decimal(5,4) NOT NULL DEFAULT '0.0099',
      `taxa_credito_avista` decimal(5,4) NOT NULL DEFAULT '0.0349',
      `taxa_credito_2` decimal(5,4) NOT NULL DEFAULT '0.0381',
      `taxa_credito_3` decimal(5,4) NOT NULL DEFAULT '0.0381',
      `taxa_credito_4` decimal(5,4) NOT NULL DEFAULT '0.0381',
      `taxa_credito_5` decimal(5,4) NOT NULL DEFAULT '0.0381',
      `taxa_credito_6` decimal(5,4) NOT NULL DEFAULT '0.0381',
      `taxa_credito_7` decimal(5,4) NOT NULL DEFAULT '0.0416',
      `taxa_credito_8` decimal(5,4) NOT NULL DEFAULT '0.0416',
      `taxa_credito_9` decimal(5,4) NOT NULL DEFAULT '0.0416',
      `taxa_credito_10` decimal(5,4) NOT NULL DEFAULT '0.0416',
      `taxa_credito_11` decimal(5,4) NOT NULL DEFAULT '0.0416',
      `taxa_credito_12` decimal(5,4) NOT NULL DEFAULT '0.0416',
      `comissao_geral_base` decimal(5,4) NOT NULL DEFAULT '0.2000',
      `comissao_geral_bonus` decimal(5,4) NOT NULL DEFAULT '0.2500',
      `meta_faturamento_geral` decimal(10,2) NOT NULL DEFAULT '1000.00',
      `comissao_especializado` decimal(5,4) NOT NULL DEFAULT '0.4000',
      `comissao_protese` decimal(5,4) NOT NULL DEFAULT '0.5000',
      `comissao_canal` decimal(5,4) NOT NULL DEFAULT '0.5000',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sql);
    
    $check = $pdo->query("SELECT count(*) FROM configuracoes_gerais")->fetchColumn();
    if($check == 0) {
        $pdo->exec("INSERT INTO `configuracoes_gerais` (`id`) VALUES (1)");
    }
    echo "Tabela configuracoes_gerais criada e populada com sucesso!";
} catch(Exception $e) {
    echo $e->getMessage();
}
