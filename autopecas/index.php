<?php
session_start();

// Verifica se o usuário está logado e se é do tipo correto
if (!isset($_SESSION['user_id']) || ($_SESSION['tipo_servico'] != 'autopecas' && $_SESSION['tipo_servico'] != 'admin')) {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Autopeças</title>
    <link rel="stylesheet" href="../css/style.css"> 
    <style>
    /* Estilos específicos para o fundo da página de autopeças */
    html, body {
        height: 100%; /* Garante que o html e body ocupem 100% da altura da viewport */
        margin: 0;
        padding: 0;
    }

    body {
        background-image: url('../img/autopecas_fundo.jpg'); /* Caminho para a imagem de autopeças */
        background-repeat: no-repeat;
        background-size: cover; /* Cobre todo o fundo, redimensionando a imagem */
        background-position: center center; /* Centraliza a imagem */
        background-attachment: fixed; /* Mantém a imagem fixa durante a rolagem */
        /* display: flex; /* Se o conteúdo estiver flutuando, descomente */
        /* flex-direction: column; */
    }

    /* Como você já tem um .container no style.css, ele deve ter um fundo.
       Apenas para garantir contraste, os cards já terão um fundo mais escuro
       pelo CSS global que já aplicamos.
    */
</style>
</head>
<body>
    <div class="container">
        <div class="dashboard-header">
            <h2>Dashboard da Autopeças</h2>
            <p>Bem-vindo, **<?php echo htmlspecialchars($_SESSION['username']); ?>**! Gerencie o inventário e as vendas.</p>
        </div>

        <ul class="module-links">
            <li>
                <h3>Gerenciar Clientes</h3>
                <p>Cadastre e visualize clientes da borracharia.</p>
                <a href="../clientes/listar_clientes.php" class="btn-access">Acessar Clientes</a>
            </li>        
            <li>
                <h3>Inventário de Peças</h3>
                <p>Controle completo de todas as peças em estoque.</p>
                <a href="inventario.php" class="btn-access">Acessar Inventário</a>
            </li>
            <li>
                <h3>Gerenciar Vendas</h3>
                <p>Registre e acompanhe todas as transações de venda de peças.</p>
                <a href="#" class="btn-access">Acessar Vendas</a> 
            </li>
            <li>
                <h3>Inventário de Peças</h3>
                <p>Controle completo de todas as peças em estoque.</p>
                <a href="../pecas/listar_pecas.php" class="btn-access">Acessar Inventário</a>
            </li>
            <li>
                <h3>Relatórios de Estoque</h3>
                <p>Visualize relatórios e análises sobre o desempenho do inventário.</p>
                <a href="relatorio_estoque.php" class="btn-access">Acessar Relatórios</a> 
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