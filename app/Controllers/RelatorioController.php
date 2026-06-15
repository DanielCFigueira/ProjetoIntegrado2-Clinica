<?php
namespace App\Controllers;

use App\Models\Relatorio;
use DateTime;
use DateInterval;
use DatePeriod;

class RelatorioController {
    public function index() {
        require_once 'config/session.php';
        require_once 'config/seguranca.php';
        require_once 'config/controle_acesso.php';
        
        if (!is_admin()) {
            header('Location: ' . BASE_URL . 'index.php');
            exit;
        }

        $data_inicio = $_GET['inicio'] ?? date('Y-m-01');
        $data_fim = $_GET['fim'] ?? date('Y-m-t');
        
        $itensPorPagina = 10;
        $pagina_at = isset($_GET['pagina_at']) ? max(1, (int)$_GET['pagina_at']) : 1;
        $offset_at = ($pagina_at - 1) * $itensPorPagina;
        $pagina_de = isset($_GET['pagina_de']) ? max(1, (int)$_GET['pagina_de']) : 1;
        $offset_de = ($pagina_de - 1) * $itensPorPagina;

        $model = new Relatorio();
        
        try {
            $financas = $model->getTotaisFinancas($data_inicio, $data_fim);
            $despesas = $model->getTotalDespesas($data_inicio, $data_fim);

            $totalRegistrosAtendimentos = $model->countAtendimentos($data_inicio, $data_fim);
            $totalPaginasAtendimentos = ceil($totalRegistrosAtendimentos / $itensPorPagina);
            $atendimentos = $model->getAtendimentosComPaginacao($data_inicio, $data_fim, $itensPorPagina, $offset_at);

            $totalRegistrosDespesas = $model->countDespesas($data_inicio, $data_fim);
            $totalPaginasDespesas = ceil($totalRegistrosDespesas / $itensPorPagina);
            $listaDespesas = $model->getDespesasComPaginacao($data_inicio, $data_fim, $itensPorPagina, $offset_de);

            // Dados Chart.js
            $dadosGrafico = $model->getDadosGraficoEvolucao($data_inicio, $data_fim);
            $dadosLiquidoGrafico = $model->getDadosLiquidoGrafico($data_inicio, $data_fim);
            
            $labels = []; $faturamentoData = []; $despesaData = []; $lucroLiquidoData = []; $taxasData = [];
            $begin = new DateTime($data_inicio);
            $end = new DateTime($data_fim);
            $end->setTime(23, 59, 59);
            $interval = DateInterval::createFromDateString('1 day');
            $period = new DatePeriod($begin, $interval, $end);

            foreach ($period as $dt) {
                $data = $dt->format('Y-m-d');
                $labels[] = $dt->format('d/m');
                $faturamento = $dadosGrafico[$data]['faturamento'] ?? 0;
                $despesa = $dadosGrafico[$data]['despesa'] ?? 0;
                $liquido = $dadosLiquidoGrafico[$data]['liquido'] ?? 0;

                $faturamentoData[] = $faturamento;
                $despesaData[] = $despesa;
                $lucroLiquidoData[] = $liquido - $despesa;
                $taxasData[] = $dadosLiquidoGrafico[$data]['taxas'] ?? 0;
            }

            $dadosPagamentos = $model->getDadosGraficoPagamentos($data_inicio, $data_fim);
            $pagamentoLabels = []; $pagamentoData = [];
            if ($dadosPagamentos) {
                $pagamentoLabels = array_map('ucfirst', array_keys($dadosPagamentos));
                $pagamentoData = array_values($dadosPagamentos);
            }

        } catch (\Exception $e) {
            $erro_msg = "Erro ao gerar relatório: " . $e->getMessage();
            $financas = ['bruto' => 0, 'liquido' => 0]; $despesas = 0; $atendimentos = []; $listaDespesas = [];
            $totalPaginasAtendimentos = 0; $totalPaginasDespesas = 0;
            $labels = []; $faturamentoData = []; $despesaData = []; $lucroLiquidoData = []; $taxasData = [];
            $pagamentoLabels = []; $pagamentoData = [];
        }

        require_once 'app/Views/relatorios/index.php';
    }

    public function diario() {
        require_once 'app/Views/relatorios/relatorio_diario.php';
    }

    public function dentistas() {
        require_once 'app/Views/relatorios/relatorio_dentistas.php';
    }

    public function paciente() {
        require_once 'app/Views/relatorios/relatorio_paciente3.php';
    }

    public function procedimentos() {
        require_once 'app/Views/relatorios/relatorio_procedimentos.php';
    }

}
