<?php
session_start();
include_once '../includes/db_config.php';
include_once '../includes/functions.php';

$allowed_roles = ['admin', 'mecanica', 'borracharia', 'autopecas'];
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['tipo_servico'], $allowed_roles)) {
    header("Location: ../login.php");
    exit();
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) { die("Erro de conexão: " . $conn->connect_error); }

$pecas_all = [];
$pecas_automotivas = [];
$itens_borracharia = [];

// Consulta SQL corrigida com os nomes de colunas da sua tabela
$sql_pecas = "SELECT id, codigo_peca, nome_peca, tipo_item, fabricante, preco_custo, preco_venda, quantidade AS quantidade_estoque FROM pecas ORDER BY tipo_item, nome_peca ASC";
$result_pecas = $conn->query($sql_pecas);

if ($result_pecas) { // Verifica se a consulta foi bem-sucedida
    if ($result_pecas->num_rows > 0) {
        while($row = $result_pecas->fetch_assoc()) {
            if ($row['tipo_item'] == 'peca_automotiva') {
                $pecas_automotivas[] = $row;
            } else {
                // Inclui pneus, acessórios e serviços de borracharia
                $itens_borracharia[] = $row;
            }
        }
    }
    $result_pecas->close();
} else {
    // Se a consulta falhou, exibe o erro (apenas para debug)
    echo "Erro na consulta SQL: " . $conn->error;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventário de Peças</title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .container { max-width: 1400px; }
        .table-section { margin-top: 30px; }
        .table-section h3 { text-align: left; margin-bottom: 15px; border-bottom: 1px solid #007bff; padding-bottom: 5px; }
        .table-actions { margin-bottom: 20px; text-align: right; }
        .table-actions a {
            background-color: #28a745;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            margin-left: 10px;
        }
        .table-actions a:hover {
            background-color: #218838;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
        }
        th, td {
            border: 1px solid #5a6268;
            padding: 12px;
            text-align: left;
            color: #e9ecef;
        }
        th {
            background-color: #6c757d;
            color: white;
            font-weight: 600;
            text-align: center;
        }
        tr:nth-child(even) { background-color: #5a6268; }
        tr:hover { background-color: #6c757d; }
        .no-records { text-align: center; color: #ced4da; font-style: italic; }
        .action-buttons-table { text-align: center; }
        .action-buttons-table a {
            background-color: #007bff;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9em;
            margin-right: 5px;
        }
        .action-buttons-table a.btn-delete { background-color: #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Inventário Geral</h2>
        <div class="table-actions">
            <a href="cadastrar_peca.php">Cadastrar Novo Item</a>
        </div>
        <div class="table-section">
            <h3>Peças Automotivas</h3>
            <?php if (empty($pecas_automotivas)): ?>
                <p class="no-records">Nenhuma peça automotiva cadastrada.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nome</th>
                            <th>Fabricante</th>
                            <th>Preço Compra</th>
                            <th>Preço Venda</th>
                            <th>Estoque</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pecas_automotivas as $peca): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($peca['codigo_peca']); ?></td>
                            <td><?php echo htmlspecialchars($peca['nome_peca']); ?></td>
                            <td><?php echo htmlspecialchars($peca['fabricante']); ?></td>
                            <td>R$ <?php echo htmlspecialchars(number_format($peca['preco_custo'], 2, ',', '.')); ?></td>
                            <td>R$ <?php echo htmlspecialchars(number_format($peca['preco_venda'], 2, ',', '.')); ?></td>
                            <td><?php echo htmlspecialchars($peca['quantidade_estoque']); ?></td>
                            <td class="action-buttons-table">
                                <a href="editar_peca.php?id=<?php echo $peca['id']; ?>">Editar</a>
                                <a href="remover_peca.php?id=<?php echo $peca['id']; ?>" class="btn-delete">Remover</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="table-section">
            <h3>Pneus e Acessórios de Borracharia</h3>
            <?php if (empty($itens_borracharia)): ?>
                <p class="no-records">Nenhum item de borracharia cadastrado.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nome</th>
                            <th>Fabricante</th>
                            <th>Preço Compra</th>
                            <th>Preço Venda</th>
                            <th>Estoque</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($itens_borracharia as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['codigo_peca']); ?></td>
                            <td><?php echo htmlspecialchars($item['nome_peca']); ?></td>
                            <td><?php echo htmlspecialchars($item['fabricante']); ?></td>
                            <td>R$ <?php echo htmlspecialchars(number_format($item['preco_custo'], 2, ',', '.')); ?></td>
                            <td>R$ <?php echo htmlspecialchars(number_format($item['preco_venda'], 2, ',', '.')); ?></td>
                            <td><?php echo htmlspecialchars($item['quantidade_estoque']); ?></td>
                            <td class="action-buttons-table">
                                <a href="editar_peca.php?id=<?php echo $item['id']; ?>">Editar</a>
                                <a href="remover_peca.php?id=<?php echo $item['id']; ?>" class="btn-delete">Remover</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="action-buttons" style="margin-top: 40px; text-align: left;">
            <a href="../dashboard.php" class="btn-back">Voltar para o Dashboard</a>
        </div>
    </div>
</body>
</html>