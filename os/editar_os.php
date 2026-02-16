<?php
session_start();

include_once '../includes/db_config.php';
include_once '../includes/functions.php';

$allowed_roles = ['admin', 'mecanica'];
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['tipo_servico'], $allowed_roles)) {
    header("Location: ../login.php");
    exit();
}

$os_id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);
if ($os_id <= 0) {
    header("Location: listar_os.php?status=error&msg=" . urlencode("ID da Ordem de Serviço não especificado ou inválido."));
    exit();
}

$message_status = '';
$message_text = '';

if (isset($_GET['status']) && isset($_GET['msg'])) {
    $message_status = htmlspecialchars($_GET['status']);
    $message_text = htmlspecialchars(urldecode($_GET['msg']));
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) { die("Erro de conexão: " . $conn->connect_error); }

$conn->begin_transaction();

$os_details = null;
$sql_os = "SELECT
            os.id, os.data_abertura, os.data_fechamento, os.problema_relatado,
            os.diagnostico, os.servicos_executados, os.total_pecas, os.total_mao_obra,
            os.total_geral, os.status,
            c.id AS cliente_id, c.nome AS cliente_nome, c.telefone AS cliente_telefone,
            v.id AS veiculo_id, v.placa AS veiculo_placa, v.marca AS veiculo_marca, v.modelo AS veiculo_modelo
          FROM
            ordens_servico os
          JOIN clientes c ON os.cliente_id = c.id
          JOIN veiculos v ON os.veiculo_id = v.id
          WHERE os.id = ?";
$stmt_os = $conn->prepare($sql_os);
$stmt_os->bind_param("i", $os_id);
$stmt_os->execute();
$result_os = $stmt_os->get_result();
if ($result_os->num_rows > 0) {
    $os_details = $result_os->fetch_assoc();
}
$stmt_os->close();

if (!$os_details) {
    $conn->rollback();
    $conn->close();
    header("Location: listar_os.php?status=error&msg=" . urlencode("Ordem de Serviço não encontrada."));
    exit();
}

$itens_os = [];
$sql_itens = "SELECT
                io.id, io.quantidade, io.preco_unitario, io.mao_de_obra,
                p.nome_peca, p.codigo_peca, p.tipo_item,
                s.nome_servico
              FROM
                itens_os io
              LEFT JOIN pecas p ON io.peca_id = p.id
              LEFT JOIN servicos s ON io.servico_id = s.id
              WHERE io.os_id = ?";

if ($stmt_itens = $conn->prepare($sql_itens)) {
    $stmt_itens->bind_param("i", $os_id);
    $stmt_itens->execute();
    $result_itens = $stmt_itens->get_result();
    if ($result_itens->num_rows > 0) {
        while($row_item = $result_itens->fetch_assoc()) {
            $itens_os[] = $row_item;
        }
    }
    $stmt_itens->close();
}

$pecas_disponiveis = [];
$sql_pecas = "SELECT id, nome_peca, preco_venda, tipo_item FROM pecas WHERE tipo_item IN ('peca_automotiva', 'pneu', 'acessorio_borracharia') ORDER BY nome_peca ASC";
$result_pecas = $conn->query($sql_pecas);
if ($result_pecas->num_rows > 0) {
    while($row = $result_pecas->fetch_assoc()) {
        $pecas_disponiveis[] = $row;
    }
}

$servicos_disponiveis = [];
$sql_servicos = "SELECT id, nome_servico, preco, tipo_estabelecimento FROM servicos WHERE tipo_estabelecimento IN ('mecanica', 'borracharia') ORDER BY nome_servico ASC";
$result_servicos = $conn->query($sql_servicos);
if ($result_servicos->num_rows > 0) {
    while($row = $result_servicos->fetch_assoc()) {
        $servicos_disponiveis[] = $row;
    }
}

$conn->commit();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes da OS #<?php echo htmlspecialchars($os_details['id']); ?></title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #343a40 !important;
            color: #f8f9fa !important;
            line-height: 1.6;
        }
        .container {
            background-color: #495057 !important;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            max-width: 1400px;
            margin: 40px auto;
            border-top: 5px solid #007bff;
        }
        .os-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #5a6268;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .os-header h2 {
            margin: 0;
            color: #007bff;
            font-size: 2.5em;
        }
        .os-header .status {
            font-size: 1.5em;
            padding: 8px 15px;
            border-radius: 8px;
            text-transform: capitalize;
            font-weight: bold;
            color: white;
        }
        .os-details {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .os-details .detail-card {
            background-color: #6c757d;
            padding: 15px;
            border-radius: 8px;
            flex: 1 1 calc(50% - 20px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .os-details .detail-card h3 {
            margin: 0 0 10px 0;
            color: white;
            font-size: 1.2em;
            text-align: left;
            border-bottom: 1px solid #adb5bd;
            padding-bottom: 5px;
        }
        .os-details .detail-card p {
            margin: 0 0 5px 0;
            color: #f8f9fa;
        }
        .os-details .detail-card p strong {
            color: #ced4da;
        }
        .os-details .detail-card .problem-text,
        .os-details .detail-card .diagnostico-text,
        .os-details .detail-card .servicos-executados-text {
            color: #ced4da;
            white-space: pre-wrap;
        }
        .os-itens-section, .os-totais-section {
            margin-top: 40px;
        }
        .os-itens-section h3, .os-totais-section h3 {
            text-align: left;
            color: #007bff;
            font-size: 1.8em;
            border-bottom: 1px solid #007bff;
            padding-bottom: 5px;
            margin-bottom: 20px;
        }
        .itens-table {
            width: 100%;
            border-collapse: collapse;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
        }
        .itens-table th, .itens-table td {
            border: 1px solid #5a6268;
            padding: 12px;
            text-align: left;
            color: #e9ecef;
        }
        .itens-table th {
            background-color: #6c757d;
            color: white;
            text-align: center;
        }
        .itens-table tr:nth-child(even) { background-color: #5a6268; }
        .itens-table tr:hover { background-color: #6c757d; }
        .total-row { font-weight: bold; }
        .total-row td {
            text-align: right;
            background-color: #5a6268;
        }
        .total-row .label { text-align: left; }
        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }
        .form-group { margin-bottom: 20px; }
        .form-group {
                display: flex;
                flex-direction: column;
            }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #ced4da; font-size: 0.95em; }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group select {
            width: calc(100% - 24px); padding: 12px; border: 1px solid #5a6268; border-radius: 6px; font-size: 1em; box-sizing: border-box; transition: border-color 0.3s ease, box-shadow 0.3s ease;
            background-color: #6c757d; color: #f8f9fa;
        }
        .form-group input:focus, .form-group select:focus {
            border-color: #007bff; box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25); outline: none;
        }
        .btn {
            padding: 5px 10px; border-radius: 4px; font-size: 0.8em; font-weight: bold; cursor: pointer; border: none; color: white; transition: background-color 0.3s ease; margin: 2px;
        }
        .btn-add { background-color: #28a745; }
        .btn-add:hover { background-color: #218838; }
        .btn-back { background-color: #6c757d; color: white; padding: 12px 25px; border-radius: 6px; text-decoration: none; font-size: 1em; font-weight: 600; }
        .btn-back:hover { background-color: #5a6268; }
        .btn-primary { background-color: #007bff; color: white; padding: 12px 25px; border-radius: 6px; text-decoration: none; font-size: 1em; font-weight: 600; }
        .btn-primary:hover { background-color: #0056b3; }
        .btn-finish-service { background-color: #28a745; color: white; padding: 12px 25px; border-radius: 6px; text-decoration: none; font-size: 1em; font-weight: 600; }
        .btn-finish-service:hover { background-color: #218838; }

        /* Estilos para status */
        .status-aberta { background-color: #007bff; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
        .status-em_andamento { background-color: #6f42c1; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
        .status-aguardando_peca { background-color: #ffc107; color: #343a40; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
        .status-concluida { background-color: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
        .status-cancelada { background-color: #dc3545; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <?php
            if (!empty($message_status)) {
                $message_class = '';
                if ($message_status == 'success') { $message_class = 'message success'; }
                elseif ($message_status == 'error') { $message_class = 'message error'; }
                elseif ($message_status == 'info') { $message_class = 'message info'; }
                ?>
                <div class="<?php echo $message_class; ?>">
                    <?php echo $message_text; ?>
                </div>
        <?php } ?>
            
        <?php if ($os_details): ?>
            <div class="os-header">
                <h2>Ordem de Serviço #<?php echo htmlspecialchars($os_details['id']); ?></h2>
                <?php
                    $status_class = 'status-' . str_replace(' ', '_', $os_details['status']);
                    $status_text = htmlspecialchars(ucfirst(str_replace('_', ' ', $os_details['status'])));
                ?>
                <span class="status <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
            </div>

            <div class="os-details">
                <div class="detail-card">
                    <h3>Dados do Cliente e Veículo</h3>
                    <p><strong>Cliente:</strong> <?php echo htmlspecialchars($os_details['cliente_nome']); ?> (Tel: <?php echo htmlspecialchars($os_details['cliente_telefone']); ?>)</p>
                    <p><strong>Veículo:</strong> <?php echo htmlspecialchars($os_details['veiculo_marca'] . ' ' . $os_details['veiculo_modelo']); ?></p>
                    <p><strong>Placa:</strong> <?php echo htmlspecialchars($os_details['veiculo_placa']); ?></p>
                </div>
                <div class="detail-card">
                    <h3>Detalhes da OS</h3>
                    <p><strong>Abertura:</strong> <?php echo (new DateTime($os_details['data_abertura']))->format('d/m/Y H:i'); ?></p>
                    <p><strong>Fechamento:</strong> <?php echo !empty($os_details['data_fechamento']) ? (new DateTime($os_details['data_fechamento']))->format('d/m/Y H:i') : 'Pendente'; ?></p>
                    <p><strong>Problema Relatado:</strong></p>
                    <p class="problem-text"><?php echo htmlspecialchars($os_details['problema_relatado']); ?></p>
                </div>
            </div>

            <div class="os-itens-section">
                <h3>Itens da Ordem de Serviço</h3>
                <?php if (empty($itens_os)): ?>
                    <p class="no-records">Nenhum item adicionado a esta Ordem de Serviço ainda.</p>
                <?php else: ?>
                    <table class="itens-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Código</th>
                                <th>Quantidade</th>
                                <th>Preço Unitário</th>
                                <th>Subtotal</th>
                                <th>Tipo</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($itens_os as $item):
                                // O subtotal é o preço unitário * quantidade + mao de obra
                                $subtotal = ($item['quantidade'] * $item['preco_unitario']) + ($item['mao_de_obra'] ?? 0);
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['nome_peca'] ?? $item['nome_servico']); ?></td>
                                <td><?php echo htmlspecialchars($item['codigo_peca'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars(number_format($item['quantidade'], 0, ',', '.')); ?></td>
                                <td>R$ <?php echo htmlspecialchars(number_format($item['preco_unitario'], 2, ',', '.')); ?></td>
                                <td>R$ <?php echo htmlspecialchars(number_format($subtotal, 2, ',', '.')); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $item['tipo_item'] ?? 'Serviço'))); ?></td>
                                <td>
                                    <form action="remover_item_os.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($item['id']); ?>">
                                        <button type="submit" class="btn btn-delete" onclick="return confirm('Tem certeza que deseja remover este item?');">Remover</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="total-row">
                                <td colspan="4" class="label">Total de Itens:</td>
                                <td>R$ <?php echo htmlspecialchars(number_format($os_details['total_pecas'] + $os_details['total_mao_obra'], 2, ',', '.')); ?></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                <?php endif; ?>

                <div style="margin-top: 30px;">
                    <h4>Adicionar Item à OS</h4>
                    <form action="processar_item_os.php" method="POST">
                        <input type="hidden" name="os_id" value="<?php echo htmlspecialchars($os_details['id']); ?>">
                        <div style="display: flex; gap: 15px; align-items: flex-start; flex-wrap: wrap;">
                            <div class="form-group" style="flex: 2;">
                                <label for="tipo_item_add">Tipo de Item:</label>
                                <select id="tipo_item_add" name="tipo_item_add" required>
                                    <option value="">Selecione</option>
                                    <option value="peca">Peça Automotiva</option>
                                    <option value="servico">Serviço</option>
                                </select>
                            </div>
                            <div class="form-group" style="flex: 4;">
                                <label for="item_id">Item:</label>
                                <select id="item_id" name="item_id" required disabled>
                                    <option value="">Selecione um tipo de item primeiro</option>
                                </option>
                                </select>
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <label for="quantidade">Qtd:</label>
                                <input type="number" id="quantidade" name="quantidade" value="1" min="1" required>
                            </div>
                            <div class="form-group" id="preco-peca-group" style="flex: 1;">
                                <label for="preco_unitario">Preço:</label>
                                <input type="text" id="preco_unitario" name="preco_unitario" value="0,00" required>
                            </div>
                            <div class="form-group" id="mao-de-obra-group" style="flex: 1; display:none;">
                                <label for="valor_mao_obra">Mão de Obra:</label>
                                <input type="text" id="valor_mao_obra" name="valor_mao_obra" value="0,00">
                            </div>
                            <button type="submit" class="btn btn-add" style="flex: 1;">Adicionar Item</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="os-totais-section">
                <h3>Resumo de Custos</h3>
                <div class="detail-card">
                    <p><strong>Total de Peças:</strong> R$ <?php echo htmlspecialchars(number_format($os_details['total_pecas'], 2, ',', '.')); ?></p>
                    <p><strong>Total de Mão de Obra:</strong> R$ <?php echo htmlspecialchars(number_format($os_details['total_mao_obra'], 2, ',', '.')); ?></p>
                    <p><strong>Total Geral:</strong> R$ <?php echo htmlspecialchars(number_format($os_details['total_geral'], 2, ',', '.')); ?></p>
                </div>
            </div>

            <div class="action-buttons">
                <a href="listar_os.php" class="btn-back">Voltar para a Lista de OS</a>
                <a href="#" class="btn btn-primary">Atualizar Status</a> <a href="#" class="btn btn-finish-service">Finalizar Ordem de Serviço</a> </div>
        <?php else: ?>
            <p class="no-records">Ordem de Serviço não encontrada.</p>
        <?php endif; ?>
    </div>
    
    <script>
        const tipoItemSelect = document.getElementById('tipo_item_add');
        const itemSelect = document.getElementById('item_id');
        const precoPecaGroup = document.getElementById('preco-peca-group');
        const precoInput = document.getElementById('preco_unitario');
        const maoDeObraGroup = document.getElementById('mao-de-obra-group');
        const maoDeObraInput = document.getElementById('valor_mao_obra');
        const quantidadeInput = document.getElementById('quantidade');

        const pecasDisponiveis = <?php echo json_encode($pecas_disponiveis); ?>;
        const servicosDisponiveis = <?php echo json_encode($servicos_disponiveis); ?>;

        function loadItems() {
            const selectedType = tipoItemSelect.value;
            itemSelect.innerHTML = '<option value="">Selecione um item</option>';
            itemSelect.disabled = true;
            
            if (selectedType === 'peca') {
                precoPecaGroup.style.display = 'flex';
                maoDeObraGroup.style.display = 'none';
                quantidadeInput.value = 1;
            } else if (selectedType === 'servico') {
                precoPecaGroup.style.display = 'flex';
                maoDeObraGroup.style.display = 'flex';
                quantidadeInput.value = 1;
            } else {
                precoPecaGroup.style.display = 'flex';
                maoDeObraGroup.style.display = 'none';
            }

            let itemsToLoad = [];
            if (selectedType === 'peca') {
                itemsToLoad = pecasDisponiveis;
            } else if (selectedType === 'servico') {
                itemsToLoad = servicosDisponiveis;
            }

            if (itemsToLoad.length > 0) {
                itemSelect.disabled = false;
                itemsToLoad.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = `${item.nome_peca || item.nome_servico} - R$ ${(item.preco_venda || item.preco || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                    itemSelect.appendChild(option);
                });
            }
            precoInput.value = '0,00';
            maoDeObraInput.value = '0,00';
        }

        function updatePrice() {
            const selectedType = tipoItemSelect.value;
            const selectedItemId = itemSelect.value;
            let itemsToSearch = [];
            let price = '0,00';

            if (selectedType === 'peca') {
                itemsToSearch = pecasDisponiveis;
                const selectedItem = itemsToSearch.find(item => item.id == selectedItemId);
                if (selectedItem) {
                    price = (selectedItem.preco_venda || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
            } else if (selectedType === 'servico') {
                itemsToSearch = servicosDisponiveis;
                const selectedItem = itemsToSearch.find(item => item.id == selectedItemId);
                if (selectedItem) {
                    price = (selectedItem.preco || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
            }
            precoInput.value = price;
        }

        tipoItemSelect.addEventListener('change', loadItems);
        itemSelect.addEventListener('change', updatePrice);

        const form = document.querySelector('form[action="processar_item_os.php"]');
        form.addEventListener('submit', function(event) {
        const precoValue = precoInput.value;
        // Substitui o ponto por vírgula se houver ponto e não houver vírgula
        if (precoValue.includes('.') && !precoValue.includes(',')) {
            precoInput.value = precoValue.replace('.', ',');
        }
        });
    </script>
</body>
</html>