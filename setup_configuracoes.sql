CREATE TABLE IF NOT EXISTS configuracoes_gerais (
    id INT(11) NOT NULL AUTO_INCREMENT,
    taxa_debito DECIMAL(10,6) NOT NULL DEFAULT 0.000000,
    taxa_credito_avista DECIMAL(10,6) NOT NULL DEFAULT 0.000000,
    taxa_credito_2 DECIMAL(10,6) NOT NULL DEFAULT 0.000000,
    taxa_credito_3 DECIMAL(10,6) NOT NULL DEFAULT 0.000000,
    taxa_credito_4 DECIMAL(10,6) NOT NULL DEFAULT 0.000000,
    taxa_credito_5 DECIMAL(10,6) NOT NULL DEFAULT 0.000000,
    taxa_credito_6 DECIMAL(10,6) NOT NULL DEFAULT 0.000000,
    taxa_credito_7 DECIMAL(10,6) NOT NULL DEFAULT 0.000000,
    taxa_credito_8 DECIMAL(10,6) NOT NULL DEFAULT 0.000000,
    taxa_credito_9 DECIMAL(10,6) NOT NULL DEFAULT 0.000000,
    taxa_credito_10 DECIMAL(10,6) NOT NULL DEFAULT 0.000000,
    taxa_credito_11 DECIMAL(10,6) NOT NULL DEFAULT 0.000000,
    taxa_credito_12 DECIMAL(10,6) NOT NULL DEFAULT 0.000000,
    comissao_geral_base DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
    comissao_geral_bonus DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
    meta_faturamento_geral DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    comissao_especializado DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
    comissao_canal DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
    comissao_protese DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO configuracoes_gerais (
    id, taxa_debito, taxa_credito_avista, taxa_credito_2, taxa_credito_3, taxa_credito_4, 
    taxa_credito_5, taxa_credito_6, taxa_credito_7, taxa_credito_8, taxa_credito_9, 
    taxa_credito_10, taxa_credito_11, taxa_credito_12, comissao_geral_base, 
    comissao_geral_bonus, meta_faturamento_geral, comissao_especializado, 
    comissao_canal, comissao_protese
) VALUES (
    1, 0.009875, 0.030000, 0.044330, 0.052700, 0.061000, 
    0.069200, 0.077310, 0.085000, 0.092000, 0.099000, 
    0.107600, 0.115000, 0.122000, 0.2000, 
    0.3000, 10000.00, 0.5000, 
    0.1000, 0.1000
) ON DUPLICATE KEY UPDATE id=id;
