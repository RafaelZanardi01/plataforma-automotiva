<?php
session_start();

// Verifica se o usuário está logado e se é do tipo 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['tipo_servico'] != 'admin') {
    header("Location: ../login.php"); // Redireciona para o login se não autorizado
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrativo</title>
    <link rel="stylesheet" href="../css/style.css"> 
    <style>
    /* Estilos específicos para o fundo da página do administrador */
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
    }

    body {
        background-image: url('../img/admin_fundo.jpg');
        background-repeat: no-repeat;
        background-size: cover;
        background-position: center center;
        background-attachment: fixed;
        display: flex;
        /* flex-direction: column; */
    }
</style>
</head>
<body>
    <div class="container">
        <div class="dashboard-header">
            <h2>Painel Administrativo</h2>
            <p>Bem-vindo, **<?php echo htmlspecialchars($_SESSION['username']); ?>**! Acesso total à gestão da plataforma.</p>
        </div>

        <ul class="module-links">
                <li>
                    <h3>Acessar Borracharia</h3>
                    <p>Gerencie agendamentos e o estoque de pneus da borracharia.</p>
                    <a href="../borracharia/index.php" class="btn-access">Ir para Borracharia</a>
                </li>
                <li>
                    <h3>Acessar Autopeças</h3>
                    <p>Controle o inventário de peças e as vendas da autopeças.</p>
                    <a href="../autopecas/index.php" class="btn-access">Ir para Autopeças</a>
                </li>
                <li>
                    <h3>Acessar Mecânica</h3>
                    <p>Gerencie ordens de serviço e o histórico de veículos da mecânica.</p>
                    <a href="../mecanica/index.php" class="btn-access">Ir para Mecânica</a>
                </li>
                <li>
                    <h3>Gerenciar Clientes</h3>
                    <p>Cadastre, visualize, edite e exclua informações dos clientes.</p>
                    <a href="../clientes/listar_clientes.php" class="btn-access">Acessar Clientes</a>
                </li>
                <li>
                    <h3>Gerenciar Agendamentos (Geral)</h3>
                    <p>Visualize e gerencie todos os agendamentos da borracharia e mecânica.</p>
                    <a href="../agendamentos/listar_agendamentos.php" class="btn-access">Ver Agendamentos Gerais</a>
                </li>
                <li>
                    <h3>Gerenciar Usuários</h3>
                    <p>Crie, edite e remova usuários da plataforma e defina suas permissões.</p>
                    <a href="#" class="btn-access">Gerenciar Usuários</a> </li>
                <li>
                    <h3>Relatórios Consolidados</h3>
                    <p>Visualize dados e relatórios de todos os estabelecimentos integrados.</p>
                    <a href="#" class="btn-access">Ver Relatórios</a> </li>
                <li>
                    <h3>Configurações do Sistema</h3>
                    <p>Ajuste configurações gerais da plataforma.</p>
                    <a href="#" class="btn-access">Ajustar Configs</a> </li>
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