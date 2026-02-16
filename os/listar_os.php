<?php
session_start();

include_once '../includes/db_config.php';

// Permissões: Mecânica, Admin
$allowed_roles = ['admin', 'mecanica'];
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['tipo_servico'], $allowed_roles)) {
    header("Location: ../login.php");
    exit();
}

$current_user_role = $_SESSION['tipo_servico'];
$message_status = '';
$message_text = '';

if (isset($_GET['status']) && isset($_GET['msg'])) {
    $message_status = htmlspecialchars($_GET['status']);
    $message_text = htmlspecialchars(urldecode($_GET['msg']));
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) { die("Erro de conexão: " . $conn->connect_error); }

// --- Lógica de Busca e Filtro ---
$search_term = '';
$where_clauses = [];
$bind_params = [];
$bind_types = '';

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = trim($_GET['search']);
    $search_pattern = '%' . strtolower($search_term) . '%';

    $search_where = [];
    $search_binds = [];
    $search_types = '';

    if (is_numeric($search_term)) {
        $search_where[] = "os.id = ?";
        $search_binds[] = (int)$search_term;
        $search_types .= 'i';
    } else {
        $search_where[] = "LOWER(c.nome) LIKE ?";
        $search_binds[] = $search_pattern;
        $search_types .= 's';

        $search_where[] = "LOWER(v.placa) LIKE ?";
        $search_binds[] = $search_pattern;
        $search_types .= 's';

        $search_where[] = "LOWER(os.status) LIKE ?";
        $search_binds[] = $search_pattern;
        $search_types .= 's';
    }
    
    $where_clauses[] = "(" . implode(" OR ", $search_where) . ")";
    $bind_params = array_merge($bind_params, $search_binds);
    $bind_types .= $search_types;
}

$sql = "SELECT
            os.id AS os_id,
            os.data_abertura,
            os.data_fechamento,
            os.status,
            os.problema_relatado,
            os.diagnostico,
            os.servicos_executados,
            os.total_pecas,
            os.total_mao_obra,
            os.total_geral,
            c.nome AS cliente_nome,
            c.telefone AS cliente_telefone,
            v.placa AS veiculo_placa,
            v.marca AS veiculo_marca,
            v.modelo AS veiculo_modelo
        FROM
            ordens_servico os
        JOIN
            clientes c ON os.cliente_id = c.id
        JOIN
            veiculos v ON os.veiculo_id = v.id";

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY os.data_abertura DESC";

$stmt = $conn->prepare($sql);
if ($stmt === false) { die("Erro na preparação da consulta: " . $conn->error); }

if (!empty($bind_params)) {
    $stmt->bind_param($bind_types, ...$bind_params);
}

$stmt->execute();
$result = $stmt->get_result();

$ordens_servico = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $ordens_servico[] = $row;
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
    <title>Gerenciar Ordens de Serviço</title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .search-form { margin-bottom: 20px; display: flex; gap: 10px; align-items: center; }
        .search-form input[type="text"] { flex-grow: 1; padding: 10px; border: 1px solid #5a6268; border-radius: 5px; font-size: 1em; background-color: #6c757d; color: #f8f9fa; }
        .search-form input[type="text"]::placeholder { color: #ced4da; }
        .search-form button { padding: 10px 15px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 1em; transition: background-color 0.3s ease; }
        .search-form button:hover { background-color: #0056b3; }

        .container { background-color: #495057 !important; padding: 30px 40px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3); max-width: 1400px; margin: 40px auto; border-top: 5px solid #007bff; }
        
        /* Resto do CSS da tabela e botões */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15); table-layout: auto; }
        th, td { border: 1px solid #5a6268; padding: 12px; text-align: left; vertical-align: middle; word-wrap: break-word; color: #e9ecef; }
        th { background-color: #007bff; color: white; font-weight: 600; text-align: center; }
        tr:nth-child(even) { background-color: #5a6268; }
        tr:hover { background-color: #6c757d; }
        .no-records { text-align: center; padding: 20px; color: #ced4da; font-style: italic; background-color: #6c757d; border-radius: 8px; }
        .action-buttons { margin-top: 20px; text-align: left; display: flex; gap: 10px; }
        .action-buttons a { padding: 10px 20px; background-color: #6c757d; color: white; border-radius: 5px; text-decoration: none; transition: background-color 0.3s ease; }
        .action-buttons a.btn-add { background-color: #28a745; }
        .action-buttons a.btn-add:hover { background-color: #218838; }
        .action-buttons a:hover { background-color: #5a6268; }
        .status-aberta { background-color: #007bff; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
        .status-em_andamento { background-color: #6f42c1; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
        .status-aguardando_peca { background-color: #ffc107; color: #343a40; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
        .status-concluida { background-color: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
        .status-cancelada { background-color: #dc3545; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
        .btn-edit-os { width: 75px; padding: 6px 0; border-radius: 4px; text-decoration: none; color: white; font-size: 0.85em; white-space: nowrap; display: inline-block; text-align: center; box-sizing: border-box; background-color: #007bff; margin-right: 5px; transition: background-color 0.3s ease, transform 0.2s ease; line-height: 1.5; vertical-align: middle; }
        .btn-edit-os:hover { background-color: #0056b3; transform: translateY(-1px); }
        .message { padding: 12px 20px; border-radius: 6px; margin-bottom: 20px; font-size: 0.95em; display: flex; align-items: center; gap: 10px; }
        .message.success { background-color: #218838; color: white; border: 1px solid #1c7430; }
        .message.error { background-color: #c82333; color: white; border: 1px solid #bd2130; }
        .message.info { background-color: #0a58ca; color: white; border: 1px solid #084cba; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Gerenciar Ordens de Serviço</h2>

        <?php
        if (!empty($message_status)) {
            $message_class = '';
            if ($message_status == 'success') { $message_class = 'message success'; }
            elseif ($message_status == 'error') { $message_class = 'message error'; }
            elseif ($message_status == 'info') { $message_class = 'message info'; }
            ?>
            <div class="<?php echo $message_class; ?>">
                <?php echo $message_text; ?>
            </div>
        <?php } ?>

        <div class="search-form">
            <form action="listar_os.php" method="GET" style="display: flex; width: 100%; gap: 10px;">
                <input type="text" name="search" placeholder="Buscar por OS ID, cliente, placa, status..." value="<?php echo htmlspecialchars($search_term); ?>">
                <button type="submit">Buscar</button>
            </form>
        </div>

        <div class="action-buttons">
            <a href="../mecanica/index.php">Voltar para o Dashboard</a>
            <a href="abrir_os.php" class="btn-add">Abrir Nova OS</a>
        </div>

        <?php if (empty($ordens_servico)): ?>
            <p class="no-records">Nenhuma Ordem de Serviço encontrada. <?php echo (!empty($search_term)) ? 'Tente uma busca diferente.' : ''; ?></p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>OS ID</th>
                        <th>Status</th>
                        <th>Cliente</th>
                        <th>Veículo (Placa)</th>
                        <th>Marca/Modelo</th>
                        <th>Data Abertura</th>
                        <th>Data Fechamento</th>
                        <th>Total Peças</th>
                        <th>Total Mão de Obra</th>
                        <th>Total Geral</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ordens_servico as $os): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($os['os_id']); ?></td>
                        <td><span class="status-<?php echo htmlspecialchars($os['status']); ?>"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $os['status']))); ?></span></td>
                        <td><?php echo htmlspecialchars($os['cliente_nome']); ?></td>
                        <td><?php echo htmlspecialchars($os['veiculo_placa']); ?></td>
                        <td><?php echo htmlspecialchars($os['veiculo_marca'] . ' ' . $os['veiculo_modelo']); ?></td>
                        <td><?php echo (new DateTime($os['data_abertura']))->format('d/m/Y H:i'); ?></td>
                        <td><?php echo !empty($os['data_fechamento']) ? (new DateTime($os['data_fechamento']))->format('d/m/Y H:i') : 'Pendente'; ?></td>
                        <td>R$ <?php echo htmlspecialchars(number_format($os['total_pecas'], 2, ',', '.')); ?></td>
                        <td>R$ <?php echo htmlspecialchars(number_format($os['total_mao_obra'], 2, ',', '.')); ?></td>
                        <td>R$ <?php echo htmlspecialchars(number_format($os['total_geral'], 2, ',', '.')); ?></td>
                        <td>
                            <a href="editar_os.php?id=<?php echo $os['os_id']; ?>" class="btn-edit-os">Editar/Detalhes</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>