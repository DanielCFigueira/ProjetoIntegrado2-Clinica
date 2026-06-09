<?php
namespace App\Controllers;

use App\Models\Atendimento;

class AtendimentoController {

    public function detalhes() {
        require_once 'config/session.php';
        require_once 'config/seguranca.php';
        require_once 'config/controle_acesso.php';
        require_once 'config/app.php';

        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'login.php');
            exit;
        }

        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ' . BASE_URL . 'index.php');
            exit;
        }

        $model = new Atendimento();
        $atendimento = $model->getDetalhesCompletos($id);

        if (!$atendimento) {
            $erro = "Atendimento não encontrado.";
            require_once 'app/Views/atendimentos/detalhes.php';
            exit;
        }

        if (!is_admin() && !is_recepcionista() && $_SESSION['user_id'] != $atendimento['id_dentista']) {
             header('Location: ' . BASE_URL . 'index.php');
             exit;
        }

        $procedimentos = $model->getProcedimentos($id);
        $pagamentos = $model->getPagamentos($id);

        require_once 'app/Views/atendimentos/detalhes.php';
    }

    public function recibo() {
        require_once 'config/session.php';
        require_once 'config/app.php';

        if (!isset($_GET['id'])) {
            die("ID do atendimento não fornecido.");
        }

        $id = $_GET['id'];
        $model = new Atendimento();
        $atendimento = $model->getDetalhesCompletos($id);

        if (!$atendimento) {
            die("Atendimento não encontrado.");
        }

        // Soma total
        $atendimento['valor_total'] = 0;
        $procedimentos = $model->getProcedimentos($id);
        foreach($procedimentos as $proc) {
            $atendimento['valor_total'] += $proc['valor_procedimento'];
        }

        // Dados da clínica (exemplo, idealmente viria de um config)
        $clinica_nome = "Clínica Odontológica Prev Dentistas";
        $clinica_endereco = "Rua União 1, Esquina com a Rua D - Atalaia, Ananindeua - PA, 67013-350";
        $clinica_cnpj = "29.249738/0001-79";
        $clinica_telefone = "(91) 98306-7459";
        
        $dentista_nome = $atendimento['dentista_nome'] ?? 'Não informado';

        require_once 'app/Views/atendimentos/recibo.php';
    }
    
    public function novo() {
        require_once 'views/novo_atendimento3.php';
    }
    
    public function pagar() {
        require_once 'views/confirmar_pagamento.php';
    }

    private function sendJsonError($message, $code = 400) {
        if (!headers_sent()) {
            header('Content-Type: application/json');
            http_response_code($code);
        }
        echo json_encode(['sucesso' => false, 'erro' => $message]);
        exit;
    }

    public function salvarAjax() {
        require_once 'config/session.php';
        require_once 'config/app.php';
        require_once 'config/controle_acesso.php';

        $pdo = \App\Core\Database::getInstance();

        // Garantir o fuso horário correto para funções de data (NOW, date)
        date_default_timezone_set('America/Sao_Paulo');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pdo->beginTransaction();
            try {
                // --- INÍCIO: Deletar procedimentos pendentes que foram finalizados agora ---
                if (!empty($_POST['procedimentos_a_deletar'])) {
                    $idsParaDeletar = $_POST['procedimentos_a_deletar'];
                    $idsParaDeletar = array_filter($idsParaDeletar, 'is_numeric');
                    
                    if (count($idsParaDeletar) > 0) {
                        $inQuery = implode(',', array_fill(0, count($idsParaDeletar), '?'));
                        $stmtDelete = $pdo->prepare("DELETE FROM atendimento_procedimentos WHERE id IN ($inQuery)");
                        $stmtDelete->execute($idsParaDeletar);
                    }
                }
                // --- FIM: Deleção ---

                // --- INÍCIO: Lógica para Regra de Comissão ---
                $idDentistaCalculo = $_POST['id_dentista'] ?? null;
                $mesAtual = date('m');
                $anoAtual = date('Y');
                
                $faturamentoBrutoMensal = 0;
                if ($idDentistaCalculo) {
                    $faturamentoBrutoMensal = \App\Models\Financeiro::calcularFaturamentoMensalDentista($idDentistaCalculo, $mesAtual, $anoAtual);
                }
                // --- FIM: Lógica para Regra de Comissão ---

                // 1. Receber dados do formulário
                $pacienteId = !empty($_POST['paciente_id']) ? trim($_POST['paciente_id']) : null;
                $pacienteNome = trim($_POST['paciente_nome'] ?? '');
                $idDentista = $_POST['id_dentista'] ?? null;
                $procedimentosInput = $_POST['procedimentos'] ?? [];

                // Validações básicas
                if ((empty($pacienteId) && empty($pacienteNome)) || empty($idDentista) || empty($procedimentosInput['id'] ?? [])) {
                    throw new \Exception("Erro: Paciente, dentista e pelo menos um procedimento são obrigatórios.");
                }

                // Bloco para obter/criar o ID do paciente
                if (!$pacienteId) {
                    $stmtPaciente = $pdo->prepare("INSERT INTO pacientes (nome) VALUES (?)");
                    $stmtPaciente->execute([$pacienteNome]);
                    $pacienteId = $pdo->lastInsertId();
                } else {
                     $stmtNome = $pdo->prepare("SELECT nome FROM pacientes WHERE id = ?");
                     $stmtNome->execute([$pacienteId]);
                     $pacienteNome = $stmtNome->fetchColumn();
                }

                if (!$pacienteId) {
                    throw new \Exception("Falha ao obter o ID do paciente.");
                }

                // Lógica de Upload
                $urlArquivo = null;
                if (isset($_FILES['raio_x_file']) && $_FILES['raio_x_file']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . '/../../uploads/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $mimeType = $finfo->file($_FILES['raio_x_file']['tmp_name']);
                    $allowedMimeTypes = ['image/jpeg' => 'jpg', 'image/png'  => 'png', 'image/gif'  => 'gif', 'application/pdf' => 'pdf'];
                    if (!array_key_exists($mimeType, $allowedMimeTypes)) {
                        throw new \Exception("Formato de arquivo não permitido.");
                    }
                    $extension = $allowedMimeTypes[$mimeType];
                    $safePaciente = preg_replace('/[^a-zA-Z0-9-_]/', '', str_replace(' ', '_', $pacienteNome));
                    $fileName = uniqid() . '_' . $safePaciente . '.' . $extension;
                    $uploadFile = $uploadDir . $fileName;
                    if (!move_uploaded_file($_FILES['raio_x_file']['tmp_name'], $uploadFile)) {
                        throw new \Exception("Falha ao mover o arquivo enviado.");
                    }
                    $urlArquivo = 'uploads/' . $fileName;
                }

                // 2. Processar e separar procedimentos
                $procedimentosFinalizados = [];
                $procedimentosPendentes = [];
                $valorBrutoTotal = 0;

                $stmtProc = $pdo->prepare("SELECT id, nome, categoria, valor_base FROM procedimentos WHERE id = ?");
                
                if (!empty($procedimentosInput['id']) && is_array($procedimentosInput['id'])) {
                    foreach ($procedimentosInput['id'] as $key => $idProcedimento) {
                        $quantidade = intval($procedimentosInput['quantidade'][$key]);
                        if (!$idProcedimento || $quantidade <= 0) continue;

                        $stmtProc->execute([$idProcedimento]);
                        $procedimento = $stmtProc->fetch();
                        if (!$procedimento) throw new \Exception("Procedimento com ID $idProcedimento não encontrado.");

                        $procParaSalvar = [
                            'id' => $idProcedimento,
                            'quantidade' => $quantidade,
                            'valor_total' => floatval($procedimentosInput['valor'][$key]),
                            'categoria' => $procedimento['categoria'],
                            'custo_auxiliar_manual' => isset($procedimentosInput['custo_auxiliar'][$key]) ? floatval($procedimentosInput['custo_auxiliar'][$key]) : 0.0,
                            'local' => trim($procedimentosInput['local'][$key]),
                            'descricao' => trim($procedimentosInput['descricao'][$key]),
                            'status_execucao' => trim($procedimentosInput['status_execucao'][$key]),
                            'natureza' => trim($procedimentosInput['natureza'][$key] ?? '')
                        ];

                        if ($procParaSalvar['status_execucao'] === 'finalizado') {
                            $procedimentosFinalizados[] = $procParaSalvar;
                            $valorBrutoTotal += $procParaSalvar['valor_total'];
                        } else {
                            $procedimentosPendentes[] = $procParaSalvar;
                        }
                    }
                }

                if (empty($procedimentosFinalizados) && empty($procedimentosPendentes)) {
                    throw new \Exception("Nenhum procedimento válido foi adicionado.");
                }

                // 4. Salvar o atendimento principal
                $idAtendimentoPrincipal = null;
                if (!empty($procedimentosFinalizados)) {
                    $sqlAtendimento = "INSERT INTO atendimentos (paciente_id, id_dentista, data_atendimento, url_arquivo) VALUES (?, ?, NOW(), ?)";
                    $stmtAtendimento = $pdo->prepare($sqlAtendimento);
                    $stmtAtendimento->execute([$pacienteId, $idDentista, $urlArquivo]);
                    $idAtendimentoPrincipal = $pdo->lastInsertId();

                    $totalComissaoDentista = 0;
                    $totalCustoAuxiliar = 0;
                    $sqlProcAtendimento = "INSERT INTO atendimento_procedimentos (id_atendimento, id_procedimento, quantidade, valor_procedimento, custo_auxiliar, local, descricao, status_execucao, natureza) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmtProcAtendimento = $pdo->prepare($sqlProcAtendimento);

                    foreach ($procedimentosFinalizados as $proc) {
                        $resComissao = \App\Models\Financeiro::calcularComissao($proc['valor_total'], $proc['categoria'], $faturamentoBrutoMensal, $proc['custo_auxiliar_manual'], $proc['natureza']);
                        $comissaoProcedimento = $resComissao['dentista'];
                        $custoAuxiliarProcedimento = $resComissao['auxiliar'] ?? 0.0;
                        $totalComissaoDentista += $comissaoProcedimento;
                        $totalCustoAuxiliar += $custoAuxiliarProcedimento;
                        
                        $stmtProcAtendimento->execute([
                            $idAtendimentoPrincipal, $proc['id'], $proc['quantidade'], $proc['valor_total'], 
                            $custoAuxiliarProcedimento, $proc['local'], $proc['descricao'], $proc['status_execucao'], $proc['natureza']
                        ]);
                    }

                    $valorLiquidoClinica = $valorBrutoTotal - $totalComissaoDentista - $totalCustoAuxiliar;
                    $statusPagamento = $valorBrutoTotal > 0 ? 'pendente' : 'pago';

                    $sqlUpdAtendimento = "UPDATE atendimentos SET valor_total = ?, comissao_dentista = ?, custo_auxiliar = ?, valor_liquido_clinica = ?, status_pagamento = ? WHERE id = ?";
                    $stmtUpdAtendimento = $pdo->prepare($sqlUpdAtendimento);
                    $stmtUpdAtendimento->execute([$valorBrutoTotal, $totalComissaoDentista, $totalCustoAuxiliar, $valorLiquidoClinica, $statusPagamento, $idAtendimentoPrincipal]);
                }
                
                // 6. Salvar um novo atendimento para os procedimentos pendentes
                if (!empty($procedimentosPendentes)) {
                    $sqlAtendimentoPendente = "INSERT INTO atendimentos (paciente_id, id_dentista, data_atendimento, valor_total, status_pagamento, url_arquivo) VALUES (?, ?, NOW(), 0, 'nao_aplicavel', ?)";
                    $stmtAtendimentoPendente = $pdo->prepare($sqlAtendimentoPendente);
                    $stmtAtendimentoPendente->execute([$pacienteId, $idDentista, $urlArquivo]);
                    $idAtendimentoPendente = $pdo->lastInsertId();

                    $sqlProcPendente = "INSERT INTO atendimento_procedimentos (id_atendimento, id_procedimento, quantidade, valor_procedimento, custo_auxiliar, local, descricao, status_execucao, natureza) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmtProcPendente = $pdo->prepare($sqlProcPendente);
                    foreach ($procedimentosPendentes as $proc) {
                         $stmtProcPendente->execute([
                            $idAtendimentoPendente, $proc['id'], $proc['quantidade'], $proc['valor_total'],
                            $proc['custo_auxiliar_manual'], $proc['local'], $proc['descricao'], $proc['status_execucao'], $proc['natureza']
                        ]);
                    }
                }
                
                $pdo->commit();
                
                header('Content-Type: application/json');
                echo json_encode(['sucesso' => true, 'mensagem' => 'Atendimento lançado com sucesso!', 'redirectUrl' => BASE_URL . 'index.php']);
                exit;

            } catch (\Exception $e) {
                $pdo->rollBack();
                error_log("Erro no AtendimentoController: " . $e->getMessage());
                $this->sendJsonError("Ocorreu um erro interno ao salvar o atendimento: " . $e->getMessage(), 500);
            }
        }
    }

    public function salvarPagamentoAjax() {
        require_once 'config/session.php';
        require_once 'config/app.php';
        require_once 'config/controle_acesso.php';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || (!is_admin() && !is_recepcionista() && !is_dentista())) {
            $this->sendJsonError('Acesso negado ou método inválido.', 403);
        }

        $atendimento_id = $_POST['atendimento_id'] ?? null;
        $paciente_id = $_POST['paciente_id'] ?? null;
        $pagamentos = $_POST['pagamentos'] ?? [];

        if (!$atendimento_id || !$paciente_id || empty($pagamentos['valor'])) {
            $this->sendJsonError('Dados essenciais não foram fornecidos.', 400);
        }

        $pdo = \App\Core\Database::getInstance();

        try {
            $pdo->beginTransaction();

            $totalTaxaCartao = 0.0;
            $totalPago = 0.0;
            $int_atendimento_id = (int)$atendimento_id;

            $stmtPagamento = $pdo->prepare(
                "INSERT INTO atendimento_pagamentos (id_atendimento, forma_pagamento, valor, qtd_parcelas) 
                 VALUES (?, ?, ?, ?)"
            );

            foreach ($pagamentos['valor'] as $index => $valor) {
                $valorPago = filter_var(str_replace(',', '.', $valor), FILTER_VALIDATE_FLOAT);

                if ($valorPago !== false && $valorPago > 0) {
                    $forma = $pagamentos['forma'][$index];
                    $parcelas = ($forma === 'credito') ? (int)($pagamentos['parcelas'][$index] ?? 1) : 1;
                    
                    $stmtPagamento->execute([$int_atendimento_id, $forma, $valorPago, $parcelas]);
                    
                    $resMaquininha = \App\Models\Financeiro::calcularLiquidoMaquininha($valorPago, $forma, $parcelas);
                    $totalTaxaCartao += (float)$resMaquininha['valor_taxa'];
                    $totalPago += $valorPago;
                }
            }

            $stmtAtendimento = $pdo->prepare("SELECT valor_total, comissao_dentista, custo_auxiliar FROM atendimentos WHERE id = ?");
            $stmtAtendimento->execute([$int_atendimento_id]);
            $atendimento = $stmtAtendimento->fetch(\PDO::FETCH_ASSOC);

            if (!$atendimento) {
                throw new \Exception("Atendimento não encontrado.");
            }
            
            $valorTotalAtendimento = (float)$atendimento['valor_total'];
            if (abs($totalPago - $valorTotalAtendimento) > 0.01) {
                throw new \Exception("A soma dos pagamentos (R$ ".number_format($totalPago, 2, ',', '.').") não corresponde ao valor total do atendimento (R$ ".number_format($valorTotalAtendimento, 2, ',', '.').").");
            }

            $stmtDentista = $pdo->prepare("SELECT id_dentista FROM atendimentos WHERE id = ?");
            $stmtDentista->execute([$int_atendimento_id]);
            $idDentistaAtendimento = $stmtDentista->fetchColumn();

            date_default_timezone_set('America/Sao_Paulo');
            $mesAtual = date('m');
            $anoAtual = date('Y');
            
            $faturamentoBrutoMensal = 0;
            if ($idDentistaAtendimento) {
                $faturamentoBrutoMensal = \App\Models\Financeiro::calcularFaturamentoMensalDentista($idDentistaAtendimento, $mesAtual, $anoAtual);
            }

            $faturamentoParaCalculo = $faturamentoBrutoMensal + $valorTotalAtendimento;

            $stmtProcedimentosAtendimento = $pdo->prepare(
                "SELECT ap.valor_procedimento, ap.custo_auxiliar, ap.natureza, p.categoria
                 FROM atendimento_procedimentos ap
                 JOIN procedimentos p ON ap.id_procedimento = p.id
                 WHERE ap.id_atendimento = ? AND ap.status_execucao = 'finalizado'"
            );
            $stmtProcedimentosAtendimento->execute([$int_atendimento_id]);
            $procedimentosDoAtendimento = $stmtProcedimentosAtendimento->fetchAll(\PDO::FETCH_ASSOC);

            $novaComissaoTotal = 0.0;
            foreach ($procedimentosDoAtendimento as $proc) {
                $resComissao = \App\Models\Financeiro::calcularComissao($proc['valor_procedimento'], $proc['categoria'], $faturamentoParaCalculo, $proc['custo_auxiliar'], $proc['natureza']);
                $novaComissaoTotal += $resComissao['dentista'];
            }

            $valorLiquidoClinica = $valorTotalAtendimento - $totalTaxaCartao - $novaComissaoTotal - (float)$atendimento['custo_auxiliar'];

            $stmtUpdateAtendimento = $pdo->prepare(
                "UPDATE atendimentos SET status_pagamento = 'pago', taxa_cartao = ?, comissao_dentista = ?, valor_liquido_clinica = ? WHERE id = ?"
            );
            $stmtUpdateAtendimento->execute([$totalTaxaCartao, $novaComissaoTotal, $valorLiquidoClinica, $int_atendimento_id]);

            $stmtUpdateProcedimentos = $pdo->prepare(
                "UPDATE atendimento_procedimentos SET status_execucao = 'feito' WHERE id_atendimento = ? AND status_execucao = 'finalizado'"
            );
            $stmtUpdateProcedimentos->execute([$int_atendimento_id]);
            
            $pdo->commit();

            header('Content-Type: application/json');
            echo json_encode(['sucesso' => true, 'mensagem' => 'Pagamento confirmado com sucesso!']);
            exit;

        } catch (\Exception $e) {
            $pdo->rollBack();
            error_log("Erro em salvarPagamentoAjax: " . $e->getMessage());
            $this->sendJsonError('Erro ao salvar o pagamento: ' . $e->getMessage(), 500);
        }
    }

    public function verificarPagamentoAjax() {
        require_once 'config/app.php';
        
        header('Content-Type: application/json');

        $paciente_id = $_GET['paciente_id'] ?? null;

        if (!$paciente_id) {
            echo json_encode(['pendente' => false, 'erro' => 'ID do paciente não fornecido.']);
            exit;
        }

        $pdo = \App\Core\Database::getInstance();

        try {
            $stmt = $pdo->prepare(
                "SELECT COUNT(*) 
                 FROM atendimentos 
                 WHERE paciente_id = ? AND status_pagamento = 'pendente'"
            );
            $stmt->execute([$paciente_id]);
            $count = $stmt->fetchColumn();

            echo json_encode(['pendente' => $count > 0]);

        } catch (\Exception $e) {
            error_log("Erro ao verificar pagamento pendente: " . $e->getMessage());
            echo json_encode(['pendente' => false, 'erro' => 'Erro ao consultar o banco de dados.']);
        }
    }

    public function concluirProcedimento() {
        require_once 'config/session.php';
        require_once 'config/seguranca.php';
        require_once 'config/controle_acesso.php';
        
        if (!is_dentista() && !is_admin()) {
            header('Location: ' . BASE_URL . 'index.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $procedimento_id = $_POST['procedimento_id'] ?? null;

            if ($procedimento_id) {
                $pdo = \App\Core\Database::getInstance();
                
                try {
                    $stmt = $pdo->prepare("UPDATE atendimento_procedimentos SET status_execucao = 'finalizado' WHERE id = ?");
                    $stmt->execute([$procedimento_id]);
                    
                    header('Location: ' . BASE_URL . 'index.php?msg=concluido');
                    exit;
                } catch (\Exception $e) {
                    error_log("Erro ao concluir procedimento: " . $e->getMessage());
                    header('Location: ' . BASE_URL . 'index.php?erro=db');
                    exit;
                }
            }
        }
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}