<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Dashboard {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function getFaturamentoBruto($data_inicio, $data_fim) {
        $stmt = $this->pdo->prepare("
            SELECT SUM(ap.valor_procedimento)
            FROM atendimento_procedimentos ap
            JOIN atendimentos a ON ap.id_atendimento = a.id
            WHERE a.data_atendimento BETWEEN ? AND ? AND a.status_pagamento = 'pago' AND ap.status_execucao = 'feito'
        ");
        $stmt->execute([$data_inicio . ' 00:00:00', $data_fim . ' 23:59:59']);
        return $stmt->fetchColumn() ?? 0;
    }

    public function getLucroLiquido($data_inicio, $data_fim) {
        $stmt = $this->pdo->prepare("
            SELECT SUM(valor_liquido_clinica) 
            FROM atendimentos 
            WHERE data_atendimento BETWEEN ? AND ? AND status_pagamento = 'pago'
        ");
        $stmt->execute([$data_inicio . ' 00:00:00', $data_fim . ' 23:59:59']);
        return $stmt->fetchColumn() ?? 0;
    }

    public function getTotalDespesas($data_inicio, $data_fim) {
        $stmt = $this->pdo->prepare("SELECT SUM(valor) FROM despesas WHERE data_despesa BETWEEN ? AND ?");
        $stmt->execute([$data_inicio, $data_fim]);
        return $stmt->fetchColumn() ?? 0;
    }

    public function countAtendimentos($busca) {
        $sql = "SELECT COUNT(DISTINCT a.id) FROM atendimentos a JOIN pacientes p ON a.paciente_id = p.id WHERE a.status_pagamento = 'pago'";
        $params = [];
        if (!empty($busca)) {
            $sql .= " AND p.nome LIKE ?";
            $params[] = "%$busca%";
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public function getAtendimentosPaginados($busca, $limit, $offset) {
        $sql = "
            SELECT 
                a.id, a.data_atendimento, p.nome AS paciente_nome, a.status_pagamento, a.taxa_cartao, 
                a.valor_liquido_clinica, a.custo_auxiliar, a.comissao_dentista, a.url_arquivo, u.nome AS dentista,
                SUM(CASE WHEN ap.status_execucao = 'feito' THEN ap.valor_procedimento ELSE 0 END) AS valor_bruto_total,
                GROUP_CONCAT(CASE WHEN ap.status_execucao = 'feito' THEN proc.nome END SEPARATOR ', ') AS procedimentos
            FROM atendimentos a
            JOIN pacientes p ON a.paciente_id = p.id
            JOIN usuarios u ON a.id_dentista = u.id
            LEFT JOIN atendimento_procedimentos ap ON a.id = ap.id_atendimento
            LEFT JOIN procedimentos proc ON ap.id_procedimento = proc.id
            WHERE a.status_pagamento = 'pago'
        ";
        $params = [];
        if (!empty($busca)) {
            $sql .= " AND p.nome LIKE ?";
            $params[] = "%$busca%";
        }
        $sql .= " GROUP BY a.id ORDER BY a.data_atendimento DESC LIMIT $limit OFFSET $offset";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getFaturamentoAnual() {
        $anoAtual = date('Y');
        $stmt = $this->pdo->prepare("
            SELECT MONTH(data_atendimento) as mes, 
                   SUM(valor_liquido_clinica) as lucro, 
                   SUM(taxa_cartao + comissao_dentista + custo_auxiliar) as taxas
            FROM atendimentos 
            WHERE YEAR(data_atendimento) = ? AND status_pagamento = 'pago'
            GROUP BY MONTH(data_atendimento)
            ORDER BY MONTH(data_atendimento) ASC
        ");
        $stmt->execute([$anoAtual]);
        $resultados = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Prepara os 12 meses (colocando zero onde não houver movimento ainda)
        $dadosAnuais = array_fill(1, 12, ['lucro' => 0, 'taxas' => 0]);
        foreach ($resultados as $row) {
            $dadosAnuais[(int)$row['mes']] = [
                'lucro' => (float)$row['lucro'],
                'taxas' => (float)$row['taxas']
            ];
        }
        return $dadosAnuais;
    }

    public function getAtendimentosDentistaHoje($id_dentista) {
        $data_hoje = date('Y-m-d');
        $stmt = $this->pdo->prepare("
            SELECT 
                a.id as atendimento_id,
                a.data_atendimento,
                p.nome as paciente_nome,
                p.telefone,
                ap.id as procedimento_id,
                proc.nome as procedimento_nome,
                ap.status_execucao
            FROM atendimentos a
            JOIN pacientes p ON a.paciente_id = p.id
            LEFT JOIN atendimento_procedimentos ap ON a.id = ap.id_atendimento
            LEFT JOIN procedimentos proc ON ap.id_procedimento = proc.id
            WHERE a.id_dentista = ? AND DATE(a.data_atendimento) = ?
            ORDER BY a.data_atendimento ASC
        ");
        $stmt->execute([$id_dentista, $data_hoje]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAtendimentosRecepcionistaHoje() {
        $data_hoje = date('Y-m-d');
        $stmt = $this->pdo->prepare("
            SELECT 
                a.id as atendimento_id,
                a.data_atendimento,
                p.nome as paciente_nome,
                p.telefone,
                u.nome as dentista_nome,
                a.status_pagamento,
                a.valor_total
            FROM atendimentos a
            JOIN pacientes p ON a.paciente_id = p.id
            LEFT JOIN usuarios u ON a.id_dentista = u.id
            WHERE DATE(a.data_atendimento) = ?
            ORDER BY a.data_atendimento ASC
        ");
        $stmt->execute([$data_hoje]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
        public function getAtendimentosDentistaHoje($id_dentista) {
        $data_hoje = date('Y-m-d');
        $stmt = $this->pdo->prepare("
            SELECT 
                a.id as atendimento_id,
                a.data_atendimento,
                p.nome as paciente_nome,
                p.telefone,
                ap.id as procedimento_id,
                proc.nome as procedimento_nome,
                ap.status_execucao
            FROM atendimentos a
            JOIN pacientes p ON a.paciente_id = p.id
            LEFT JOIN atendimento_procedimentos ap ON a.id = ap.id_atendimento
            LEFT JOIN procedimentos proc ON ap.id_procedimento = proc.id
            WHERE a.id_dentista = ? AND DATE(a.data_atendimento) = ?
            ORDER BY a.data_atendimento ASC
        ");
        $stmt->execute([$id_dentista, $data_hoje]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAtendimentosRecepcionistaHoje() {
        $data_hoje = date('Y-m-d');
        $stmt = $this->pdo->prepare("
            SELECT 
                a.id as atendimento_id,
                a.data_atendimento,
                p.nome as paciente_nome,
                p.telefone,
                u.nome as dentista_nome,
                a.status_pagamento,
                a.valor_total
            FROM atendimentos a
            JOIN pacientes p ON a.paciente_id = p.id
            LEFT JOIN usuarios u ON a.id_dentista = u.id
            WHERE DATE(a.data_atendimento) = ?
            ORDER BY a.data_atendimento ASC
        ");
        $stmt->execute([$data_hoje]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}