<?php
session_start();

include_once '../includes/db_config.php';

// Permissões: Admin, Borracharia, Mecânica
$allowed_roles = ['admin', 'borracharia', 'mecanica'];
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['tipo_servico'], $allowed_roles)) {
    header("Location: ../login.php");
    exit();
}

$current_user_role = $_SESSION['tipo_servico'];
$message_status = '';
$message_text = '';

// Lógica para exibir mensagens de status (sucesso/erro)
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

// Filtro por tipo de estabelecimento para usuários não-admin
if ($current_user_role != 'admin') {
    $where_clauses[] = "a.tipo_estabelecimento = ?";
    $bind_params[] = $current_user_role;
    $bind_types .= 's';
}

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = trim($_GET['search']);
    $search_pattern = '%' . strtolower($search_term) . '%'; // Para busca insensível a maiúsculas/minúsculas

    // Cláusulas de busca para diferentes campos
    $search_where = [];
    $search_binds = [];
    $search_types = '';

    $search_where[] = "LOWER(c.nome) LIKE ?";
    $search_binds[] = $search_pattern;
    $search_types .= 's';

    $search_where[] = "LOWER(v.placa) LIKE ?";
    $search_binds[] = $search_pattern;
    $search_types .= 's';

    $search_where[] = "LOWER(s.nome_servico) LIKE ?";
    $search_binds[] = $search_pattern;
    $search_types .= 's';
    
    // Agrega as cláusulas de busca ao WHERE principal
    $where_clauses[] = "(" . implode(" OR ", $search_where) . ")";
    $bind_params = array_merge($bind_params, $search_binds);
    $bind_types .= $search_types;
}

// Constrói a query SQL
$sql = "SELECT
            a.id AS agendamento_id,
            a.data_hora,
            a.observacoes,
            a.status,
            a.tipo_estabelecimento,
            c.nome AS cliente_nome,
            c.telefone AS cliente_telefone,
            v.placa AS veiculo_placa,
            v.marca AS veiculo_marca,
            v.modelo AS veiculo_modelo,
            s.nome_servico
        FROM
            agendamentos a
        JOIN
            clientes c ON a.cliente_id = c.id
        JOIN
            veiculos v ON a.veiculo_id = v.id
        JOIN
            servicos s ON a.servico_id = s.id";

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY a.data_hora DESC"; // Ordena do mais recente para o mais antigo

$stmt = $conn->prepare($sql);
if ($stmt === false) { die("Erro na preparação da consulta: " . $conn->error); }

if (!empty($bind_params)) {
    $stmt->bind_param($bind_types, ...$bind_params);
}

$stmt->execute();
$result = $stmt->get_result();

$agendamentos = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $agendamentos[] = $row;
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
    <title>Gerenciar Agendamentos</title>
    <link rel="stylesheet" href="../../css/style.css"> 
    <style>
        .container {
            background-color: #495057;             
        }
        body {
            background-color: #343a40;
        }
        /* Estilos da tabela e formulário de busca */
        .search-form {
            margin-bottom: 20px; display: flex; gap: 10px; align-items: center;
        }
        .search-form input[type="text"] {
            flex-grow: 1; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 1em;
        }
        .search-form button {
            padding: 10px 15px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 1em; transition: background-color 0.3s ease;
        }
        .search-form button:hover { background-color: #0056b3; }

        table { width: 100%; border-collapse: collapse; margin-top: 20px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); table-layout: auto; }
        th, td {
            border: 1px solid #e0e0e0; padding: 12px; text-align: left; vertical-align: middle; word-wrap: break-word; color: #333;
        }
        th { background-color: #007bff; color: white; font-weight: 600; text-align: center; }
        tr:nth-child(even) { background-color: #f8f8f8; }
        tr:hover { background-color: #eef1f5; }
        .no-records { text-align: center; padding: 20px; color: #666; font-style: italic; }
        .action-buttons {
            margin-top: 20px; text-align: left; display: flex; gap: 10px;
        }
        .action-buttons a {
            padding: 10px 20px; background-color: #6c757d; color: white; border-radius: 5px; text-decoration: none; transition: background-color 0.3s ease;
        }
        .action-buttons a.btn-add { background-color: #28a745; }
        .action-buttons a.btn-add:hover { background-color: #218838; }
        .action-buttons a:hover { background-color: #5a6268; }

        /* Estilos para status do agendamento */
        .status-pendente { background-color: #ffc107; color: #343a40; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
        .status-confirmado { background-color: #17a2b8; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
        .status-concluido { background-color: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
        .status-cancelado { background-color: #dc3545; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }

        /* Estilos para os botões de Editar/Excluir dentro da tabela */
        .btn-edit, .btn-delete {
            width: 75px; padding: 6px 0; border-radius: 4px; text-decoration: none; color: white; font-size: 0.85em;
            white-space: nowrap; display: inline-block; text-align: center; box-sizing: border-box;
            transition: background-color 0.3s ease, transform 0.2s ease; line-height: 1.5; vertical-align: middle;
        }
        .btn-edit { background-color: #007bff; margin-right: 5px; }
        .btn-edit:hover { background-color: #0056b3; transform: translateY(-1px); }
        .btn-delete { background-color: #dc3545; }
        .btn-delete:hover { background-color: #c82333; transform: translateY(-1px); }
        /* Mensagens de Sucesso ou Falha */
        .message { padding: 12px 20px; border-radius: 6px; margin-bottom: 20px; font-size: 0.95em; display: flex; align-items: center; gap: 10px; }
        .message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .message.info { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }

        /* Estilos para os botões de status */
        .btn {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
            cursor: pointer;
            border: none;
            color: white;
            transition: background-color 0.3s ease;
            margin: 2px; /* Pequena margem para separar os botões */
        }
        .btn:hover:not(:disabled) {
            opacity: 0.9;
        }
        .btn:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
            color: #666;
        }

        /* Estilos para status do agendamento */
        .status-pendente { background-color: #ffc107; color: #343a40; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
        .status-confirmado { background-color: #17a2b8; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
        .status-em_andamento { background-color: #6f42c1; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
        .status-concluido { background-color: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
        .status-cancelado { background-color: #dc3545; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }

        /* Responsividade para botões de status */
        @media (max-width: 768px) {
            td:last-child form {
                display: block; /* Empilha os formulários para os botões */
                margin-bottom: 5px;
            }
            .btn {
                width: 100%; /* Botões de status ocupam 100% da largura em telas pequenas */
                box-sizing: border-box;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Agendamentos de Serviços</h2>

        <?php
        // Lógica para exibir mensagens de status (sucesso/erro)
        if (!empty($message_status)) {
            $message_class = '';
            if ($message_status == 'success') {
                $message_class = 'message success';
            } elseif ($message_status == 'error') {
                $message_class = 'message error';
            } elseif ($message_status == 'info') {
                $message_class = 'message info';
            }
            ?>
            <div class="<?php echo $message_class; ?>">
                <?php echo $message_text; ?>
            </div>
        <?php } ?>

        <div class="search-form">
            <form action="listar_agendamentos.php" method="GET" style="display: flex; width: 100%; gap: 10px;">
                <input type="text" name="search" placeholder="Buscar por cliente, placa, serviço..." value="<?php echo htmlspecialchars($search_term); ?>">
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
            <a href="agendar_servico.php" class="btn-add">Agendar Novo Serviço</a>
        </div>

        <?php if (empty($agendamentos)): ?>
            <p class="no-records">Nenhum agendamento encontrado. <?php echo (!empty($search_term)) ? 'Tente uma busca diferente.' : ''; ?></p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Data/Hora</th>
                        <th>Status</th>
                        <th>Cliente</th>
                        <th>Telefone Cliente</th>
                        <th>Veículo (Placa)</th>
                        <th>Marca/Modelo</th>
                        <th>Serviço</th>
                        <th>Tipo Estab.</th>
                        <th>Observações</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($agendamentos as $agendamento): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($agendamento['agendamento_id']); ?></td>
                        <td><?php echo (new DateTime($agendamento['data_hora']))->format('d/m/Y H:i'); ?></td>
                        <td><span class="status-<?php echo htmlspecialchars($agendamento['status']); ?>"><?php echo htmlspecialchars(ucfirst($agendamento['status'])); ?></span></td>
                        <td><?php echo htmlspecialchars($agendamento['cliente_nome']); ?></td>
                        <td><?php echo htmlspecialchars($agendamento['cliente_telefone']); ?></td>
                        <td><?php echo htmlspecialchars($agendamento['veiculo_placa']); ?></td>
                        <td><?php echo htmlspecialchars($agendamento['veiculo_marca'] . ' ' . $agendamento['veiculo_modelo']); ?></td>
                        <td><?php echo htmlspecialchars($agendamento['nome_servico']); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($agendamento['tipo_estabelecimento'])); ?></td>
                        <td><?php echo htmlspecialchars($agendamento['observacoes']); ?></td>
                        <td>
                            <?php
                            $agendamento_id = htmlspecialchars($agendamento['agendamento_id']);
                            $current_status = htmlspecialchars($agendamento['status']);
                            ?>

                            <div style="margin-bottom: 5px;">
                                <a href="editar_agendamento.php?id=<?php echo $agendamento_id; ?>" class="btn-edit">Editar</a>
                                <a href="excluir_agendamento.php?id=<?php echo $agendamento_id; ?>" class="btn-delete" onclick="return confirm('Tem certeza que deseja excluir este agendamento?');">Excluir</a>
                            </div>

                            <form action="atualizar_status.php" method="POST" style="display: inline-block;">
                                <input type="hidden" name="agendamento_id" value="<?php echo $agendamento_id; ?>">
                                <input type="hidden" name="redirect_url" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">

                                <?php if ($current_status == 'pendente' || $current_status == 'confirmado'): ?>
                                    <button type="submit" name="new_status" value="em_andamento" class="btn btn-start-service" title="Marcar como Em Andamento" <?php echo ($current_status == 'em_andamento') ? 'disabled' : ''; ?>>
                                        Iniciar Serviço
                                    </button>
                                <?php endif; ?>

                                <?php if ($current_status == 'em_andamento'): ?>
                                    <button type="submit" name="new_status" value="concluido" class="btn btn-finish-service" title="Marcar como Concluído">
                                        Finalizar
                                    </button>
                                <?php endif; ?>

                                <?php if ($current_status != 'concluido' && $current_status != 'cancelado'): ?>
                                    <button type="submit" name="new_status" value="cancelado" class="btn btn-cancel-service" title="Marcar como Cancelado">
                                        Não Comp. / Cancelar
                                    </button>
                                <?php endif; ?>

                                <?php if ($current_status == 'pendente'): ?>
                                    <button type="submit" name="new_status" value="confirmado" class="btn btn-confirm-service" title="Marcar como Confirmado">
                                        Confirmar
                                    </button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>