<?php
session_start();
include_once '../includes/db_config.php';
include_once '../includes/functions.php';

// Permissões: Mecânica, Admin
$allowed_roles = ['admin', 'mecanica'];
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['tipo_servico'], $allowed_roles)) {
    header("Location: ../login.php");
    exit();
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['item_id'])) {
    $item_os_id = (int)$_POST['item_id'];

    if ($item_os_id <= 0) {
        $conn->close();
        header("Location: listar_os.php?status=error&msg=" . urlencode("ID do item inválido."));
        exit();
    }

    $conn->begin_transaction();

    // 1. Buscar detalhes do item_os a ser removido para atualizar os totais
    $sql_find_item = "SELECT os_id, quantidade, preco_unitario, peca_id, servico_id FROM itens_os WHERE id = ?";
    $stmt_find = $conn->prepare($sql_find_item);
    $stmt_find->bind_param("i", $item_os_id);
    $stmt_find->execute();
    $result_find = $stmt_find->get_result();

    if ($result_find->num_rows === 0) {
        $conn->rollback();
        $conn->close();
        header("Location: listar_os.php?status=error&msg=" . urlencode("Item da OS não encontrado."));
        exit();
    }
    $item_to_remove = $result_find->fetch_assoc();
    $os_id = $item_to_remove['os_id'];
    $subtotal_removido = $item_to_remove['quantidade'] * $item_to_remove['preco_unitario'];
    $is_peca = !empty($item_to_remove['peca_id']);

    // 2. Deletar o item da tabela itens_os
    $sql_delete = "DELETE FROM itens_os WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $item_os_id);

    if ($stmt_delete->execute()) {
        // 3. Atualizar os totais na tabela ordens_servico
        if ($is_peca) {
            $sql_update_totals = "UPDATE ordens_servico SET
                                total_pecas = total_pecas - ?,
                                total_geral = total_geral - ?
                                WHERE id = ?";
            $stmt_update_totals = $conn->prepare($sql_update_totals);
            $stmt_update_totals->bind_param("ddi", $subtotal_removido, $subtotal_removido, $os_id);
        } else {
            $sql_update_totals = "UPDATE ordens_servico SET
                                total_mao_obra = total_mao_obra - ?,
                                total_geral = total_geral - ?
                                WHERE id = ?";
            $stmt_update_totals = $conn->prepare($sql_update_totals);
            $stmt_update_totals->bind_param("ddi", $subtotal_removido, $subtotal_removido, $os_id);
        }

        if ($stmt_update_totals->execute()) {
            $conn->commit();
            $conn->close();
            header("Location: editar_os.php?id=$os_id&status=success&msg=" . urlencode("Item removido e totais atualizados com sucesso!"));
            exit();
        } else {
            $conn->rollback();
            $conn->close();
            header("Location: editar_os.php?id=$os_id&status=error&msg=" . urlencode("Erro ao atualizar os totais: " . $stmt_update_totals->error));
            exit();
        }
    } else {
        $conn->rollback();
        $conn->close();
        header("Location: editar_os.php?id=$os_id&status=error&msg=" . urlencode("Erro ao remover o item: " . $stmt_delete->error));
        exit();
    }
} else {
    $conn->close();
    header("Location: listar_os.php?status=error&msg=" . urlencode("Requisição inválida."));
    exit();
}
?>