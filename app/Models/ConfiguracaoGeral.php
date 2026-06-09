<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class ConfiguracaoGeral {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    // Busca a única linha da tabela
    public function getConfiguracoes() {
        $stmt = $this->pdo->query("SELECT * FROM configuracoes_gerais WHERE id = 1");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Atualiza todas as taxas de uma vez
    public function atualizar($dados) {
        $sql = "UPDATE configuracoes_gerais SET 
                taxa_debito = ?, taxa_credito_avista = ?, taxa_credito_2 = ?, taxa_credito_3 = ?, 
                taxa_credito_4 = ?, taxa_credito_5 = ?, taxa_credito_6 = ?, taxa_credito_7 = ?, 
                taxa_credito_8 = ?, taxa_credito_9 = ?, taxa_credito_10 = ?, taxa_credito_11 = ?, 
                taxa_credito_12 = ?, comissao_geral_base = ?, comissao_geral_bonus = ?, 
                meta_faturamento_geral = ?, comissao_especializado = ?, comissao_canal = ?, 
                comissao_protese = ? 
                WHERE id = 1";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $dados['taxa_debito'], $dados['taxa_credito_avista'], $dados['taxa_credito_2'], $dados['taxa_credito_3'], 
            $dados['taxa_credito_4'], $dados['taxa_credito_5'], $dados['taxa_credito_6'], $dados['taxa_credito_7'], 
            $dados['taxa_credito_8'], $dados['taxa_credito_9'], $dados['taxa_credito_10'], $dados['taxa_credito_11'], 
            $dados['taxa_credito_12'], $dados['comissao_geral_base'], $dados['comissao_geral_bonus'], 
            $dados['meta_faturamento_geral'], $dados['comissao_especializado'], $dados['comissao_canal'], 
            $dados['comissao_protese']
        ]);
    }
}