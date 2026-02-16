<?php
session_start();

include_once '../includes/db_config.php';

$allowed_roles = ['admin', 'borracharia', 'autopecas', 'mecanica'];
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['tipo_servico'], $allowed_roles)) {
    header("Location: ../login.php");
    exit();
}

$message_status = '';
$message_text = '';
if (isset($_GET['status']) && isset($_GET['msg'])) {
    $message_status = htmlspecialchars($_GET['status']);
    $message_text = htmlspecialchars(urldecode($_GET['msg']));
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) { die("Erro de conexão com o banco de dados: " . $conn->connect_error); }

$search_term = '';
$where_clauses = [];
$bind_params = [];
$bind_types = '';

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = trim($_GET['search']);
    $search_pattern = '%' . strtolower($search_term) . '%';

    $where_clauses[] = "LOWER(nome) LIKE ?";
    $bind_params[] = $search_pattern;
    $bind_types .= 's';

    $where_clauses[] = "telefone LIKE ?";
    $bind_params[] = $search_pattern;
    $bind_types .= 's';

    $where_clauses[] = "LOWER(email) LIKE ?";
    $bind_params[] = $search_pattern;
    $bind_types .= 's';

    $where_clauses[] = "cpf_cnpj LIKE ?";
    $bind_params[] = $search_pattern;
    $bind_types .= 's';

    $where_clauses[] = "LOWER(endereco) LIKE ?";
    $bind_params[] = $search_pattern;
    $bind_types .= 's';
}

$sql = "SELECT id, nome, telefone, email, cpf_cnpj, endereco, tipo_cliente FROM clientes";

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" OR ", $where_clauses);
}
$sql .= " ORDER BY nome ASC";

$stmt = $conn->prepare($sql);
if ($stmt === false) { die("Erro na preparação da consulta: " . $conn->error); }

if (!empty($bind_params)) {
    $stmt->bind_param($bind_types, ...$bind_params);
}

$stmt->execute();
$result = $stmt->get_result();

$clientes = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $clientes[] = $row;
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
    <title>Gerenciar Clientes</title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .search-form { margin-bottom: 20px; display: flex; gap: 10px; align-items: center; }
        .search-form input[type="text"] { flex-grow: 1; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 1em; }
        .search-form button { padding: 10px 15px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 1em; transition: background-color 0.3s ease; }
        .search-form button:hover { background-color: #0056b3; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); table-layout: auto; }
        th, td { border: 1px solid #e0e0e0; padding: 12px; text-align: left; vertical-align: middle; word-wrap: break-word; color: #333; }
        th { background-color: #007bff; color: white; font-weight: 600; text-align: center; }
        tr:nth-child(even) { background-color: #f8f8f8; }
        tr:hover { background-color: #eef1f5; }
        .no-records { text-align: center; padding: 20px; color: #666; font-style: italic; }
        .action-buttons { margin-top: 20px; text-align: left; display: flex; gap: 10px; }
        .action-buttons a { padding: 10px 20px; background-color: #6c757d; color: white; border-radius: 5px; text-decoration: none; transition: background-color 0.3s ease; }
        .action-buttons a.btn-add { background-color: #28a745; }
        .action-buttons a.btn-add:hover { background-color: #218838; }
        .action-buttons a:hover { background-color: #5a6268; }
        .btn-edit, .btn-delete { width: 75px; padding: 6px 0; border-radius: 4px; text-decoration: none; color: white; font-size: 0.85em; white-space: nowrap; display: inline-block; text-align: center; box-sizing: border-box; transition: background-color 0.3s ease, transform 0.2s ease; line-height: 1.5; vertical-align: middle; }
        .btn-edit { background-color: #007bff; margin-right: 5px; }
        .btn-edit:hover { background-color: #0056b3; transform: translateY(-1px); }
        .btn-delete { background-color: #dc3545; }
        .btn-delete:hover { background-color: #c82333; transform: translateY(-1px); }
        .message { padding: 12px 20px; border-radius: 6px; margin-bottom: 20px; font-size: 0.95em; display: flex; align-items: center; gap: 10px; }
        .message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .message.info { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Gerenciar Clientes</h2>
        <?php if (!empty($message_status)) { ?>
            <div class="message <?php echo $message_status; ?>">
                <?php echo $message_text; ?>
            </div>
        <?php } ?>
        <div class="search-form">
            <form action="listar_clientes.php" method="GET" style="display: flex; width: 100%; gap: 10px;">
                <input type="text" name="search" placeholder="Buscar por nome, email, CPF/CNPJ..." value="<?php echo htmlspecialchars($search_term); ?>">
                <button type="submit">Buscar</button>
            </form>
        </div>
        <div class="action-buttons">
            <a href="<?php
                if (isset($_SESSION['tipo_servico']) && $_SESSION['tipo_servico'] != 'admin') {
                    echo '../' . $_SESSION['tipo_servico'] . '/index.php';
                } else {
                    echo '../admin/index.php';
                }
            ?>">Voltar para o Dashboard</a>
            <a href="cadastrar_cliente.php" class="btn-add">Cadastrar Novo Cliente</a>
        </div>
        <?php if (empty($clientes)): ?>
            <p class="no-records">Nenhum cliente encontrado. <?php echo (!empty($search_term)) ? 'Tente uma busca diferente.' : ''; ?></p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Telefone</th>
                        <th>Email</th>
                        <th>CPF/CNPJ</th>
                        <th>Endereço</th>
                        <th>Tipo Cliente</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $cliente): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cliente['id']); ?></td>
                        <td><?php echo htmlspecialchars($cliente['nome']); ?></td>
                        <td><?php echo htmlspecialchars($cliente['telefone']); ?></td>
                        <td><?php echo htmlspecialchars($cliente['email']); ?></td>
                        <td><?php echo htmlspecialchars($cliente['cpf_cnpj']); ?></td>
                        <td><?php echo htmlspecialchars($cliente['endereco']); ?></td>
                        <td><?php echo htmlspecialchars($cliente['tipo_cliente']); ?></td>
                        <td>
                            <a href="editar_cliente.php?id=<?php echo $cliente['id']; ?>" class="btn-edit">Editar</a>
                            <a href="excluir_cliente.php?id=<?php echo $cliente['id']; ?>" class="btn-delete" onclick="return confirm('Tem certeza que deseja excluir este cliente? Isso removerá também veículos e outros registros relacionados a ele!');">Excluir</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>