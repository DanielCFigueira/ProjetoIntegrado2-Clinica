<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Relatorio {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function getTotaisFinancas($data_inicio, $data_fim) {
        $stmtBruto = $this->pdo->prepare("
            SELECT SUM(ap.valor_procedimento) 
            FROM atendimentos a 
            JOIN atendimento_procedimentos ap ON a.id = ap.id_atendimento
            WHERE a.data_atendimento BETWEEN ? AND ? AND a.status_pagamento = 'pago' AND ap.status_execucao = 'feito'
        ");
        $stmtBruto->execute([$data_inicio . ' 00:00:00', $data_fim . ' 23:59:59']);
        $bruto = $stmtBruto->fetchColumn() ?? 0;

        $stmtLiquido = $this->pdo->prepare("
            SELECT SUM(valor_liquido_clinica) 
            FROM atendimentos 
            WHERE data_atendimento BETWEEN ? AND ? AND status_pagamento = 'pago'
        ");
        $stmtLiquido->execute([$data_inicio . ' 00:00:00', $data_fim . ' 23:59:59']);
        $liquido = $stmtLiquido->fetchColumn() ?? 0;

        return ['bruto' => $bruto, 'liquido' => $liquido];
    }

    public function getTotalDespesas($data_inicio, $data_fim) {
        $stmt = $this->pdo->prepare("SELECT SUM(valor) FROM despesas WHERE data_despesa BETWEEN ? AND ?");
        $stmt->execute([$data_inicio, $data_fim]);
        return $stmt->fetchColumn() ?? 0;
    }

    public function countAtendimentos($data_inicio, $data_fim) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(DISTINCT a.id) 
            FROM atendimentos a 
            WHERE a.data_atendimento BETWEEN ? AND ? AND a.status_pagamento = 'pago'
        ");
        $stmt->execute([$data_inicio . ' 00:00:00', $data_fim . ' 23:59:59']);
        return $stmt->fetchColumn() ?? 0;
    }

    public function getAtendimentosComPaginacao($data_inicio, $data_fim, $limit, $offset) {
        $stmt = $this->pdo->prepare("
            SELECT 
                a.id, a.data_atendimento, p.nome as paciente_nome, a.valor_liquido_clinica, 
                u.nome as dentista, 
                GROUP_CONCAT(CASE WHEN ap.status_execucao = 'feito' THEN proc.nome END SEPARATOR ', ') as procedimento, 
                SUM(CASE WHEN ap.status_execucao = 'feito' THEN ap.valor_procedimento ELSE 0 END) as valor_bruto 
            FROM atendimentos a 
            JOIN pacientes p ON a.paciente_id = p.id
            JOIN usuarios u ON a.id_dentista = u.id 
            LEFT JOIN atendimento_procedimentos ap ON a.id = ap.id_atendimento 
            LEFT JOIN procedimentos proc ON ap.id_procedimento = proc.id 
            WHERE a.data_atendimento BETWEEN :data_inicio AND :data_fim AND a.status_pagamento = 'pago'
            GROUP BY a.id
            ORDER BY a.data_atendimento DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':data_inicio', $data_inicio . ' 00:00:00');
        $stmt->bindValue(':data_fim', $data_fim . ' 23:59:59');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countDespesas($data_inicio, $data_fim) {
        $stmt = $this->pdo->prepare("SELECT COUNT(id) FROM despesas WHERE data_despesa BETWEEN ? AND ?");
        $stmt->execute([$data_inicio, $data_fim]);
        return $stmt->fetchColumn() ?? 0;
    }

    public function getDespesasComPaginacao($data_inicio, $data_fim, $limit, $offset) {
        $stmt = $this->pdo->prepare("SELECT * FROM despesas WHERE data_despesa BETWEEN :data_inicio AND :data_fim ORDER BY data_despesa DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':data_inicio', $data_inicio);
        $stmt->bindValue(':data_fim', $data_fim);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDadosGraficoEvolucao($data_inicio, $data_fim) {
        $stmt = $this->pdo->prepare("
            SELECT dia, SUM(faturamento) as faturamento, SUM(despesa) as despesa
            FROM (
                SELECT DATE(a.data_atendimento) as dia, SUM(ap.valor_procedimento) as faturamento, 0 as despesa
                FROM atendimentos a JOIN atendimento_procedimentos ap ON a.id = ap.id_atendimento
                WHERE a.data_atendimento BETWEEN ? AND ? AND a.status_pagamento = 'pago' AND ap.status_execucao = 'feito'
                GROUP BY DATE(a.data_atendimento)
                UNION ALL
                SELECT DATE(data_despesa) as dia, 0 as faturamento, SUM(valor) as despesa
                FROM despesas WHERE data_despesa BETWEEN ? AND ? GROUP BY DATE(data_despesa)
            ) as T
            GROUP BY dia ORDER BY dia
        ");
        $stmt->execute([$data_inicio . ' 00:00:00', $data_fim . ' 23:59:59', $data_inicio, $data_fim]);
        $raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $res = [];
        foreach($raw as $r) $res[$r['dia']] = $r;
        return $res;
    }

    public function getDadosLiquidoGrafico($data_inicio, $data_fim) {
        $stmt = $this->pdo->prepare("
            SELECT DATE(data_atendimento) as dia, SUM(valor_liquido_clinica) as liquido,
                   SUM(taxa_cartao + comissao_dentista + custo_auxiliar) as taxas
            FROM atendimentos
            WHERE data_atendimento BETWEEN ? AND ? AND status_pagamento = 'pago'
            GROUP BY DATE(data_atendimento) ORDER BY dia
        ");
        $stmt->execute([$data_inicio . ' 00:00:00', $data_fim . ' 23:59:59']);
        $raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $res = [];
        foreach($raw as $r) $res[$r['dia']] = $r;
        return $res;
    }

    public function getDadosGraficoPagamentos($data_inicio, $data_fim) {
        $stmt = $this->pdo->prepare("
            SELECT forma_pagamento, SUM(valor) as total
            FROM atendimento_pagamentos ap JOIN atendimentos a ON ap.id_atendimento = a.id
            WHERE a.data_atendimento BETWEEN ? AND ? GROUP BY forma_pagamento
        ");
        $stmt->execute([$data_inicio . ' 00:00:00', $data_fim . ' 23:59:59']);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}
