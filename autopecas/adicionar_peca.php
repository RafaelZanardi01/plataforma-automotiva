<?php
session_start();

include_once '../includes/db_config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['tipo_servico'] != 'autopecas' && $_SESSION['tipo_servico'] != 'admin')) {
    header("Location: ../login.php");
    exit();
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome_peca = trim($_POST['nome_peca'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $fabricante = trim($_POST['fabricante'] ?? '');
    $codigo_peca = trim($_POST['codigo_peca'] ?? '');
    $quantidade = (int)($_POST['quantidade'] ?? 0);

    // --- MUDANÇA AQUI: Processamento de vírgula para ponto ---
    $preco_custo_input = trim($_POST['preco_custo'] ?? '0,00');
    $preco_custo = (float)str_replace(',', '.', $preco_custo_input);

    $preco_venda_input = trim($_POST['preco_venda'] ?? '0,00');
    $preco_venda = (float)str_replace(',', '.', $preco_venda_input);
    // --- FIM DA MUDANÇA ---

    $localizacao_estoque = trim($_POST['localizacao_estoque'] ?? '');

    if (empty($nome_peca) || empty($codigo_peca) || $quantidade <= 0 || $preco_venda <= 0) {
        $message = 'Por favor, preencha todos os campos obrigatórios e garanta que quantidade e preços sejam maiores que zero.';
        $message_type = 'error';
    } else {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($conn->connect_error) {
            die("Erro de conexão: " . $conn->connect_error);
        }

        $sql = "INSERT INTO pecas (nome_peca, descricao, fabricante, codigo_peca, quantidade, preco_custo, preco_venda, localizacao_estoque) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssiddd", $nome_peca, $descricao, $fabricante, $codigo_peca, $quantidade, $preco_custo, $preco_venda, $localizacao_estoque);

            if ($stmt->execute()) {
                $message = 'Peça "' . htmlspecialchars($nome_peca) . '" adicionada com sucesso!';
                $message_type = 'success';
            } else {
                $message = 'Erro ao adicionar peça: ' . $stmt->error;
                $message_type = 'error';
            }
            $stmt->close();
        } else {
            $message = 'Erro na preparação da consulta: ' . $conn->error;
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
    <title>Adicionar Peça - Autopeças</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        <style>
        /* Estilos globais/base (podem ir para style.css principal) */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #eef1f5; /* Um cinza claro suave */
            margin: 0;
            padding: 0;
            line-height: 1.6;
            color: #333;
        }

        /* Container principal do formulário */
        .container {
            background-color: #ffffff;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); /* Sombra mais suave */
            max-width: 800px;
            margin: 40px auto; /* Centraliza e adiciona margem superior/inferior */
            border-top: 5px solid #007bff; /* Detalhe azul no topo */
        }

        h2 {
            color: #007bff; /* Título azul */
            margin-bottom: 25px;
            text-align: center;
            font-size: 2em;
            font-weight: 600;
        }

        /* Mensagens de feedback (sucesso/erro) */
        .message {
            padding: 12px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 0.95em;
            display: flex;
            align-items: center;
            gap: 10px; /* Espaço entre ícone e texto */
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Agrupamento de campos do formulário */
        .form-group {
            margin-bottom: 20px; /* Mais espaço entre os grupos de campos */
        }

        .form-group label {
            display: block;
            margin-bottom: 8px; /* Mais espaço entre label e input */
            font-weight: 600; /* Negrito para os labels */
            color: #555;
            font-size: 0.95em;
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group textarea {
            width: calc(100% - 24px); /* Ajusta a largura com padding/border */
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1em;
            box-sizing: border-box; /* Garante que padding/border não aumentem a largura total */
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .form-group input[type="text"]:focus,
        .form-group input[type="number"]:focus,
        .form-group textarea:focus {
            border-color: #007bff; /* Borda azul ao focar */
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25); /* Sombra suave ao focar */
            outline: none; /* Remove o outline padrão do navegador */
        }

        .form-group textarea {
            resize: vertical; /* Permite redimensionar verticalmente */
            min-height: 100px; /* Altura mínima para textarea */
        }

        /* Estilo para a pequena dica de texto */
        .form-group small {
            display: block; /* Garante que a dica fique em sua própria linha */
            margin-top: 5px;
            color: #777;
            font-size: 0.85em;
        }

        /* Botões */
        .form-actions {
            display: flex; /* Alinha os botões lado a lado */
            justify-content: flex-end; /* Alinha os botões à direita */
            gap: 15px; /* Espaço entre os botões */
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
            text-decoration: none; /* Para o link parecer um botão */
            text-align: center;
            display: inline-block; /* Para o link parecer um botão */
        }

        .form-actions input[type="submit"] {
            background-color: #28a745; /* Verde para adicionar */
            color: white;
        }
        .form-actions input[type="submit"]:hover {
            background-color: #218838;
            transform: translateY(-2px); /* Efeito de "levantar" */
        }

        .form-actions .btn-back {
            background-color: #6c757d; /* Cinza para voltar */
            color: white;
        }
        .form-actions .btn-back:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }

        /* Responsividade básica para telas menores */
        @media (max-width: 768px) {
            .container {
                margin: 20px 15px;
                padding: 20px;
            }
            .form-actions {
                flex-direction: column; /* Botões empilhados em telas pequenas */
                align-items: stretch; /* Estica os botões */
            }
            .form-actions input[type="submit"],
            .form-actions .btn-back {
                width: 100%;
                margin: 5px 0;
            }
        }
    </style>
    </style>
</head>
<body>
    <div class="container">
        <h2>Adicionar Nova Peça ao Inventário</h2>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="adicionar_peca.php" method="POST">
            <div class="form-group">
                <label for="nome_peca">Nome da Peça:</label>
                <input type="text" id="nome_peca" name="nome_peca" required>
            </div>
            <div class="form-group">
                <label for="descricao">Descrição:</label>
                <textarea id="descricao" name="descricao"></textarea>
            </div>
            <div class="form-group">
                <label for="fabricante">Fabricante:</label>
                <input type="text" id="fabricante" name="fabricante">
            </div>
            <div class="form-group">
                <label for="codigo_peca">Código da Peça:</label>
                <input type="text" id="codigo_peca" name="codigo_peca" required>
            </div>
            <div class="form-group">
                <label for="quantidade">Quantidade:</label>
                <input type="number" id="quantidade" name="quantidade" min="1" required value="1"> </div>
            <div class="form-group">
                <label for="preco_custo">Preço de Custo (R$):</label>
                <input type="text" id="preco_custo" name="preco_custo" placeholder="0,00">
                <small>Use vírgula para centavos (ex: 12,50)</small>
            </div>
            <div class="form-group">
                <label for="preco_venda">Preço de Venda (R$):</label>
                <input type="text" id="preco_venda" name="preco_venda" required placeholder="0,00">
                <small>Use vírgula para centavos (ex: 25,99)</small>
            </div>
            <div class="form-group">
                <label for="localizacao_estoque">Localização no Estoque:</label>
                <input type="text" id="localizacao_estoque" name="localizacao_estoque">
            </div>
           <div class="form-actions">
                <a href="inventario.php" class="btn-back">Voltar para o Inventário</a>
                <input type="submit" value="Adicionar Peça">
            </div>
        </form>
    </div>
</body>
</html>