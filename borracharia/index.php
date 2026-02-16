<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['tipo_servico'] != 'borracharia' && $_SESSION['tipo_servico'] != 'admin')) {
    header("Location: ../login.php"); 
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Borracharia</title>
    <link rel="stylesheet" href="../css/style.css"> 
    <style>
        /* Estilos específicos para o fundo da página da borracharia */
        html, body {
            height: 100%; /* Garante que o html e body ocupem 100% da altura da viewport */
            margin: 0;
            padding: 0;
        }

        body {
            background-image: url('../img/borracharia_fundo.jpg'); /* Caminho para a imagem da borracharia */
            background-repeat: no-repeat;
            background-size: cover; /* Cobre todo o fundo, redimensionando a imagem */
            background-position: center center; /* Centraliza a imagem */
            background-attachment: fixed; /* Mantém a imagem fixa durante a rolagem */
            /* display: flex; /* Se o conteúdo estiver flutuando, descomente */
            /* flex-direction: column; */
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-header">
            <h2>Dashboard da Borracharia</h2>
            <p>Bem-vindo, **<?php echo htmlspecialchars($_SESSION['username']); ?>**! Gerencie agendamentos e o estoque de pneus.</p>
        </div>

        <ul class="module-links">
            <li>
                <h3>Gerenciar Clientes</h3>
                <p>Cadastre e visualize clientes da borracharia.</p>
                <a href="../clientes/listar_clientes.php" class="btn-access">Acessar Clientes</a>
            </li>
            <li>
                <h3>Agendamentos de Serviços</h3>
                <p>Visualize, agende e gerencie todos os serviços da borracharia.</p>
                <a href="../agendamentos/listar_agendamentos.php" class="btn-access">Gerenciar Agendamentos</a>
            </li>
            <li>
                <h3>Estoque de Pneus e Acessórios</h3>
                    <p>Mantenha o controle do seu inventário de pneus, câmaras e outros itens.</p>
                    <a href="../pecas/listar_pecas.php" class="btn-access">Acessar Estoque</a>
            </li>
            <li>
                <h3>Histórico de Clientes</h3>
                <p>Consulte os serviços realizados e o histórico de cada cliente.</p>
                <a href="#" class="btn-access">Acessar Histórico</a> 
            </li>
            <li>
                <h3>Gerenciar Solicitações de Exclusão</h3>
                <p>Aprovar ou rejeitar pedidos de exclusão de clientes de outros estabelecimentos.</p>
                <a href="../clientes/gerenciar_solicitacoes_exclusao.php" class="btn-access">Ver Solicitações</a>
            </li>
        </ul>

        <div class="logout-button">
            <a href="../logout.php">Sair da Plataforma</a>
        </div>
    </div>
</body>
</html>