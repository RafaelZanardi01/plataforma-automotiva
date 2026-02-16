<?php
session_start();

// Inclui as configurações do banco de dados
include_once '../includes/db_config.php';

// Verifica se o usuário está logado e se tem permissão (autopecas ou admin)
if (!isset($_SESSION['user_id']) || ($_SESSION['tipo_servico'] != 'autopecas' && $_SESSION['tipo_servico'] != 'admin')) {
    header("Location: ../login.php"); // Redireciona para o login se não autorizado
    exit();
}

// Tenta conectar ao banco de dados
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verifica a conexão
if ($conn->connect_error) {
    die("Erro de conexão com o banco de dados: " . $conn->connect_error);
}

// --- Lógica de Consulta e Cálculo ---
$sql = "SELECT id, nome_peca, fabricante, codigo_peca, quantidade, preco_custo, preco_venda, localizacao_estoque FROM pecas ORDER BY nome_peca ASC";
$result = $conn->query($sql);

$pecas_relatorio = [];
$total_custo_estoque = 0;
$total_venda_estoque = 0;
$limiar_baixo_estoque = 10; // Defina aqui a quantidade limite para considerar "baixo estoque"

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $row['valor_total_custo'] = $row['quantidade'] * $row['preco_custo'];
        $row['valor_total_venda'] = $row['quantidade'] * $row['preco_venda'];
        $pecas_relatorio[] = $row;

        $total_custo_estoque += $row['valor_total_custo'];
        $total_venda_estoque += $row['valor_total_venda'];
    }
}

// Fecha a conexão com o banco de dados
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Estoque - Autopeças</title>
    <link rel="stylesheet" href="../css/style.css"> <style>
        /* Estilos específicos para o relatório */
        .report-summary {
            background-color: #e9ecef;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 5px solid #007bff;
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 15px;
        }
        .report-summary div {
            text-align: center;
            font-size: 1.1em;
            color: #343a40;
        }
        .report-summary div strong {
            display: block;
            font-size: 1.5em;
            color: #007bff;
            margin-top: 5px;
        }

        /* Alerta de baixo estoque na tabela */
        .low-stock {
            background-color: #fff3cd; /* Amarelo claro */
            color: #856404; /* Texto amarelo escuro */
            font-weight: bold;
        }
        .low-stock td {
            border-color: #ffeeba !important; /* Borda também amarela */
        }

        /* Estilos da tabela (reuso do style.css ou ajuste se necessário) */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            table-layout: fixed;
        }
        th, td {
            border: 1px solid #e0e0e0;
            padding: 12px;
            text-align: left;
            vertical-align: middle;
            word-wrap: break-word;
            color: #333; /* Cor do texto para contraste com o fundo branco do container */
        }
        th {
            background-color: #007bff;
            color: white;
            font-weight: 600;
            text-align: center;
        }
        tr:nth-child(even) {
            background-color: #f8f8f8;
        }
        tr:hover {
            background-color: #eef1f5;
        }

        /* Alinhamento de colunas numéricas */
        td:nth-child(1), /* ID */
        td:nth-child(4), /* Código */
        td:nth-child(5), /* Quantidade */
        td:nth-child(6), /* Preço Custo */
        td:nth-child(7), /* Preço Venda */
        td:nth-child(8), /* Valor Custo Total */
        td:nth-child(9) /* Valor Venda Total */
        {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Relatório de Estoque da Autopeças</h2>

        <div class="report-summary">
            <div>
                Valor Total de Custo em Estoque
                <strong>R$ <?php echo htmlspecialchars(number_format($total_custo_estoque, 2, ',', '.')); ?></strong>
            </div>
            <div>
                Valor Total de Venda em Estoque
                <strong>R$ <?php echo htmlspecialchars(number_format($total_venda_estoque, 2, ',', '.')); ?></strong>
            </div>
            <div>
                Itens Atualmente em Estoque
                <strong><?php echo htmlspecialchars(count($pecas_relatorio)); ?></strong>
            </div>
        </div>

        <div class="action-buttons" style="text-align: left; margin-bottom: 20px;">
            <a href="index.php" style="background-color: #6c757d;">Voltar para o Dashboard</a>
            <a href="inventario.php" style="background-color: #007bff;">Ver Inventário Completo</a>
        </div>


        <?php if (empty($pecas_relatorio)): ?>
            <p class="no-records">Nenhuma peça encontrada no inventário para o relatório.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome da Peça</th>
                        <th>Fabricante</th>
                        <th>Código</th>
                        <th>Qtd.</th>
                        <th>Pç Custo Unit.</th>
                        <th>Pç Venda Unit.</th>
                        <th>Valor Total Custo</th>
                        <th>Valor Total Venda</th>
                        <th>Localização</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pecas_relatorio as $peca): ?>
                    <tr <?php echo ($peca['quantidade'] <= $limiar_baixo_estoque) ? 'class="low-stock"' : ''; ?>>
                        <td><?php echo htmlspecialchars($peca['id']); ?></td>
                        <td><?php echo htmlspecialchars($peca['nome_peca']); ?></td>
                        <td><?php echo htmlspecialchars($peca['fabricante']); ?></td>
                        <td><?php echo htmlspecialchars($peca['codigo_peca']); ?></td>
                        <td><?php echo htmlspecialchars($peca['quantidade']); ?></td>
                        <td>R$ <?php echo htmlspecialchars(number_format($peca['preco_custo'], 2, ',', '.')); ?></td>
                        <td>R$ <?php echo htmlspecialchars(number_format($peca['preco_venda'], 2, ',', '.')); ?></td>
                        <td>R$ <?php echo htmlspecialchars(number_format($peca['valor_total_custo'], 2, ',', '.')); ?></td>
                        <td>R$ <?php echo htmlspecialchars(number_format($peca['valor_total_venda'], 2, ',', '.')); ?></td>
                        <td><?php echo htmlspecialchars($peca['localizacao_estoque']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>