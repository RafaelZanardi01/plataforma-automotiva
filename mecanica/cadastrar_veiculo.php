<?php
session_start();

// Inclui as configurações do banco de dados
include_once '../includes/db_config.php';

// Verifica se o usuário está logado e se tem permissão (mecanica ou admin)
if (!isset($_SESSION['user_id']) || ($_SESSION['tipo_servico'] != 'mecanica' && $_SESSION['tipo_servico'] != 'admin')) {
    header("Location: ../login.php");
    exit();
}

$message = ''; // Para mensagens de sucesso ou erro
$message_type = ''; // 'success' ou 'error'

// --- Lógica para buscar clientes para o dropdown ---
$clientes = [];
$conn_clientes = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn_clientes->connect_error) {
    die("Erro de conexão ao buscar clientes: " . $conn_clientes->connect_error);
}
$sql_clientes = "SELECT id, nome, cpf_cnpj FROM clientes ORDER BY nome ASC";
$result_clientes = $conn_clientes->query($sql_clientes);
if ($result_clientes->num_rows > 0) {
    while($row_cliente = $result_clientes->fetch_assoc()) {
        $clientes[] = $row_cliente;
    }
}
$conn_clientes->close();
// --- Fim da lógica para buscar clientes ---


// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Coleta e sanitiza os dados do formulário
    $cliente_id = (int)($_POST['cliente_id'] ?? 0);
    $marca = trim($_POST['marca'] ?? '');
    $modelo = trim($_POST['modelo'] ?? '');
    $ano = (int)($_POST['ano'] ?? 0);
    $placa = trim(strtoupper($_POST['placa'] ?? '')); // Converte placa para maiúsculas
    $chassi = trim(strtoupper($_POST['chassi'] ?? '')); // Converte chassi para maiúsculas

    // Validação básica
    if ($cliente_id <= 0 || empty($marca) || empty($modelo) || empty($placa) || empty($chassi) || $ano <= 1900) {
        $message = 'Por favor, preencha todos os campos obrigatórios corretamente.';
        $message_type = 'error';
    } else {
        // Conecta ao banco de dados para inserir o veículo
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die("Erro de conexão: " . $conn->connect_error);
        }

        // Verifica se a placa ou chassi já existem (UNIQUE no BD)
        $sql_check_duplicate = "SELECT COUNT(*) FROM veiculos WHERE placa = ? OR chassi = ?";
        if ($stmt_check = $conn->prepare($sql_check_duplicate)) {
            $stmt_check->bind_param("ss", $placa, $chassi);
            $stmt_check->execute();
            $stmt_check->bind_result($count_duplicates);
            $stmt_check->fetch();
            $stmt_check->close();

            if ($count_duplicates > 0) {
                $message = 'Erro: Placa ou Chassi já cadastrados no sistema.';
                $message_type = 'error';
            } else {
                // Prepara a consulta SQL para inserção de dados (Prepared Statement)
                $sql = "INSERT INTO veiculos (cliente_id, marca, modelo, ano, placa, chassi) VALUES (?, ?, ?, ?, ?, ?)";

                if ($stmt = $conn->prepare($sql)) {
                    // 'isiss' -> tipos dos parâmetros (int, string, string, int, string, string)
                    $stmt->bind_param("ississ", $cliente_id, $marca, $modelo, $ano, $placa, $chassi);

                    if ($stmt->execute()) {
                        $message = 'Veículo (' . htmlspecialchars($placa) . ') adicionado com sucesso!';
                        $message_type = 'success';
                        // Limpar os campos do formulário após sucesso (opcional)
                        // $marca = $modelo = $placa = $chassi = ''; $ano = 0; $cliente_id = 0;
                    } else {
                        $message = 'Erro ao adicionar veículo: ' . $stmt->error;
                        $message_type = 'error';
                    }
                    $stmt->close();
                } else {
                    $message = 'Erro na preparação da consulta: ' . $conn->error;
                    $message_type = 'error';
                }
            }
        } else {
             $message = 'Erro na preparação da consulta de verificação: ' . $conn->error;
             $message_type = 'error';
        }
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Veículo - Mecânica</title>
    <link rel="stylesheet" href="../css/style.css"> <style>
        /* Estilos específicos para o formulário de cadastro de veículo */
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; font-size: 0.95em; }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group select { /* Adicionado 'select' aqui */
            width: calc(100% - 24px);
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1em;
            box-sizing: border-box;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            background-color: #f8f8f8; /* Fundo do input */
        }
        .form-group input[type="text"]:focus,
        .form-group input[type="number"]:focus,
        .form-group select:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            outline: none;
        }
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
        }
        .form-actions input[type="submit"],
        .form-actions .btn-back {
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.2s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .form-actions input[type="submit"] {
            background-color: #28a745;
            color: white;
        }
        .form-actions input[type="submit"]:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }
        .form-actions .btn-back {
            background-color: #6c757d;
            color: white;
        }
        .form-actions .btn-back:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }
        .message { padding: 12px 20px; border-radius: 6px; margin-bottom: 20px; font-size: 0.95em; display: flex; align-items: center; gap: 10px; }
        .message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        /* Responsividade */
        @media (max-width: 768px) {
            .container {
                margin: 20px 15px;
                padding: 20px;
            }
            .form-actions {
                flex-direction: column;
                align-items: stretch;
            }
            .form-actions input[type="submit"],
            .form-actions .btn-back {
                width: 100%;
                margin: 5px 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Cadastrar Novo Veículo</h2>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="cadastrar_veiculo.php" method="POST">
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
                <label for="marca">Marca:</label>
                <input type="text" id="marca" name="marca" required>
            </div>

            <div class="form-group">
                <label for="modelo">Modelo:</label>
                <input type="text" id="modelo" name="modelo" required>
            </div>

            <div class="form-group">
                <label for="ano">Ano:</label>
                <input type="number" id="ano" name="ano" min="1900" max="<?php echo date('Y') + 1; ?>" required>
            </div>

            <div class="form-group">
                <label for="placa">Placa (Ex: ABC1B23):</label>
                <input type="text" id="placa" name="placa" required maxlength="7">
            </div>

            <div class="form-group">
                <label for="chassi">Chassi:</label>
                <input type="text" id="chassi" name="chassi" required maxlength="17">
            </div>

            <div class="form-actions">
                <a href="index.php" class="btn-back">Voltar para o Dashboard</a>
                <input type="submit" value="Cadastrar Veículo">
            </div>
        </form>
    </div>
</body>
</html>