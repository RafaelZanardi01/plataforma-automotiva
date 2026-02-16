<?php
session_start(); // Inicia a sessão para guardar informações do usuário

// Inclui o arquivo de configuração do banco de dados
include_once 'includes/db_config.php';

$error_message = ''; // Variável para armazenar mensagens de erro

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? ''); // Use ?? '' para evitar erros se o campo não existir
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error_message = "Por favor, preencha todos os campos.";
    } else {
        // Conecta ao banco de dados usando MySQLi
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($conn->connect_error) {
            // Este die pode ser mais amigável em produção, mas para dev está ok
            die("Conexão falhou: " . $conn->connect_error);
        }

        // Prepara a consulta SQL para evitar injeção SQL
        $stmt = $conn->prepare("SELECT id, username, password, tipo_servico FROM usuarios WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($id, $db_username, $db_password_hash, $tipo_servico);
        $stmt->fetch();

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
                case 'autopecas':
                    header("Location: autopecas/index.php");
                    break;
                case 'mecanica':
                    header("Location: mecanica/index.php");
                    break;
                case 'admin':
                    header("Location: admin/index.php");
                    break;
                default:
                    // Se o tipo de serviço do usuário for inesperado, redireciona de volta para o login
                    session_unset();
                    session_destroy();
                    header("Location: login.php?erro=tipo_servico_invalido");
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
    <style>
        /* Estilos globais para a página de login */
        html, body {
            height: 100%; /* Garante que o html e body ocupem 100% da altura da viewport */
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #f8f9fa; /* Cor de texto padrão para o corpo, sobre a imagem */
            display: flex;
            justify-content: center;
            align-items: center; /* Centraliza o conteúdo vertical e horizontalmente */
            flex-direction: column; /* Para alinhar o conteúdo em coluna se houver mais de um item */
        }

        body {
            background-image: url('img/login_fundo.jpg'); /* Caminho para a sua imagem de login */
            background-repeat: no-repeat;
            background-size: cover; /* Faz a imagem cobrir toda a área */
            background-position: center center; /* Centraliza a imagem */
            background-attachment: fixed; /* Mantém a imagem fixa na rolagem */
        }

        .login-container {
            background-color: rgba(33, 37, 41, 0.9); /* Cinza escuro semi-transparente */
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.5); /* Sombra mais forte */
            width: 100%;
            max-width: 450px; /* Largura um pouco maior para o formulário */
            text-align: center;
            border-top: 5px solid #007bff; /* Detalhe azul no topo */
            color: #e9ecef; /* Cor do texto dentro do container de login */
        }

        .login-container h2 {
            margin-bottom: 30px;
            color: #007bff; /* Título azul */
            font-size: 2.2em;
            font-weight: 600;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.7); /* Sombra para o título */
        }

        .login-container label {
            display: block;
            text-align: left;
            margin-bottom: 8px;
            color: #adb5bd; /* Cor mais clara para o label */
            font-weight: 500;
        }

        .login-container input[type="text"],
        .login-container input[type="password"] {
            width: calc(100% - 24px); /* Ajuste de largura */
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #495057; /* Borda mais escura */
            border-radius: 6px;
            background-color: #343a40; /* Fundo do input mais escuro */
            color: #f8f9fa; /* Cor do texto do input */
            font-size: 1em;
            box-sizing: border-box; /* Inclui padding e border na largura total */
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .login-container input[type="text"]:focus,
        .login-container input[type="password"]:focus {
            border-color: #007bff; /* Borda azul ao focar */
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25); /* Sombra suave ao focar */
            outline: none;
        }

        .login-container button[type="submit"] {
            background-color: #28a745; /* Botão verde */
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        .login-container button[type="submit"]:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }

        .error-message {
            color: #dc3545; /* Vermelho para erro */
            margin-bottom: 20px;
            font-weight: bold;
            background-color: rgba(220, 53, 69, 0.1); /* Fundo suave para o erro */
            border: 1px solid #dc3545;
            padding: 10px;
            border-radius: 5px;
        }

        /* Opcional: Link para voltar à página principal */
        .back-to-home {
            margin-top: 20px;
            font-size: 0.9em;
        }
        .back-to-home a {
            color: #007bff;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .back-to-home a:hover {
            color: #0056b3;
            text-decoration: underline;
        }

        /* Responsividade básica */
        @media (max-width: 500px) {
            .login-container {
                margin: 20px;
                padding: 30px;
            }
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
        <div class="back-to-home">
            <a href="index.php">Voltar para a Página Principal</a>
        </div>
    </div>
</body>
</html>