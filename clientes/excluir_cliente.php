<?php
session_start();

include_once '../includes/db_config.php';

// Permissões: Admin, Borracharia, Autopeças, Mecânica
$allowed_roles = ['admin', 'borracharia', 'autopecas', 'mecanica'];
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['tipo_servico'], $allowed_roles)) {
    header("Location: ../login.php"); // Redireciona para o login se não autorizado
    exit();
}

$message = '';
$message_type = '';
$redirect_url = 'listar_clientes.php'; // URL de redirecionamento padrão

// Verifica se um ID de cliente foi passado via GET
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $cliente_id_to_delete = (int)$_GET['id'];
    $solicitante_tipo_servico = $_SESSION['tipo_servico']; // Quem está solicitando a exclusão

    if ($cliente_id_to_delete <= 0) {
        $message = 'ID do cliente inválido para exclusão.';
        $message_type = 'error';
    } else {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die("Erro de conexão: " . $conn->connect_error);
        }

        // 1. Verificar se já existe uma solicitação PENDENTE para este cliente
        $sql_check_existing = "SELECT id, solicitante_tipo_servico FROM solicitacoes_exclusao_clientes WHERE cliente_id = ? AND data_conclusao IS NULL";
        if ($stmt_check = $conn->prepare($sql_check_existing)) {
            $stmt_check->bind_param("i", $cliente_id_to_delete);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
                // Já existe uma solicitação pendente
                $existing_request = $result_check->fetch_assoc();
                $message = 'Já existe uma solicitação de exclusão pendente para este cliente, iniciada por ' . htmlspecialchars($existing_request['solicitante_tipo_servico']) . '.';
                $message_type = 'error';
                $stmt_check->close();
            } else {
                $stmt_check->close();

                // 2. Inserir a nova solicitação de exclusão
                // Definir o status inicial do solicitante como 'aprovado' para ele mesmo
                $initial_status_field = 'status_' . $solicitante_tipo_servico;
                $sql_insert_request = "INSERT INTO solicitacoes_exclusao_clientes (cliente_id, solicitante_tipo_servico, $initial_status_field) VALUES (?, ?, 'aprovado')";

                if ($stmt_insert = $conn->prepare($sql_insert_request)) {
                    $stmt_insert->bind_param("is", $cliente_id_to_delete, $solicitante_tipo_servico);

                    if ($stmt_insert->execute()) {
                        $message = 'Solicitação de exclusão do cliente enviada para aprovação dos outros estabelecimentos.';
                        $message_type = 'success';
                    } else {
                        $message = 'Erro ao registrar solicitação de exclusão: ' . $stmt_insert->error;
                        $message_type = 'error';
                    }
                    $stmt_insert->close();
                } else {
                    $message = 'Erro na preparação da consulta de inserção de solicitação: ' . $conn->error;
                    $message_type = 'error';
                }
            }
        } else {
            $message = 'Erro na preparação da consulta de verificação de solicitação: ' . $conn->error;
            $message_type = 'error';
        }
        $conn->close();
    }
} else {
    $message = 'Nenhum ID de cliente especificado para exclusão.';
    $message_type = 'error';
}

// Redireciona de volta para a página de listagem de clientes com a mensagem
header("Location: " . $redirect_url . "?status=" . $message_type . "&msg=" . urlencode($message));
exit();
?>