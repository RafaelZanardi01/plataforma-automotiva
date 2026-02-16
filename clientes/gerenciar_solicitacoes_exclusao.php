<?php
session_start();

include_once '../includes/db_config.php';

// Permissões: Admin, Borracharia, Autopeças, Mecânica
$allowed_roles = ['admin', 'borracharia', 'autopecas', 'mecanica'];
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['tipo_servico'], $allowed_roles)) {
    header("Location: ../login.php");
    exit();
}

$current_user_role = $_SESSION['tipo_servico'];
$message = '';
$message_type = '';

// Lógica para processar aprovações/rejeições
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && isset($_POST['request_id'])) {
    $request_id = (int)$_POST['request_id'];
    $action = $_POST['action']; // 'approve' ou 'reject'

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) { die("Erro de conexão: " . $conn->connect_error); }

    // Inicia uma transação para garantir atomicidade
    $conn->begin_transaction();

    try {
        // 1. Atualizar o status da solicitação para o tipo de serviço atual
        $status_field = 'status_' . $current_user_role;
        $new_status = ($action == 'approve') ? 'aprovado' : 'rejeitado';

        $sql_update_status = "UPDATE solicitacoes_exclusao_clientes SET $status_field = ? WHERE id = ?";
        if ($stmt_update_status = $conn->prepare($sql_update_status)) {
            $stmt_update_status->bind_param("si", $new_status, $request_id);
            $stmt_update_status->execute();
            $stmt_update_status->close();
        } else {
            throw new Exception("Erro na preparação da consulta de atualização de status: " . $conn->error);
        }

        // 2. Verificar se todos os status necessários foram definidos (aprovados ou rejeitados)
        // E se todos os status não-solicitantes são 'aprovado' para prosseguir com a exclusão
        $sql_check_completion = "SELECT cliente_id, status_admin, status_borracharia, status_autopecas, status_mecanica, solicitante_tipo_servico FROM solicitacoes_exclusao_clientes WHERE id = ?";
        if ($stmt_check_completion = $conn->prepare($sql_check_completion)) {
            $stmt_check_completion->bind_param("i", $request_id);
            $stmt_check_completion->execute();
            $result_completion = $stmt_check_completion->get_result();
            $request_data = $result_completion->fetch_assoc();
            $stmt_check_completion->close();

            if ($request_data) {
                $cliente_id = $request_data['cliente_id'];
                $solicitante = $request_data['solicitante_tipo_servico'];

                $all_approved = true;
                $any_rejected = false;
                $all_responded = true;

                $roles_to_check = ['admin', 'borracharia', 'autopecas', 'mecanica'];
                foreach ($roles_to_check as $role) {
                    $status = $request_data['status_' . $role];
                    if ($status == 'pendente') {
                        $all_responded = false; // Ainda há alguém que não respondeu
                        break; // Sai do loop, não precisa verificar mais
                    }
                    if ($status == 'rejeitado') {
                        $any_rejected = true;
                    }
                    if ($role != $solicitante && $status != 'aprovado') { // O solicitante já está 'aprovado'
                        $all_approved = false;
                    }
                }

                if ($all_responded) {
                    if ($any_rejected) {
                        // Se qualquer um rejeitou, a solicitação é concluída como rejeitada
                        $sql_finalize_request = "UPDATE solicitacoes_exclusao_clientes SET data_conclusao = NOW() WHERE id = ?";
                        if ($stmt_finalize = $conn->prepare($sql_finalize_request)) {
                            $stmt_finalize->bind_param("i", $request_id);
                            $stmt_finalize->execute();
                            $stmt_finalize->close();
                            $message = 'Solicitação de exclusão do cliente ID ' . $cliente_id . ' foi rejeitada por um dos estabelecimentos.';
                            $message_type = 'error';
                        } else {
                            throw new Exception("Erro ao finalizar solicitação (rejeitada): " . $conn->error);
                        }
                    } elseif ($all_approved) {
                        // Se todos aprovaram, prosseguir com a exclusão do cliente
                        // Primeiro, verificar se o cliente tem veículos associados
                        $sql_check_vehicles = "SELECT COUNT(*) FROM veiculos WHERE cliente_id = ?";
                        if ($stmt_check_vehicles = $conn->prepare($sql_check_vehicles)) {
                            $stmt_check_vehicles->bind_param("i", $cliente_id);
                            $stmt_check_vehicles->execute();
                            $stmt_check_vehicles->bind_result($vehicle_count);
                            $stmt_check_vehicles->fetch();
                            $stmt_check_vehicles->close();

                            if ($vehicle_count > 0) {
                                // Se houver veículos, não exclui e finaliza a solicitação como rejeitada (com mensagem específica)
                                $sql_finalize_request = "UPDATE solicitacoes_exclusao_clientes SET data_conclusao = NOW() WHERE id = ?";
                                if ($stmt_finalize = $conn->prepare($sql_finalize_request)) {
                                    $stmt_finalize->bind_param("i", $request_id);
                                    $stmt_finalize->execute();
                                    $stmt_finalize->close();
                                    $message = 'Cliente ID ' . $cliente_id . ' não pôde ser excluído: Existem ' . $vehicle_count . ' veículo(s) associado(s). Solicitação finalizada como rejeitada.';
                                    $message_type = 'error';
                                } else {
                                    throw new Exception("Erro ao finalizar solicitação (veículos): " . $conn->error);
                                }
                            } else {
                                // Se não houver veículos e todos aprovaram, exclui o cliente
                                $sql_delete_client = "DELETE FROM clientes WHERE id = ?";
                                if ($stmt_delete_client = $conn->prepare($sql_delete_client)) {
                                    $stmt_delete_client->bind_param("i", $cliente_id);
                                    if ($stmt_delete_client->execute()) {
                                        if ($stmt_delete_client->affected_rows > 0) {
                                            $message = 'Cliente ID ' . $cliente_id . ' excluído com sucesso após aprovação de todos os estabelecimentos.';
                                            $message_type = 'success';
                                            // Finaliza a solicitação após a exclusão bem-sucedida
                                            $sql_finalize_request = "UPDATE solicitacoes_exclusao_clientes SET data_conclusao = NOW() WHERE id = ?";
                                            if ($stmt_finalize = $conn->prepare($sql_finalize_request)) {
                                                $stmt_finalize->bind_param("i", $request_id);
                                                $stmt_finalize->execute();
                                                $stmt_finalize->close();
                                            } else {
                                                throw new Exception("Erro ao finalizar solicitação (excluída): " . $conn->error);
                                            }
                                        } else {
                                            $message = 'Cliente ID ' . $cliente_id . ' não encontrado para exclusão (já pode ter sido excluído). Solicitação finalizada.';
                                            $message_type = 'error';
                                            // Finaliza a solicitação mesmo que o cliente não tenha sido encontrado (já excluído)
                                            $sql_finalize_request = "UPDATE solicitacoes_exclusao_clientes SET data_conclusao = NOW() WHERE id = ?";
                                            if ($stmt_finalize = $conn->prepare($sql_finalize_request)) {
                                                $stmt_finalize->bind_param("i", $request_id);
                                                $stmt_finalize->execute();
                                                $stmt_finalize->close();
                                            } else {
                                                throw new Exception("Erro ao finalizar solicitação (não encontrado): " . $conn->error);
                                            }
                                        }
                                    } else {
                                        throw new Exception("Erro ao excluir cliente: " . $stmt_delete_client->error);
                                    }
                                    $stmt_delete_client->close();
                                } else {
                                    throw new Exception("Erro na preparação da consulta de exclusão do cliente: " . $conn->error);
                                }
                            }
                        } else {
                            throw new Exception("Erro na preparação da consulta de verificação de veículos: " . $conn->error);
                        }
                    }
                } else {
                    // Se ainda não todos responderam, apenas informa que o status foi atualizado
                    $message = 'Seu status para a solicitação foi atualizado para "' . $new_status . '". Aguardando os outros estabelecimentos.';
                    $message_type = 'info'; // Usar 'info' para um status intermediário
                }
            } else {
                $message = 'Solicitação não encontrada ou já processada.';
                $message_type = 'error';
            }
        } else {
            throw new Exception("Erro na preparação da consulta de verificação de conclusão: " . $conn->error);
        }

        $conn->commit(); // Confirma a transação se tudo deu certo
    } catch (Exception $e) {
        $conn->rollback(); // Reverte a transação em caso de erro
        $message = 'Erro inesperado: ' . $e->getMessage();
        $message_type = 'error';
    } finally {
        $conn->close();
    }
}

// --- Lógica para buscar as solicitações de exclusão pendentes ---
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) { die("Erro de conexão com o banco de dados: " . $conn->connect_error); }

// Buscar solicitações PENDENTES (data_conclusao IS NULL)
// E que o status do usuário atual ainda seja 'pendente' ou que ele seja o solicitante
$sql_requests = "
    SELECT
        s.id AS request_id,
        s.cliente_id,
        c.nome AS cliente_nome,
        c.cpf_cnpj AS cliente_cpf_cnpj,
        s.solicitante_tipo_servico,
        s.data_solicitacao,
        s.status_admin,
        s.status_borracharia,
        s.status_autopecas,
        s.status_mecanica
    FROM
        solicitacoes_exclusao_clientes s
    JOIN
        clientes c ON s.cliente_id = c.id
    WHERE
        s.data_conclusao IS NULL
    ORDER BY s.data_solicitacao DESC
";

$stmt_requests = $conn->prepare($sql_requests);
if ($stmt_requests === false) { die("Erro na preparação da consulta de solicitações: " . $conn->error); }
$stmt_requests->execute();
$result_requests = $stmt_requests->get_result();

$pending_requests = [];
if ($result_requests->num_rows > 0) {
    while($row = $result_requests->fetch_assoc()) {
        $pending_requests[] = $row;
    }
}

$stmt_requests->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Solicitações de Exclusão de Clientes</title>
    <link rel="stylesheet" href="../../css/style.css"> <style>
        .message { padding: 12px 20px; border-radius: 6px; margin-bottom: 20px; font-size: 0.95em; display: flex; align-items: center; gap: 10px; }
        .message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .message.info { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }

        .request-card {
            background-color: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .request-card h3 {
            margin-top: 0;
            color: #007bff;
            font-size: 1.2em;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .request-card p {
            margin-bottom: 8px;
            color: #555;
            font-size: 0.95em;
        }
        .request-card p strong {
            color: #333;
        }
        .request-card .status-indicators {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px dashed #eee;
        }
        .request-card .status-indicators span {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
            margin-right: 8px;
            margin-bottom: 5px;
            text-transform: capitalize;
        }
        .status-indicators .status-pendente { background-color: #ffc107; color: #343a40; } /* Warning */
        .status-indicators .status-aprovado { background-color: #28a745; color: white; } /* Success */
        .status-indicators .status-rejeitado { background-color: #dc3545; color: white; } /* Danger */

        .request-actions {
            margin-top: 20px;
            text-align: right;
        }
        .request-actions button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            font-weight: 600;
            transition: background-color 0.3s ease;
            margin-left: 10px;
        }
        .request-actions button.btn-approve { background-color: #28a745; color: white; }
        .request-actions button.btn-approve:hover { background-color: #218838; }
        .request-actions button.btn-reject { background-color: #dc3545; color: white; }
        .request-actions button.btn-reject:hover { background-color: #c82333; }
        .request-actions button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
            color: #666;
        }

        .no-requests {
            text-align: center;
            padding: 30px;
            background-color: #f0f8ff;
            border: 1px dashed #cce7ff;
            border-radius: 8px;
            color: #666;
            font-style: italic;
            margin-top: 20px;
        }
        .btn-back-dashboard {
            display: inline-block;
            padding: 10px 20px;
            background-color: #6c757d;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
            margin-top: 20px;
        }
        .btn-back-dashboard:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Solicitações de Exclusão de Clientes</h2>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($pending_requests)): ?>
            <p class="no-requests">Não há solicitações de exclusão de clientes pendentes no momento.</p>
        <?php else: ?>
            <?php foreach ($pending_requests as $request): ?>
                <div class="request-card">
                    <h3>Solicitação #<?php echo htmlspecialchars($request['request_id']); ?> para Cliente: <?php echo htmlspecialchars($request['cliente_nome']); ?> (CPF/CNPJ: <?php echo htmlspecialchars($request['cliente_cpf_cnpj']); ?>)</h3>
                    <p>Solicitado por: <strong><?php echo htmlspecialchars(ucfirst($request['solicitante_tipo_servico'])); ?></strong> em <?php echo (new DateTime($request['data_solicitacao']))->format('d/m/Y H:i'); ?></p>

                    <div class="status-indicators">
                        <p>Status de Aprovação:</p>
                        <?php
                            $roles_display = [
                                'admin' => 'Administrador',
                                'borracharia' => 'Borracharia',
                                'autopecas' => 'Autopeças',
                                'mecanica' => 'Mecânica'
                            ];
                            foreach ($roles_display as $role_key => $role_name):
                                $status_val = $request['status_' . $role_key];
                                $status_class = 'status-' . $status_val;
                        ?>
                            <span class="<?php echo $status_class; ?>">
                                <?php echo htmlspecialchars($role_name); ?>: <?php echo htmlspecialchars(ucfirst($status_val)); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>

                    <div class="request-actions">
                        <?php
                            $current_role_status_field = 'status_' . $current_user_role;
                            $current_role_status = $request[$current_role_status_field];
                            $is_disabled = ($current_role_status != 'pendente');
                        ?>
                        <form action="gerenciar_solicitacoes_exclusao.php" method="POST" style="display: inline;">
                            <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($request['request_id']); ?>">
                            <button type="submit" name="action" value="approve" class="btn-approve" <?php echo $is_disabled ? 'disabled' : ''; ?>>
                                <?php echo $is_disabled ? 'Aprovado' : 'Aprovar'; ?>
                            </button>
                        </form>
                        <form action="gerenciar_solicitacoes_exclusao.php" method="POST" style="display: inline;">
                            <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($request['request_id']); ?>">
                            <button type="submit" name="action" value="reject" class="btn-reject" <?php echo $is_disabled ? 'disabled' : ''; ?>>
                                <?php echo $is_disabled ? 'Rejeitado' : 'Rejeitar'; ?>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <a href="<?php
            if (isset($_SESSION['tipo_servico']) && $_SESSION['tipo_servico'] != 'admin') {
                echo '../' . $_SESSION['tipo_servico'] . '/index.php';
            } else {
                echo '../admin/index.php';
            }
        ?>" class="btn-back-dashboard">Voltar para o Dashboard</a>
    </div>
</body>
</html>