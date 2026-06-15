<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Atendimento {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function getDetalhesCompletos($id) {
        $stmt = $this->pdo->prepare("
            SELECT 
                a.*, 
                u.nome as dentista_nome,
                p.nome as paciente_nome,
                p.cpf as paciente_cpf,
                p.telefone as paciente_telefone,
                p.email as paciente_email,
                p.endereco, p.numero, p.bairro, p.cidade, p.estado
            FROM atendimentos a
            JOIN usuarios u ON a.id_dentista = u.id
            JOIN pacientes p ON a.paciente_id = p.id
            WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getProcedimentos($id) {
        $stmt = $this->pdo->prepare("
            SELECT p.nome, ap.quantidade, ap.valor_procedimento, p.categoria
            FROM atendimento_procedimentos ap
            JOIN procedimentos p ON ap.id_procedimento = p.id
            WHERE ap.id_atendimento = ? AND ap.status_execucao = 'feito'
        ");
        $stmt->execute([$id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPagamentos($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM atendimento_pagamentos WHERE id_atendimento = ?");
        $stmt->execute([$id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}