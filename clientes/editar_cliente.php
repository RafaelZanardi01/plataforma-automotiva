<?php
session_start();

include_once '../includes/db_config.php';

// Permissões: Admin, Borracharia, Autopeças, Mecânica
$allowed_roles = ['admin', 'borracharia', 'autopecas', 'mecanica'];
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['tipo_servico'], $allowed_roles)) {
    header("Location: ../login.php");
    exit();
}

$message = '';
$message_type = '';
$cliente_data = null;

// --- Lógica para buscar os dados do cliente (ao carregar a página ou após POST) ---
// Obtém o ID do cliente da URL
$cliente_id_param = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);

if ($cliente_id_param <= 0) {
    // Redireciona de volta para a lista se o ID for inválido ao carregar a página
    header("Location: listar_clientes.php?status=error&msg=" . urlencode("ID do cliente não especificado ou inválido."));
    exit();
} else {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) { die("Erro de conexão: " . $conn->connect_error); }

    // --- Processar o formulário de atualização (POST) ---
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $nome = trim($_POST['nome'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $email = trim(strtolower($_POST['email'] ?? ''));
        $cpf_cnpj = trim($_POST['cpf_cnpj'] ?? '');
        $endereco = trim($_POST['endereco'] ?? '');
        $tipo_cliente = trim($_POST['tipo_cliente'] ?? '');

        // Validação básica
        if (empty($nome) || empty($telefone) || empty($cpf_cnpj) || empty($tipo_cliente)) {
            $message = 'Por favor, preencha todos os campos obrigatórios (Nome, Telefone, CPF/CNPJ, Tipo de Cliente).';
            $message_type = 'error';
        } elseif (!in_array($tipo_cliente, ['PF', 'PJ'])) {
            $message = 'Tipo de Cliente inválido. Selecione PF ou PJ.';
            $message_type = 'error';
        } else {
            // --- Verificação de Duplicidade para CPF/CNPJ (ignorando o próprio cliente que está sendo editado) ---
            $sql_check_duplicate = "SELECT COUNT(*) FROM clientes WHERE cpf_cnpj = ? AND id != ?";
            if ($stmt_check = $conn->prepare($sql_check_duplicate)) {
                $stmt_check->bind_param("si", $cpf_cnpj, $cliente_id_param);
                $stmt_check->execute();
                $stmt_check->bind_result($count_duplicates);
                $stmt_check->fetch();
                $stmt_check->close();

                if ($count_duplicates > 0) {
                    $message = 'Erro: CPF/CNPJ já cadastrado para outro cliente no sistema.';
                    $message_type = 'error';
                } else {
                    // --- Atualização do Cliente ---
                    $sql_update = "UPDATE clientes SET nome = ?, telefone = ?, email = ?, cpf_cnpj = ?, endereco = ?, tipo_cliente = ? WHERE id = ?";

                    if ($stmt_update = $conn->prepare($sql_update)) {
                        $stmt_update->bind_param("ssssssi", $nome, $telefone, $email, $cpf_cnpj, $endereco, $tipo_cliente, $cliente_id_param);

                        if ($stmt_update->execute()) {
                            $message = 'Cliente "' . htmlspecialchars($nome) . '" atualizado com sucesso!';
                            $message_type = 'success';
                            // Após sucesso, os dados devem ser recarregados no formulário para refletir a atualização
                        } else {
                            $message = 'Erro ao atualizar cliente: ' . $stmt_update->error;
                            $message_type = 'error';
                        }
                        $stmt_update->close();
                    } else {
                        $message = 'Erro na preparação da consulta de atualização: ' . $conn->error;
                        $message_type = 'error';
                    }
                }
            } else {
                 $message = 'Erro na preparação da consulta de verificação de duplicidade: ' . $conn->error;
                 $message_type = 'error';
            }
        }
    }

    // --- Recarregar os dados do cliente (necessário após POST ou no carregamento inicial) ---
    // Isso garante que o formulário exiba os dados mais recentes após uma atualização ou erro
    $sql_fetch_client = "SELECT id, nome, telefone, email, cpf_cnpj, endereco, tipo_cliente FROM clientes WHERE id = ?";
    if ($stmt_fetch = $conn->prepare($sql_fetch_client)) {
        $stmt_fetch->bind_param("i", $cliente_id_param);
        $stmt_fetch->execute();
        $result_fetch = $stmt_fetch->get_result();
        if ($result_fetch->num_rows > 0) {
            $cliente_data = $result_fetch->fetch_assoc();
        } else {
            // Se o cliente não for encontrado mesmo após o POST (ex: foi excluído por outro user)
            header("Location: listar_clientes.php?status=error&msg=" . urlencode("Cliente não encontrado ou excluído."));
            exit();
        }
        $stmt_fetch->close();
    } else {
        $message = 'Erro na preparação da consulta de busca: ' . $conn->error;
        $message_type = 'error';
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente</title>
    <link rel="stylesheet" href="../../css/style.css"> <style>
        /* Reuso de estilos de form-group e form-actions */
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; font-size: 0.95em; }
        .form-group input[type="text"],
        .form-group input[type="email"],
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
        .form-group input[type="text"]:focus,
        .form-group input[type="email"]:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            outline: none;
        }
        .form-group textarea { resize: vertical; min-height: 100px; }
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
            background-color: #007bff;
            color: white;
        }
        .form-actions input[type="submit"]:hover {
            background-color: #0056b3;
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
            .container { margin: 20px 15px; padding: 20px; }
            .form-actions { flex-direction: column; align-items: stretch; }
            .form-actions input[type="submit"], .form-actions .btn-back { width: 100%; margin: 5px 0; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Editar Cliente</h2>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($cliente_data): // Exibe o formulário apenas se os dados do cliente foram carregados ?>
        <form action="editar_cliente.php" method="POST">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($cliente_data['id']); ?>">

            <div class="form-group">
                <label for="nome">Nome Completo:</label>
                <input type="text" id="nome" name="nome" required value="<?php echo htmlspecialchars($cliente_data['nome'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="telefone">Telefone (DDD + Número):</label>
                <input type="text" id="telefone" name="telefone" required value="<?php echo htmlspecialchars($cliente_data['telefone'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($cliente_data['email'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="cpf_cnpj">CPF/CNPJ:</label>
                <input type="text" id="cpf_cnpj" name="cpf_cnpj" required value="<?php echo htmlspecialchars($cliente_data['cpf_cnpj'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="endereco">Endereço Completo (Rua, Número, Bairro, Cidade, Estado, CEP):</label>
                <textarea id="endereco" name="endereco"><?php echo htmlspecialchars($cliente_data['endereco'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label for="tipo_cliente">Tipo de Cliente:</label>
                <select id="tipo_cliente" name="tipo_cliente" required>
                    <option value="">Selecione</option>
                    <option value="PF" <?php echo (($cliente_data['tipo_cliente'] ?? '') == 'PF') ? 'selected' : ''; ?>>Pessoa Física (PF)</option>
                    <option value="PJ" <?php echo (($cliente_data['tipo_cliente'] ?? '') == 'PJ') ? 'selected' : ''; ?>>Pessoa Jurídica (PJ)</option>
                </select>
            </div>

            <div class="form-actions">
                <a href="listar_clientes.php" class="btn-back">Voltar para a Lista</a>
                <input type="submit" value="Salvar Alterações">
            </div>
        </form>
        <?php else: // Mensagem se o cliente não for encontrado ou ID inválido ?>
            <p class="no-records" style="text-align: center; color: red;">Não foi possível carregar os dados do cliente. <a href="listar_clientes.php">Voltar para a lista.</a></p>
        <?php endif; ?>
    </div>
</body>
</html>