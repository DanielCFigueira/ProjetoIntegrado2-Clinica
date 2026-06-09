<?php
namespace App\Core;

class Router {
    protected $controller = 'DashboardController'; // Página inicial padrão
    protected $method = 'index'; // Método padrão
    protected $params = [];

    public function __construct() {
        $url = $this->parseUrl();

        // 1. Dicionario de Rotas (De/para)
        // Ensina o sistema qual Controller carregar quando o usuário digitar a URL
        $rotas = [
            'login' => 'AuthController',
            'pacientes' => 'PacienteController',
            'procedimentos' => 'ProcedimentoController',
            'despesas' => 'DespesaController',
            'usuarios' => 'UsuarioController',
            'relatorios' => 'RelatorioController',
            'configuracoes_gerais' => 'ConfiguracaoGeralController',
            'atendimentos' => 'AtendimentoController',
            'configuracoes' => 'ConfiguracaoController'
        ];

        // 2. Descobrir qual página foi pedida (ex: localhost/pacientes)
        if (isset($url[0])) {
            $rotaSolicitada = strtolower(str_replace('.php', '', $url[0]));

            if (array_key_exists($rotaSolicitada, $rotas)) {
                $this->controller = $rotas[$rotaSolicitada];
                unset($url[0]); // Limpa a URL depois de achar o controller
            } else {
                // Rotas antigas temporárias (Sub-relatórios, etc.)
                $this->controller = 'DashboardController';
            }
        }

        // 3. Carregar o Controller
        $classeController = '\\App\\Controllers\\' . $this->controller;
        if (class_exists($classeController)){
            $this->controller = new $classeController;
        } else {
            // Prevenção de erros caso a classe do controller não exista
            $classeController = '\\App\\Controllers\\DashboardController';
            $this->controller = new $classeController;
        }

        // 4. Descobrir qual método foi pedido (ex: localhost/pacientes/editar)
        if (isset($url[1])) {
            if (method_exists($this->controller, $url[1])) {
                $this->method = $url[1];
                unset($url[1]); // Limpa a URL depois de achar o método
            }
        }

        // 5. Pegar os parâmetros (ex: localhost/pacientes/editar/123)
        // O número '5' (se houvesse) iria para dentro do $this->params
        $this->params = $url ? array_values($url) : [];

        // 6. Chamar o método com os parâmetros
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    /**
     * Limpa e divide a URL digitada
     */
    private function parseUrl() {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return [];
    }
}