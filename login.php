<?php
session_start(); // Inicia a sessão para guardar informações do usuário

// Inclui o arquivo de configuração do banco de dados
include_once 'includes/db_config.php';

$error_message = ''; // Variável para armazenar mensagens de erro

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']); // Remove espaços em branco
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error_message = "Por favor, preencha todos os campos.";
    } else {
        // Conecta ao banco de dados usando MySQLi (como configuramos antes)
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($conn->connect_error) {
            die("Conexão falhou: " . $conn->connect_error);
        }

        // Prepara a consulta SQL para evitar injeção SQL
        // Seleciona id, username, senha (hash) e tipo_servico
        $stmt = $conn->prepare("SELECT id, username, password, tipo_servico FROM usuarios WHERE username = ?");
        $stmt->bind_param("s", $username); // 's' indica que o parâmetro é uma string
        $stmt->execute();
        $stmt->store_result(); // Armazena o resultado para poder verificar o número de linhas
        $stmt->bind_result($id, $db_username, $db_password_hash, $tipo_servico); // Associa as colunas a variáveis
        $stmt->fetch(); // Busca o resultado da consulta

        if ($stmt->num_rows > 0 && password_verify($password, $db_password_hash)) {
            // Login bem-sucedido
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $db_username;
            $_SESSION['tipo_servico'] = $tipo_servico; // Guarda o tipo de serviço na sessão

            // Redireciona com base no tipo de serviço
            switch ($tipo_servico) {
                case 'borracharia':
                    header("Location: borracharia/index.php");
                    break;
                case 'autopeças':
                    header("Location: autopeças/index.php");
                    break;
                case 'mecanica':
                    header("Location: mecanica/index.php");
                    break;
                case 'admin': // Se você tiver um usuário administrador geral
                    header("Location: admin/index.php"); // Crie uma pasta 'admin' se for usar
                    break;
                default:
                    // Redirecionamento padrão ou mensagem de erro se o tipo de serviço for desconhecido
                    header("Location: dashboard.php"); // Ou uma página genérica de erro
                    break;
            }
            exit(); // Importante: Garante que o script pare de executar após o redirecionamento
        } else {
            $error_message = "Usuário ou senha inválidos.";
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Plataforma Automotiva Integrada</title>
    <link rel="stylesheet" href="css/style.css"> <style>
        /* Estilos básicos para o formulário de login (pode mover para style.css) */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .login-container h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .login-container label {
            display: block;
            text-align: left;
            margin-bottom: 5px;
            color: #555;
        }
        .login-container input[type="text"],
        .login-container input[type="password"] {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .login-container button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        .login-container button:hover {
            background-color: #0056b3;
        }
        .error-message {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Acesso à Plataforma</h2>
        <?php if (!empty($error_message)) { ?>
            <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
        <?php } ?>
        <form action="login.php" method="POST">
            <label for="username">Usuário:</label>
            <input type="text" id="username" name="username" required autocomplete="username">

            <label for="password">Senha:</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">

            <button type="submit">Entrar</button>
        </form>
    </div>
</body>
</html>