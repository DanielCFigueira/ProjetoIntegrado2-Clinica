<?php
namespace App\Controllers;

use App\Models\Dashboard;
use IntlDateFormatter;

class DashboardController {
       public function index() {
        require_once 'config/session.php';
        require_once 'config/seguranca.php';
        require_once 'config/controle_acesso.php';
        
        if (!is_admin() && !is_dentista() && !is_recepcionista()) {
            header('Location: ' . BASE_URL . 'login.php');
            exit;
        }

        $perfil = $_SESSION['usuario_perfil'] ?? '';

        if ($perfil === 'recepcionista') {
            $dashboardModel = new Dashboard();
            $atendimentosHoje = $dashboardModel->getAtendimentosRecepcionistaHoje();
            require_once 'app/Views/dashboard/recepcionista.php';
            exit;
        }

        if ($perfil === 'dentista') {
            $dashboardModel = new Dashboard();
            $id_dentista = $_SESSION['usuario_id'];
            $atendimentosHoje = $dashboardModel->getAtendimentosDentistaHoje($id_dentista);
            require_once 'app/Views/dashboard/dentista.php';
            exit;
        }

        // Lógica para Proprietário / Admin (Dashboard original com Gráficos)
        date_default_timezone_set('America/Sao_Paulo');
        $mes_selecionado = $_GET['mes'] ?? date('Y-m');
        if (!preg_match('/^\d{4}-\d{2}$/', $mes_selecionado)) {
            $mes_selecionado = date('Y-m');
        }

        $data_inicio = date('Y-m-01', strtotime($mes_selecionado));
        $data_fim = date('Y-m-t', strtotime($mes_selecionado));
        $mes_anterior = date('Y-m', strtotime($data_inicio . ' -1 month'));
        $mes_proximo = date('Y-m', strtotime($data_inicio . ' +1 month'));

        $dashboardModel = new Dashboard();
        
        try {
            $faturamentoBruto = $dashboardModel->getFaturamentoBruto($data_inicio, $data_fim);
            $lucroLiquido = $dashboardModel->getLucroLiquido($data_inicio, $data_fim);
            $totalDespesas = $dashboardModel->getTotalDespesas($data_inicio, $data_fim);

            $busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
            $pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
            $itensPorPagina = 10;
            $offset = ($pagina - 1) * $itensPorPagina;

            $totalRegistros = $dashboardModel->countAtendimentos($busca);
            $totalPaginas = ceil($totalRegistros / $itensPorPagina);
            $ultimosAtendimentos = $dashboardModel->getAtendimentosPaginados($busca, $itensPorPagina, $offset);
            $dadosGrafico = $dashboardModel->getFaturamentoAnual();

        } catch (\Exception $e) {
            $dadosGrafico = array_fill(1, 12, ['lucro' => 0, 'comissoes' => 0]);
            $lucroLiquido = 0; $faturamentoBruto = 0; $totalDespesas = 0;
            $ultimosAtendimentos = []; $totalPaginas = 0;
            $erro_msg = "Erro ao carregar dashboard: " . $e->getMessage();
        }

        $formatter = new IntlDateFormatter('pt_BR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'America/Sao_Paulo', IntlDateFormatter::GREGORIAN, 'MMMM \'de\' yyyy');
        $mesAtual = $formatter->format(strtotime($data_inicio));

        require_once 'app/Views/dashboard/index.php';
    }
}