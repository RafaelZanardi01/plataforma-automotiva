<?php
session_start();

include_once '../includes/db_config.php';

// Permissões: Mecânica, Admin
$allowed_roles = ['admin', 'mecanica'];
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['tipo_servico'], $allowed_roles)) {
    header("Location: ../login.php");
    exit();
}

$message = ''; // Para mensagens de sucesso ou erro
$message_type = ''; // 'success' ou 'error'

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
$veiculos_json = json_encode($veiculos_all); // Converte array PHP de veículos para JSON


// --- Processar o formulário de abertura de OS (POST) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cliente_id = (int)($_POST['cliente_id'] ?? 0);
    $veiculo_id = (int)($_POST['veiculo_id'] ?? 0);
    $problema_relatado = trim($_POST['problema_relatado'] ?? '');

    // Validação básica
    if ($cliente_id <= 0 || $veiculo_id <= 0 || empty($problema_relatado)) {
        $message = 'Por favor, preencha todos os campos obrigatórios (Cliente, Veículo, Problema Relatado).';
        $message_type = 'error';
    } else {
        // Inserir a nova Ordem de Serviço
        // O status inicial é 'aberta', e data_abertura é DEFAULT CURRENT_TIMESTAMP no BD
        $sql_insert = "INSERT INTO ordens_servico (cliente_id, veiculo_id, problema_relatado, status) VALUES (?, ?, ?, 'aberta')";
        
        if ($stmt_insert = $conn->prepare($sql_insert)) {
            $stmt_insert->bind_param("iis", $cliente_id, $veiculo_id, $problema_relatado);

            if ($stmt_insert->execute()) {
                $new_os_id = $conn->insert_id; // Pega o ID da OS recém-criada
                $message = 'Ordem de Serviço #' . $new_os_id . ' aberta com sucesso!';
                $message_type = 'success';
                // Opcional: Redirecionar para a página de edição/detalhes da OS recém-criada
                header("Location: editar_os.php?id=" . $new_os_id . "&status=success&msg=" . urlencode($message));
                exit();
            } else {
                $message = 'Erro ao abrir Ordem de Serviço: ' . $stmt_insert->error;
                $message_type = 'error';
            }
            $stmt_insert->close();
        } else {
            $message = 'Erro na preparação da consulta de abertura de OS: ' . $conn->error;
            $message_type = 'error';
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
    <title>Abrir Nova Ordem de Serviço</title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        /* Reuso de estilos de formulários anteriores */
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; font-size: 0.95em; }
        .form-group input[type="text"],
        .form-group input[type="number"],
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
        #veiculo_id option.hidden { display: none; }

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
        <h2>Abrir Nova Ordem de Serviço</h2>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="abrir_os.php" method="POST">
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
                <label for="problema_relatado">Problema Relatado pelo Cliente:</label>
                <textarea id="problema_relatado" name="problema_relatado" required></textarea>
            </div>

            <div class="form-actions">
                <a href="listar_os.php" class="btn-back">Voltar para a Lista de OS</a>
                <input type="submit" value="Abrir OS">
            </div>
        </form>
    </div>

    <script>
        // --- JavaScript para filtrar veículos com base no cliente selecionado ---
        const clienteSelect = document.getElementById('cliente_id');
        const veiculoSelect = document.getElementById('veiculo_id');
        const allVehicles = <?php echo $veiculos_json; ?>;

        function filterVehicles() {
            const selectedClientId = clienteSelect.value;
            veiculoSelect.innerHTML = '<option value="">Selecione um veículo</option>';
            veiculoSelect.disabled = true;

            if (selectedClientId) {
                const clientVehicles = allVehicles.filter(vehicle => vehicle.cliente_id == selectedClientId);

                if (clientVehicles.length > 0) {
                    veiculoSelect.disabled = false;
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
            // Resetar o veículo selecionado se o cliente mudar
            veiculoSelect.value = "";
        }

        clienteSelect.addEventListener('change', filterVehicles);
        filterVehicles(); // Chama ao carregar a página
    </script>
</body>
</html>