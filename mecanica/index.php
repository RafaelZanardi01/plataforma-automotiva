<?php
session_start();

// Verifica se o usuário está logado e se é do tipo correto (mecanica ou admin)
if (!isset($_SESSION['user_id']) || ($_SESSION['tipo_servico'] != 'mecanica' && $_SESSION['tipo_servico'] != 'admin')) {
    header("Location: ../login.php"); // Redireciona para o login se não autorizado
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mecânica</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* Estilos específicos para o fundo da página da mecânica */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            background-image: url('../img/mecanica_fundo.jpg'); 
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center center;
            background-attachment: fixed;
            /* display: flex; */
            /* flex-direction: column; */
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-header">
            <h2>Dashboard da Mecânica</h2>
            <p>Bem-vindo, **<?php echo htmlspecialchars($_SESSION['username']); ?>**! Otimize ordens de serviço e histórico de veículos.</p>
        </div>

        <ul class="module-links">
            <li>
                <h3>Gerenciar Ordens de Serviço</h3>
                <p>Abra, visualize e gerencie o andamento das ordens de serviço da oficina.</p>
                <a href="../os/listar_os.php" class="btn-access">Acessar Ordens de Serviço</a>
            </li>
            
            <li>
                <h3>Gerenciar Clientes</h3>
                <p>Cadastre e visualize clientes da mecânica.</p>
                <a href="../clientes/listar_clientes.php" class="btn-access">Acessar Clientes</a>
            </li>

            <li>
                <h3>Agendamentos de Serviços</h3>
                <p>Agende serviços e gerencie o fluxo de trabalho da sua oficina.</p>
                <a href="../agendamentos/listar_agendamentos.php" class="btn-access">Gerenciar Agendamentos</a>
            </li>
            <li>
                <h3>Cadastrar Novo Veículo</h3>
                <p>Adicione veículos à base de dados e associe-os aos clientes.</p>
                <a href="cadastrar_veiculo.php" class="btn-access">Cadastrar Veículo</a>
            </li>
            <li>
                <h3>Listar e Consultar Veículos</h3>
                <p>Visualize todos os veículos cadastrados e suas informações detalhadas.</p>
                <a href="listar_veiculos.php" class="btn-access">Ver Veículos</a>
            </li>
            
            <li>
                <h3>Histórico de Veículos e Clientes</h3>
                <p>Consulte o histórico completo de manutenções por veículo ou cliente.</p>
                <a href="#" class="btn-access">Acessar Histórico</a> 
            </li>
            <li>
                <h3>Base de Conhecimento</h3>
                <p>Acesse diagramas, manuais e informações técnicas para auxiliar nos reparos.</p>
                <a href="#" class="btn-access">Acessar Base</a> 
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