<?php
session_start();

include_once '../includes/db_config.php';

// Permissões: Admin, Borracharia, Mecânica (quem pode atualizar agendamentos)
$allowed_roles = ['admin', 'borracharia', 'mecanica'];
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['tipo_servico'], $allowed_roles)) {
    header("Location: ../login.php");
    exit();
}

$message = '';
$message_type = '';
$redirect_url = 'listar_agendamentos.php'; // Redirecionamento padrão

// Verifica se a requisição é POST e se os dados necessários estão presentes
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agendamento_id']) && isset($_POST['new_status'])) {
    $agendamento_id = (int)$_POST['agendamento_id'];
    $new_status = $_POST['new_status'];
    $redirect_url = $_POST['redirect_url'] ?? $redirect_url; // Pega a URL de redirecionamento

    // Validação básica do ID e do novo status
    $valid_statuses = ['pendente', 'confirmado', 'em_andamento', 'concluido', 'cancelado'];
    if ($agendamento_id <= 0 || !in_array($new_status, $valid_statuses)) {
        $message = 'Dados inválidos para atualizar o agendamento.';
        $message_type = 'error';
    } else {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) { die("Erro de conexão: " . $conn->connect_error); }

        // --- Verificação de Permissão Adicional (para não-admins) ---
        // Garante que um usuário de borracharia só possa mudar status de agendamentos da borracharia, e mecânica de mecânica
        if ($_SESSION['tipo_servico'] != 'admin') {
            $sql_check_permission = "SELECT tipo_estabelecimento FROM agendamentos WHERE id = ?";
            if ($stmt_check = $conn->prepare($sql_check_permission)) {
                $stmt_check->bind_param("i", $agendamento_id);
                $stmt_check->execute();
                $stmt_check->bind_result($agendamento_estab_type);
                $stmt_check->fetch();
                $stmt_check->close();

                if ($agendamento_estab_type !== $_SESSION['tipo_servico']) {
                    $message = 'Você não tem permissão para alterar o status deste agendamento.';
                    $message_type = 'error';
                    $conn->close();
                    header("Location: " . $redirect_url . "?status=" . $message_type . "&msg=" . urlencode($message));
                    exit();
                }
            } else {
                $message = 'Erro na preparação da verificação de permissão: ' . $conn->error;
                $message_type = 'error';
                $conn->close();
                header("Location: " . $redirect_url . "?status=" . $message_type . "&msg=" . urlencode($message));
                exit();
            }
        }

        // --- Atualização do Status ---
        $sql_update_status = "UPDATE agendamentos SET status = ? WHERE id = ?";

        if ($stmt = $conn->prepare($sql_update_status)) {
            $stmt->bind_param("si", $new_status, $agendamento_id);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $message = 'Status do agendamento ID ' . htmlspecialchars($agendamento_id) . ' atualizado para "' . htmlspecialchars(ucfirst($new_status)) . '" com sucesso!';
                    $message_type = 'success';
                } else {
                    $message = 'Nenhuma alteração feita ou agendamento não encontrado.';
                    $message_type = 'info';
                }
            } else {
                $message = 'Erro ao atualizar status: ' . $stmt->error;
                $message_type = 'error';
            }
            $stmt->close();
        } else {
            $message = 'Erro na preparação da consulta de atualização: ' . $conn->error;
            $message_type = 'error';
        }
        $conn->close();
    }
} else {
    $message = 'Ação inválida para atualizar agendamento.';
    $message_type = 'error';
}

// Redireciona de volta para a página de listagem
header("Location: " . $redirect_url . "?status=" . $message_type . "&msg=" . urlencode($message));
exit();
?>