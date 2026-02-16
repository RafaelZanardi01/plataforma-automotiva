<?php
session_start();

// Inclui as configurações do banco de dados
include_once '../includes/db_config.php'; // Caminho relativo: sobe um nível para 'plataforma-automotiva' e entra em 'includes'

// Verifica se o usuário está logado e se tem permissão (autopecas ou admin)
if (!isset($_SESSION['user_id']) || ($_SESSION['tipo_servico'] != 'autopecas' && $_SESSION['tipo_servico'] != 'admin')) {
    header("Location: ../login.php"); // Redireciona para o login se não autorizado
    exit();
}

// Tenta conectar ao banco de dados
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verifica a conexão
if ($conn->connect_error) {
    die("Erro de conexão com o banco de dados: " . $conn->connect_error);
}

// --- LÓGICA DE PESQUISA ---
$search_term = '';
$sql = "SELECT id, nome_peca, descricao, fabricante, codigo_peca, quantidade, preco_custo, preco_venda, localizacao_estoque FROM pecas";
$params = [];
$types = '';

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_term = trim($_GET['search']);
    // Adiciona o termo de pesquisa à cláusula WHERE
    // Usamos LOWER() para tornar a pesquisa insensível a maiúsculas/minúsculas
    $sql .= " WHERE LOWER(nome_peca) LIKE ? OR LOWER(fabricante) LIKE ? OR LOWER(descricao) LIKE ?";
    // Adiciona os parâmetros para a prepared statement
    $params = ["%" . strtolower($search_term) . "%", "%" . strtolower($search_term) . "%", "%" . strtolower($search_term) . "%"];
    $types = 'sss'; // Três strings
}

$sql .= " ORDER BY nome_peca ASC";

// Prepara e executa a consulta
if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Erro na preparação da consulta: " . $conn->error);
    }
    $stmt->bind_param($types, ...$params); // O '...' (splat operator) expande o array $params
    $stmt->execute();
    $result = $stmt->get_result(); // Pega o resultado da prepared statement
} else {
    $result = $conn->query($sql); // Executa a consulta simples se não houver pesquisa
}
// --- FIM DA LÓGICA DE PESQUISA ---

// Variável para armazenar as peças
$pecas = [];
if ($result && $result->num_rows > 0) { // Verifica se $result não é nulo e tem linhas
    while($row = $result->fetch_assoc()) {
        $pecas[] = $row;
    }
}

// Fecha a conexão com o banco de dados
if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventário de Peças - Autopeças</title>
    <style>
        /* Estilos globais/base para a página */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #dbe2ef; /* Fundo cinza-azulado suave */
            margin: 0;
            padding: 0;
            line-height: 1.6;
            color: #333;
        }

        /* Container principal da tabela/conteúdo */
        .container {
            background-color: #ffffff;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            max-width: 1400px; /* Largura máxima maior para acomodar a tabela */
            margin: 40px auto;
            border-top: 5px solid #007bff; /* Detalhe azul no topo */
        }

        h2 {
            color: #007bff;
            margin-bottom: 25px;
            text-align: center;
            font-size: 2em;
            font-weight: 600;
        }

        /* Estilos para o campo de pesquisa e botões de ação */
        .top-section {
            display: flex;
            justify-content: space-between; /* Espaça os itens: pesquisa à esquerda, botões à direita */
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap; /* Permite que os itens quebrem linha em telas menores */
            gap: 15px; /* Espaço entre os elementos */
        }

        .search-box {
            display: flex;
            gap: 10px;
        }

        .search-box input[type="text"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 250px; /* Largura do campo de pesquisa */
            font-size: 1em;
        }

        .search-box button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s ease;
        }
        .search-box button:hover {
            background-color: #0056b3;
        }

        .action-buttons {
            display: flex; /* Para alinhar os dois botões internos */
            gap: 10px; /* Espaço entre "Voltar" e "Adicionar" */
        }

        .action-buttons a {
            background-color: #28a745; /* Cor verde para Adicionar e Voltar */
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s ease;
        }
        .action-buttons a:hover {
            background-color: #218838;
        }
        /* Ajuste para o botão voltar, se quiser uma cor diferente */
        .action-buttons a:first-child { /* Seleciona o primeiro link dentro de .action-buttons (o "Voltar") */
            background-color: #6c757d; /* Cinza para o botão Voltar */
        }
        .action-buttons a:first-child:hover {
            background-color: #5a6268;
        }


        /* Estilos da Tabela */
        table {
            width: 100%;
            border-collapse: collapse; /* Remove o espaçamento entre as células */
            margin-top: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            table-layout: fixed; /* Ajuda a controlar as larguras das colunas com mais precisão */
        }

        /* Bordas para cabeçalho e células */
        th, td {
            border: 1px solid #e0e0e0; /* Borda cinza clara */
            padding: 12px; /* Espaçamento interno */
            text-align: left; /* Padrão */
            vertical-align: middle; /* Alinha o conteúdo verticalmente ao meio */
            word-wrap: break-word; /* Permite que o texto longo quebre a linha dentro da célula */
        }

        /* Estilo do cabeçalho da tabela */
        th {
            background-color: #007bff; /* Azul vibrante */
            color: white;
            font-weight: 600;
            text-align: center; /* Centraliza o texto no cabeçalho */
        }

        /* Alterna a cor das linhas para melhor leitura */
        tr:nth-child(even) {
            background-color: #f8f8f8; /* Linhas pares mais claras */
        }
        tr:hover {
            background-color: #eef1f5; /* Efeito ao passar o mouse */
        }

        /* Larguras das Colunas - Ajuste esses valores percentuais conforme necessário */
        th:nth-child(1), td:nth-child(1) { width: 5%; text-align: center; } /* ID */
        th:nth-child(2), td:nth-child(2) { width: 18%; } /* Nome da Peça - Aumentado */
        th:nth-child(3), td:nth-child(3) { width: 20%; } /* Descrição - Mantido */
        th:nth-child(4), td:nth-child(4) { width: 10%; } /* Fabricante - Mantido */
        th:nth-child(5), td:nth-child(5) { width: 10%; text-align: center; } /* Código */
        th:nth-child(6), td:nth-child(6) { width: 7%; text-align: center; } /* Quantidade */
        th:nth-child(7), td:nth-child(7) { width: 8%; text-align: center; } /* Preço Custo */
        th:nth-child(8), td:nth-child(8) { width: 8%; text-align: center; } /* Preço Venda */
        th:nth-child(9), td:nth-child(9) { width: 7%; text-align: left; } /* Localização */
        th:nth-child(10), td:nth-child(10) { width: 7%; text-align: center; } /* Ações - Ligeiramente reduzido para caber os botões */

        /* Estilos para a mensagem de "Nenhum registro" */
        .no-records {
            text-align: center;
            color: #777;
            padding: 20px;
            background-color: #f0f0f0;
            border-radius: 8px;
            margin-top: 20px;
        }

        /* Estilos para os botões de ação: Editar e Excluir */
        .btn-edit, .btn-delete {
            width: 75px; /* Largura fixa para ambos os botões - Ligeiramente ajustado */
            padding: 6px 0;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            font-size: 0.85em; /* Ligeiramente menor para caber melhor */
            white-space: nowrap; /* Evita que o texto quebre */
            display: inline-block;
            text-align: center; /* Centraliza o texto dentro do botão */
            box-sizing: border-box;
            transition: background-color 0.3s ease, transform 0.2s ease;
            line-height: 1.5; /* Ajuda na centralização vertical do texto */
            vertical-align: middle; /* Essencial para alinhar os botões */
        }

        .btn-edit {
            background-color: #007bff;
            margin-right: 5px; /* Espaçamento ajustado */
        }
        .btn-edit:hover {
            background-color: #0056b3;
            transform: translateY(-1px);
        }

        .btn-delete {
            background-color: #dc3545;
        }
        .btn-delete:hover {
            background-color: #c82333;
            transform: translateY(-1px);
        }

        /* Responsividade básica para telas menores */
        @media (max-width: 768px) {
            .container {
                margin: 20px 10px;
                padding: 15px;
                overflow-x: auto; /* Habilita rolagem horizontal para a tabela */
            }
            .top-section {
                flex-direction: column; /* Empilha a pesquisa e os botões */
                align-items: stretch;
            }
            .search-box {
                flex-direction: column;
                width: 100%;
            }
            .search-box input[type="text"] {
                width: calc(100% - 22px);
            }
            .search-box button {
                width: 100%;
            }
            .action-buttons {
                flex-direction: column; /* Empilha os botões de ação */
                align-items: stretch;
            }
            .action-buttons a {
                display: block;
                margin: 5px 0;
                width: 100%;
            }
            table {
                min-width: 1000px; /* Garante uma largura mínima para a tabela em telas pequenas */
            }
            .btn-edit, .btn-delete {
                display: block;
                margin-right: 0;
                margin-bottom: 5px;
                width: 100%;
                box-sizing: border-box;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Inventário de Peças da Autopeças</h2>

        <div class="top-section">
            <div class="search-box">
                <form action="inventario.php" method="GET">
                    <input type="text" name="search" placeholder="Pesquisar peça, fabricante..." value="<?php echo htmlspecialchars($search_term); ?>">
                    <button type="submit">Pesquisar</button>
                </form>
            </div>
            <div class="action-buttons">
                <a href="index.php">Voltar para o Dashboard</a>
                <a href="adicionar_peca.php">Adicionar Nova Peça</a>
            </div>
        </div>
        <?php if (empty($pecas)): ?>
            <p class="no-records">Nenhuma peça encontrada no inventário. Comece adicionando novas peças<?php echo !empty($search_term) ? ' ou ajuste sua pesquisa por "' . htmlspecialchars($search_term) . '".' : '.'; ?></p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome da Peça</th>
                        <th>Descrição</th>
                        <th>Fabricante</th>
                        <th>Código</th>
                        <th>Quantidade</th>
                        <th>Preço Custo</th>
                        <th>Preço Venda</th>
                        <th>Localização</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pecas as $peca): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($peca['id']); ?></td>
                        <td><?php echo htmlspecialchars($peca['nome_peca']); ?></td>
                        <td><?php echo htmlspecialchars($peca['descricao']); ?></td>
                        <td><?php echo htmlspecialchars($peca['fabricante']); ?></td>
                        <td><?php echo htmlspecialchars($peca['codigo_peca']); ?></td>
                        <td><?php echo htmlspecialchars($peca['quantidade']); ?></td>
                        <td>R$ <?php echo htmlspecialchars(number_format($peca['preco_custo'], 2, ',', '.')); ?></td>
                        <td>R$ <?php echo htmlspecialchars(number_format($peca['preco_venda'], 2, ',', '.')); ?></td>
                        <td><?php echo htmlspecialchars($peca['localizacao_estoque']); ?></td>
                        <td>
                            <a href="editar_peca.php?id=<?php echo $peca['id']; ?>" class="btn-edit">Editar</a>
                            <a href="excluir_peca.php?id=<?php echo $peca['id']; ?>" class="btn-delete" onclick="return confirm('Tem certeza que deseja excluir esta peça?');">Excluir</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>