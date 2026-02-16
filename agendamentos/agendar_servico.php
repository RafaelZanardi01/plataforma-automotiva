<?php
session_start();

include_once '../includes/db_config.php';
include_once '../includes/functions.php'; // Inclui funções auxiliares

// Permissões: Admin, Borracharia, Autopeças (se precisar agendar algo), Mecânica
$allowed_roles = ['admin', 'borracharia', 'mecanica']; // Autopeças não agenda serviços diretamente
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['tipo_servico'], $allowed_roles)) {
    header("Location: ../login.php"); // Redireciona para o login se não autorizado
    exit();
}

$current_user_role = $_SESSION['tipo_servico'];
$message = '';
$message_type = '';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) { die("Erro de conexão: " . $conn->connect_error); }

// --- Lógica para buscar CLIENTES ---
$clientes = [];
$sql_clientes = "SELECT id, nome, cpf_cnpj FROM clientes ORDER BY nome ASC";
$result_clientes = $conn->query($sql_clientes);
if ($result_clientes->num_rows > 0) {
    while($row_cliente = $result_clientes->fetch_assoc()) {
        $clientes[] = $row_cliente;
    }
}

// --- Lógica para buscar VEÍCULOS (Todos os veículos, e JavaScript filtrará por cliente) ---
$veiculos_all = [];
$sql_veiculos = "SELECT v.id, v.placa, v.marca, v.modelo, v.cliente_id FROM veiculos v ORDER BY v.placa ASC";
$result_veiculos = $conn->query($sql_veiculos);
if ($result_veiculos->num_rows > 0) {
    while($row_veiculo = $result_veiculos->fetch_assoc()) {
        $veiculos_all[] = $row_veiculo;
    }
}
// Converte array PHP de veículos para JSON para uso no JavaScript
$veiculos_json = json_encode($veiculos_all);


// --- Lógica para buscar SERVIÇOS ---
$servicos_disponiveis = [];
$sql_servicos = "SELECT id, nome_servico, tipo_estabelecimento FROM servicos";
// Se não for admin, filtra os serviços pelo tipo de estabelecimento do usuário
if ($current_user_role != 'admin') {
    $sql_servicos .= " WHERE tipo_estabelecimento = ?";
    $stmt_servicos = $conn->prepare($sql_servicos);
    $stmt_servicos->bind_param("s", $current_user_role);
    $stmt_servicos->execute();
    $result_servicos = $stmt_servicos->get_result();
} else {
    // Admin vê todos os serviços
    $sql_servicos .= " ORDER BY tipo_estabelecimento, nome_servico ASC";
    $result_servicos = $conn->query($sql_servicos);
}

if ($result_servicos->num_rows > 0) {
    while($row_servico = $result_servicos->fetch_assoc()) {
        $servicos_disponiveis[] = $row_servico;
    }
}
if (isset($stmt_servicos)) { $stmt_servicos->close(); }


// --- Processar o formulário de agendamento (POST) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cliente_id = (int)($_POST['cliente_id'] ?? 0);
    $veiculo_id = (int)($_POST['veiculo_id'] ?? 0);
    $servico_id = (int)($_POST['servico_id'] ?? 0);
    $data_hora_str = trim($_POST['data_hora'] ?? ''); // String do input datetime-local
    $observacoes = trim($_POST['observacoes'] ?? '');

    // Validação básica
    if ($cliente_id <= 0 || $veiculo_id <= 0 || $servico_id <= 0 || empty($data_hora_str)) {
        $message = 'Por favor, preencha todos os campos obrigatórios (Cliente, Veículo, Serviço, Data/Hora).';
        $message_type = 'error';
    } else {
        // Obter o tipo_estabelecimento do serviço selecionado
        $selected_service_type = '';
        foreach ($servicos_disponiveis as $s) {
            if ($s['id'] == $servico_id) {
                $selected_service_type = $s['tipo_estabelecimento'];
                break;
            }
        }

        // Se o usuário não for admin, verifica se o tipo de serviço corresponde à sua role
        if ($current_user_role != 'admin' && $selected_service_type != $current_user_role) {
            $message = 'Você não tem permissão para agendar este tipo de serviço.';
            $message_type = 'error';
        } else {
            // Converte a string de data/hora para o formato DATETIME do MySQL (YYYY-MM-DD HH:MM:SS)
            $data_hora_mysql = date('Y-m-d H:i:s', strtotime($data_hora_str));

            // Inserir agendamento
            $sql_insert = "INSERT INTO agendamentos (cliente_id, veiculo_id, servico_id, data_hora, observacoes, tipo_estabelecimento, status) VALUES (?, ?, ?, ?, ?, ?, 'pendente')";
            
            if ($stmt_insert = $conn->prepare($sql_insert)) {
                $stmt_insert->bind_param("iissss", $cliente_id, $veiculo_id, $servico_id, $data_hora_mysql, $observacoes, $selected_service_type);

                if ($stmt_insert->execute()) {
                    $message = 'Agendamento para o cliente ID ' . htmlspecialchars($cliente_id) . ' realizado com sucesso!';
                    $message_type = 'success';
                    // Limpar campos do formulário (opcional)
                } else {
                    $message = 'Erro ao agendar serviço: ' . $stmt_insert->error;
                    $message_type = 'error';
                }
                $stmt_insert->close();
            } else {
                $message = 'Erro na preparação da consulta de agendamento: ' . $conn->error;
                $message_type = 'error';
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar Serviço</title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        /* Estilos reusados de formulários anteriores */
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; font-size: 0.95em; }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="datetime-local"], /* Adicionado datetime-local */
        .form-group select,
        .form-group textarea {
            width: calc(100% - 24px);
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1em;
            box-sizing: border-box;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            background-color: #f8f8f8;
            color: #333;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            border-color: #007bff; box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25); outline: none;
        }
        .form-group textarea { resize: vertical; min-height: 80px; }
        .form-actions { display: flex; justify-content: flex-end; gap: 15px; margin-top: 30px; }
        .form-actions input[type="submit"], .form-actions .btn-back {
            padding: 12px 25px; border: none; border-radius: 6px; cursor: pointer; font-size: 1em; font-weight: 600; transition: background-color 0.3s ease, transform 0.2s ease; text-decoration: none; display: inline-block; text-align: center;
        }
        .form-actions input[type="submit"] { background-color: #28a745; color: white; }
        .form-actions input[type="submit"]:hover { background-color: #218838; transform: translateY(-2px); }
        .form-actions .btn-back { background-color: #6c757d; color: white; }
        .form-actions .btn-back:hover { background-color: #5a6268; transform: translateY(-2px); }
        .message { padding: 12px 20px; border-radius: 6px; margin-bottom: 20px; font-size: 0.95em; display: flex; align-items: center; gap: 10px; }
        .message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .message.info { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }

        /* Estilos específicos para o dropdown de veículos dinâmico */
        #veiculo_id option.hidden {
            display: none;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .container { margin: 20px 15px; padding: 20px; }
            .form-actions { flex-direction: column; align-items: stretch; }
            .form-actions input[type="submit"], .form-actions .btn-back { width: 100%; margin: 5px 0; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Agendar Novo Serviço</h2>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="agendar_servico.php" method="POST">
            <div class="form-group">
                <label for="cliente_id">Cliente:</label>
                <select id="cliente_id" name="cliente_id" required>
                    <option value="">Selecione um cliente</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?php echo htmlspecialchars($cliente['id']); ?>">
                            <?php echo htmlspecialchars($cliente['nome'] . ' (' . $cliente['cpf_cnpj'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="veiculo_id">Veículo:</label>
                <select id="veiculo_id" name="veiculo_id" required disabled>
                    <option value="">Selecione um cliente primeiro</option>
                    <?php foreach ($veiculos_all as $veiculo): ?>
                        <option value="<?php echo htmlspecialchars($veiculo['id']); ?>" data-cliente-id="<?php echo htmlspecialchars($veiculo['cliente_id']); ?>" class="hidden">
                            <?php echo htmlspecialchars($veiculo['placa'] . ' - ' . $veiculo['marca'] . ' ' . $veiculo['modelo']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="servico_id">Serviço:</label>
                <select id="servico_id" name="servico_id" required>
                    <option value="">Selecione um serviço</option>
                    <?php foreach ($servicos_disponiveis as $servico): ?>
                        <option value="<?php echo htmlspecialchars($servico['id']); ?>" data-tipo-estabelecimento="<?php echo htmlspecialchars($servico['tipo_estabelecimento']); ?>">
                            <?php echo htmlspecialchars($servico['nome_servico'] . ' (' . ucfirst($servico['tipo_estabelecimento']) . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="data_hora">Data e Hora do Agendamento:</label>
                <input type="datetime-local" id="data_hora" name="data_hora" required>
            </div>

            <div class="form-group">
                <label for="observacoes">Observações:</label>
                <textarea id="observacoes" name="observacoes"></textarea>
            </div>

            <div class="form-actions">
                <a href="<?php
                    // Voltar para o dashboard correto de onde veio
                    if (isset($_SESSION['tipo_servico']) && $_SESSION['tipo_servico'] != 'admin') {
                        echo '../' . $_SESSION['tipo_servico'] . '/index.php';
                    } else {
                        echo '../admin/index.php';
                    }
                ?>" class="btn-back">Voltar para o Dashboard</a>
                <input type="submit" value="Agendar Serviço">
            </div>
        </form>
    </div>

    <script>
        // --- JavaScript para filtrar veículos com base no cliente selecionado ---
        const clienteSelect = document.getElementById('cliente_id');
        const veiculoSelect = document.getElementById('veiculo_id');
        const allVehicles = <?php echo $veiculos_json; ?>; // Todos os veículos do PHP

        function filterVehicles() {
            const selectedClientId = clienteSelect.value;
            veiculoSelect.innerHTML = '<option value="">Selecione um veículo</option>'; // Limpa opções anteriores
            veiculoSelect.disabled = true; // Desabilita por padrão

            if (selectedClientId) {
                const clientVehicles = allVehicles.filter(vehicle => vehicle.cliente_id == selectedClientId);

                if (clientVehicles.length > 0) {
                    veiculoSelect.disabled = false; // Habilita se houver veículos
                    clientVehicles.forEach(vehicle => {
                        const option = document.createElement('option');
                        option.value = vehicle.id;
                        option.textContent = `${vehicle.placa} - ${vehicle.marca} ${vehicle.modelo}`;
                        veiculoSelect.appendChild(option);
                    });
                } else {
                    const option = document.createElement('option');
                    option.value = "";
                    option.textContent = "Nenhum veículo para este cliente";
                    veiculoSelect.appendChild(option);
                }
            } else {
                 const option = document.createElement('option');
                 option.value = "";
                 option.textContent = "Selecione um cliente primeiro";
                 veiculoSelect.appendChild(option);
            }
        }

        // Adiciona o evento de mudança ao select de clientes
        clienteSelect.addEventListener('change', filterVehicles);

        // Chame a função uma vez ao carregar a página caso haja um cliente selecionado (ex: em caso de erro no POST)
        filterVehicles();

        // --- JavaScript para filtrar serviços com base na role do usuário (se não for admin) ---
        // Este filtro é mais para feedback visual e para o usuário não se confundir.
        // A validação real de permissão de agendamento é feita no PHP.
        const servicoSelect = document.getElementById('servico_id');
        const userRole = '<?php echo $current_user_role; ?>';
    
    </script>
</body>
</html>