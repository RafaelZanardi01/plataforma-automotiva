<?php
session_start();

// Inclui as configurações do banco de dados
include_once '../includes/db_config.php';

// Verifica se o usuário está logado e se tem permissão (mecanica ou admin)
if (!isset($_SESSION['user_id']) || ($_SESSION['tipo_servico'] != 'mecanica' && $_SESSION['tipo_servico'] != 'admin')) {
    header("Location: ../login.php"); // Redireciona para o login se não autorizado
    exit();
}

// Tenta conectar ao banco de dados
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verifica a conexão
if ($conn->connect_error) {
    die("Erro de conexão com o banco de dados: " . $conn->connect_error);
}

// --- Lógica de Busca e Filtro ---
$search_term = '';
$where_clauses = [];
$bind_params = [];
$bind_types = '';

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = trim($_GET['search']);
    // Adiciona cláusulas WHERE para buscar em várias colunas
    $where_clauses[] = "v.placa LIKE ?";
    $bind_params[] = '%' . $search_term . '%';
    $bind_types .= 's';

    $where_clauses[] = "v.marca LIKE ?";
    $bind_params[] = '%' . $search_term . '%';
    $bind_types .= 's';

    $where_clauses[] = "v.modelo LIKE ?";
    $bind_params[] = '%' . $search_term . '%';
    $bind_types .= 's';

    $where_clauses[] = "c.nome LIKE ?"; // Busca também pelo nome do cliente
    $bind_params[] = '%' . $search_term . '%';
    $bind_types .= 's';
}

// Constrói a query SQL
$sql = "SELECT v.id, v.marca, v.modelo, v.ano, v.placa, v.chassi, c.nome AS cliente_nome, c.cpf_cnpj AS cliente_cpf_cnpj
        FROM veiculos v
        JOIN clientes c ON v.cliente_id = c.id";

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" OR ", $where_clauses);
}
$sql .= " ORDER BY v.marca, v.modelo ASC";

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Erro na preparação da consulta: " . $conn->error);
}

/*
if (!empty($bind_params)) {
    // Usar call_user_func_array para bind_param com número variável de argumentos
    $params = array_merge([$bind_types], $bind_params);
    call_user_func_array([$stmt, 'bind_param'], $params);
}
*/

if (!empty($bind_params)) {
    // A função bind_param requer referências.
    // Usar a função 'bind_params_by_ref' para criar um array de referências
    $params = array_merge([$bind_types], $bind_params);
    
    // Cria um array de referências para contornar o problema
    $refs = [];
    foreach ($params as $key => $value) {
        $refs[$key] = &$params[$key];
    }
    
    // Agora, use call_user_func_array com o array de referências
    call_user_func_array([$stmt, 'bind_param'], $refs);
}

$stmt->execute();
$result = $stmt->get_result();

$veiculos = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $veiculos[] = $row;
    }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listar Veículos - Mecânica</title>
    <link rel="stylesheet" href="../css/style.css"> 
        <style>
        /* Estilos específicos para a tabela de listagem */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #343a40; /* Fundo escuro */
            color: #f8f9fa; /* Texto claro padrão */
            line-height: 1.6;
        }
        .container {
            background-color: #495057; /* Container mais claro */
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            max-width: 1400px;
            margin: 40px auto;
            border-top: 5px solid #007bff;
        }
        .search-form {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .search-form input[type="text"] {
            flex-grow: 1;
            padding: 10px;
            border: 1px solid #6c757d;
            border-radius: 5px;
            font-size: 1em;
            background-color: #6c757d;
            color: #f8f9fa;
        }
        .search-form input[type="text"]::placeholder {
            color: #adb5bd;
        }
        .search-form button {
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s ease;
        }
        .search-form button:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
            table-layout: auto;
        }
        th, td {
            border: 1px solid #5a6268;
            padding: 12px;
            text-align: left;
            vertical-align: middle;
            word-wrap: break-word;
        }
        th {
            background-color: #007bff;
            color: white;
            font-weight: 600;
            text-align: center;
        }
        tbody tr {
            color: #e9ecef; /* Cor do texto padrão para o corpo da tabela */
            background-color: #6c757d; /* Cor de fundo padrão */
        }
        tbody tr:nth-child(even) {
            background-color: #5a6268; /* Fundo mais escuro para linhas pares */
        }
        tbody tr:hover {
            background-color: #8c929a; /* Cor de fundo ao passar o mouse */
        }
        .no-records {
            text-align: center;
            padding: 20px;
            color: #adb5bd;
            font-style: italic;
        }
        .action-buttons {
            margin-top: 20px;
            text-align: left;
        }
        .action-buttons a {
            padding: 10px 20px;
            background-color: #6c757d;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        .action-buttons a:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Veículos Cadastrados</h2>

        <div class="search-form">
            <form action="listar_veiculos.php" method="GET" style="display: flex; width: 100%; gap: 10px;">
                <input type="text" name="search" placeholder="Buscar por placa, marca, modelo ou cliente..." value="<?php echo htmlspecialchars($search_term); ?>">
                <button type="submit">Buscar</button>
            </form>
        </div>

        <?php if (empty($veiculos)): ?>
            <p class="no-records">Nenhum veículo encontrado. <?php echo (!empty($search_term)) ? 'Tente uma busca diferente.' : ''; ?></p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>CPF/CNPJ Cliente</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Ano</th>
                        <th>Placa</th>
                        <th>Chassi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($veiculos as $veiculo): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($veiculo['id']); ?></td>
                        <td><?php echo htmlspecialchars($veiculo['cliente_nome']); ?></td>
                        <td><?php echo htmlspecialchars($veiculo['cliente_cpf_cnpj']); ?></td>
                        <td><?php echo htmlspecialchars($veiculo['marca']); ?></td>
                        <td><?php echo htmlspecialchars($veiculo['modelo']); ?></td>
                        <td><?php echo htmlspecialchars($veiculo['ano']); ?></td>
                        <td><?php echo htmlspecialchars($veiculo['placa']); ?></td>
                        <td><?php echo htmlspecialchars($veiculo['chassi']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="action-buttons">
            <a href="index.php">Voltar para o Dashboard</a>
        </div>
    </div>
</body>
</html>