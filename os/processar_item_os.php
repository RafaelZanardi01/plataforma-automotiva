<?php
session_start();

include_once '../includes/db_config.php';
include_once '../includes/functions.php';

$allowed_roles = ['admin', 'mecanica'];
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['tipo_servico'], $allowed_roles)) {
    header("Location: ../login.php");
    exit();
}

$message = '';
$message_type = 'error';
$os_id = 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $os_id = (int)($_POST['os_id'] ?? 0);
    $tipo_item_add = trim($_POST['tipo_item_add'] ?? '');
    $item_id = (int)($_POST['item_id'] ?? 0);
    $quantidade = (int)($_POST['quantidade'] ?? 0);
    $preco_unitario_br = $_POST['preco_unitario'] ?? '0,00';
    $valor_mao_obra_br = $_POST['valor_mao_obra'] ?? '0,00';

    // Chama a função parseBrazilianCurrency, que já faz a conversão correta
    $preco_unitario = parseBrazilianCurrency($preco_unitario_br);
    $valor_mao_obra = parseBrazilianCurrency($valor_mao_obra_br);



    if ($os_id <= 0 || empty($tipo_item_add) || $item_id <= 0 || $quantidade <= 0 || $preco_unitario < 0) {
        $message = 'Por favor, preencha todos os campos do item corretamente.';
    } else {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            $message = "Erro de conexão: " . $conn->connect_error;
            header("Location: editar_os.php?id=" . $os_id . "&status=" . $message_type . "&msg=" . urlencode($message));
            exit();
        }

        $conn->begin_transaction();

        try {
            if ($tipo_item_add == 'peca') {
                $sql_insert_item = "INSERT INTO itens_os (os_id, peca_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)";
                if ($stmt_insert_item = $conn->prepare($sql_insert_item)) {
                    $stmt_insert_item->bind_param("iidd", $os_id, $item_id, $quantidade, $preco_unitario);
                    if (!$stmt_insert_item->execute()) { throw new Exception("Falha na execução do INSERT (Peça): " . $stmt_insert_item->error); }
                    $stmt_insert_item->close();
                } else { throw new Exception("Erro na preparação da inserção de peça: " . $conn->error); }

            } else { // 'servico'
                $sql_insert_item = "INSERT INTO itens_os (os_id, servico_id, quantidade, preco_unitario, mao_de_obra) VALUES (?, ?, ?, ?, ?)";
                if ($stmt_insert_item = $conn->prepare($sql_insert_item)) {
                    $stmt_insert_item->bind_param("iiidd", $os_id, $item_id, $quantidade, $preco_unitario, $valor_mao_obra);
                    if (!$stmt_insert_item->execute()) { throw new Exception("Falha na execução do INSERT (Serviço): " . $stmt_insert_item->error); }
                    $stmt_insert_item->close();
                } else { throw new Exception("Erro na preparação da inserção de serviço: " . $conn->error); }
            }

            // Chame a função de recalcular totais após a inserção
            calculateOSSum($os_id, $conn);

            $conn->commit();
            $message = 'Item adicionado à Ordem de Serviço com sucesso!';
            $message_type = 'success';

        } catch (Exception $e) {
            $conn->rollback();
            $message = 'Erro inesperado durante a transação: ' . $e->getMessage();
            $message_type = 'error';
        } finally {
            $conn->close();
        }
    }
}

header("Location: editar_os.php?id=" . $os_id . "&status=" . $message_type . "&msg=" . urlencode($message));
exit();
?>