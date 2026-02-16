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

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigo_peca = trim($_POST['codigo_peca']);
    $nome_peca = trim($_POST['nome_peca']);
    $tipo_item = trim($_POST['tipo_item']);
    $fabricante = trim($_POST['fabricante']);
    $preco_custo = parseBrazilianCurrency($_POST['preco_custo']);
    $preco_venda = parseBrazilianCurrency($_POST['preco_venda']);
    $quantidade = (int)$_POST['quantidade'];

    if (empty($codigo_peca) || empty($nome_peca) || empty($tipo_item) || $preco_custo < 0 || $preco_venda < 0) {
        $message = "Todos os campos obrigatórios precisam ser preenchidos.";
        $message_type = 'error';
    } else {
        // SQL corrigido com os nomes de colunas da sua tabela
        $sql = "INSERT INTO pecas (codigo_peca, nome_peca, tipo_item, fabricante, preco_custo, preco_venda, quantidade) VALUES (?, ?, ?, ?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssddi", $codigo_peca, $nome_peca, $tipo_item, $fabricante, $preco_custo, $preco_venda, $quantidade);
            if ($stmt->execute()) {
                $message = "Item cadastrado com sucesso!";
                $message_type = 'success';
                header("Location: listar_pecas.php?status=$message_type&msg=" . urlencode($message));
                exit();
            } else {
                $message = "Erro ao cadastrar o item: " . $stmt->error;
                $message_type = 'error';
            }
            $stmt->close();
        } else {
            $message = "Erro na preparação da consulta: " . $conn->error;
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
    <title>Cadastrar Item de Estoque</title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #343a40;
            color: #f8f9fa;
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background-color: #495057;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
        }
        .container h2 {
            color: #007bff;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #6c757d;
            border-radius: 5px;
            box-sizing: border-box;
            background-color: #343a40;
            color: #f8f9fa;
        }
        .form-group input[type="submit"] {
            background-color: #28a745;
            color: white;
            cursor: pointer;
            border: none;
            padding: 12px;
            border-radius: 5px;
            font-size: 1.1em;
            transition: background-color 0.3s ease;
        }
        .form-group input[type="submit"]:hover {
            background-color: #218838;
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid transparent;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        .btn-back {
            background-color: #6c757d;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        .btn-back:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Cadastrar Novo Item de Estoque</h2>
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <form action="" method="POST">
            <div class="form-group">
                <label for="tipo_item">Tipo de Item:</label>
                <select name="tipo_item" id="tipo_item" required>
                    <option value="">Selecione o tipo</option>
                    <option value="peca_automotiva">Peça Automotiva</option>
                    <option value="pneu">Pneu</option>
                    <option value="acessorio_borracharia">Acessório de Borracharia</option>
                </select>
            </div>
            <div class="form-group">
                <label for="codigo_peca">Código do Item:</label>
                <input type="text" id="codigo_peca" name="codigo_peca" required>
            </div>
            <div class="form-group">
                <label for="nome_peca">Nome do Item:</label>
                <input type="text" id="nome_peca" name="nome_peca" required>
            </div>
            <div class="form-group">
                <label for="fabricante">Fabricante:</label>
                <input type="text" id="fabricante" name="fabricante">
            </div>
            <div class="form-group">
                <label for="preco_custo">Preço de Compra (R$):</label>
                <input type="text" id="preco_custo" name="preco_custo" required value="0,00" onfocus="this.select()">
            </div>
            <div class="form-group">
                <label for="preco_venda">Preço de Venda (R$):</label>
                <input type="text" id="preco_venda" name="preco_venda" required value="0,00" onfocus="this.select()">
            </div>
            <div class="form-group">
                <label for="quantidade">Quantidade em Estoque:</label>
                <input type="number" id="quantidade" name="quantidade" required min="0" value="0">
            </div>
            <div class="form-group">
                <input type="submit" value="Cadastrar Item">
            </div>
        </form>
        <a href="listar_pecas.php" class="btn-back">Voltar para a Lista</a>
    </div>

    <script>
        // JS para formatar o valor como moeda brasileira
        function formatarMoeda(input) {
            let value = input.value.replace(/\D/g, ''); // Remove tudo que não é dígito
            value = (value / 100).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            input.value = value;
        }

        const precoCustoInput = document.getElementById('preco_custo');
        const precoVendaInput = document.getElementById('preco_venda');
        
        precoCustoInput.addEventListener('input', () => formatarMoeda(precoCustoInput));
        precoVendaInput.addEventListener('input', () => formatarMoeda(precoVendaInput));
    </script>
</body>
</html>