<?php
namespace App\Controllers;

use App\Models\ConfiguracaoGeral;

class ConfiguracaoGeralController {

    public function index() {
        require_once 'config/session.php';
        require_once 'config/seguranca.php';
        require_once 'config/controle_acesso.php';
        
        // Apenas o dono/admin pode acessar e mudar as taxas
        if (!is_admin()) {
            header('Location: ' . BASE_URL . 'index.php');
            exit;
        }

        $model = new ConfiguracaoGeral();
        $configuracoes = $model->getConfiguracoes();

        require_once 'app/Views/configuracoes/geral.php';
    }

    public function salvar() {
        require_once __DIR__ . '/../../config/session.php';
        require_once __DIR__ . '/../../config/controle_acesso.php';
        require_once __DIR__ . '/../../config/app.php';

        if (!is_admin() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'index.php');
            exit;
        }

        // Função auxiliar para converter do HTML (ex: 2.5) para o Banco (0.025)
        $converterParaDecimal = function($valorDigitado) {
            $valorFormatado = str_replace(',', '.', $valorDigitado);
            return (float)$valorFormatado / 100;
        };

        // Valores brutos do formulário (que o admin digitou na tela em "reais" ou "porcentagem inteira")
        $dados = [
            'clinica_nome' => trim($_POST['clinica_nome'] ?? ''),
            'clinica_endereco' => trim($_POST['clinica_endereco'] ?? ''),
            'clinica_cnpj' => trim($_POST['clinica_cnpj'] ?? ''),
            'clinica_telefone' => trim($_POST['clinica_telefone'] ?? ''),
            'taxa_debito' => $converterParaDecimal($_POST['taxa_debito']),
            'taxa_credito_avista' => $converterParaDecimal($_POST['taxa_credito_avista']),
            'taxa_credito_2' => $converterParaDecimal($_POST['taxa_credito_2']),
            'taxa_credito_3' => $converterParaDecimal($_POST['taxa_credito_3']),
            'taxa_credito_4' => $converterParaDecimal($_POST['taxa_credito_4']),
            'taxa_credito_5' => $converterParaDecimal($_POST['taxa_credito_5']),
            'taxa_credito_6' => $converterParaDecimal($_POST['taxa_credito_6']),
            'taxa_credito_7' => $converterParaDecimal($_POST['taxa_credito_7']),
            'taxa_credito_8' => $converterParaDecimal($_POST['taxa_credito_8']),
            'taxa_credito_9' => $converterParaDecimal($_POST['taxa_credito_9']),
            'taxa_credito_10' => $converterParaDecimal($_POST['taxa_credito_10']),
            'taxa_credito_11' => $converterParaDecimal($_POST['taxa_credito_11']),
            'taxa_credito_12' => $converterParaDecimal($_POST['taxa_credito_12']),
            
            'comissao_geral_base' => $converterParaDecimal($_POST['comissao_geral_base']),
            'comissao_geral_bonus' => $converterParaDecimal($_POST['comissao_geral_bonus']),
            'meta_faturamento_geral' => (float)str_replace(',', '.', $_POST['meta_faturamento_geral']), // Esse não divide por 100 pois é dinheiro, não porcentagem
            'comissao_especializado' => $converterParaDecimal($_POST['comissao_especializado']),
            'comissao_canal' => $converterParaDecimal($_POST['comissao_canal']),
            'comissao_protese' => $converterParaDecimal($_POST['comissao_protese'])
        ];

        $model = new ConfiguracaoGeral();
        $model->atualizar($dados);

        // Volta para a tela com mensagem de sucesso
        header('Location: ' . BASE_URL . 'configuracoes_gerais?msg=sucesso');
        exit;
    }
}