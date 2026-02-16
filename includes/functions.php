<?php

// Correção robusta da função no functions.php
function parseBrazilianCurrency($value) {
    if (is_string($value)) {
        // Remove todos os caracteres que não sejam dígitos ou vírgula
        $value = preg_replace('/[^0-9,]/', '', $value);
        // Substitui a vírgula pelo ponto decimal
        $value = str_replace(',', '.', $value);
    }
    return (float)$value;
}
/**
 * Recalcula e atualiza os totais de uma Ordem de Serviço com base em seus itens.
 * @param int $os_id O ID da Ordem de Serviço.
 * @param mysqli $conn A conexão ativa com o banco de dados.
 * @return void
 */
if (!function_exists('calculateOSSum')) {
    function calculateOSSum($os_id, $conn) {
        // SQL para somar os totais de todas as peças e serviços de uma OS
        $sql = "SELECT 
                    SUM(CASE WHEN peca_id IS NOT NULL THEN quantidade * preco_unitario ELSE 0 END) AS total_pecas,
                    SUM(CASE WHEN servico_id IS NOT NULL THEN (quantidade * preco_unitario) + mao_de_obra ELSE 0 END) AS total_mao_obra
                FROM itens_os
                WHERE os_id = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $os_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $totals = $result->fetch_assoc();
            $stmt->close();

            $total_pecas = $totals['total_pecas'] ?? 0;
            $total_mao_obra = $totals['total_mao_obra'] ?? 0;
            $total_geral = $total_pecas + $total_mao_obra;
            
            // ---- INÍCIO DO CÓDIGO DE DEBUG ----
            error_log("DEBUG: Recalculando totais para OS #{$os_id}");
            error_log("DEBUG: Total de Peças calculado: {$total_pecas}");
            error_log("DEBUG: Total de Mão de Obra calculado: {$total_mao_obra}");
            error_log("DEBUG: Total Geral calculado: {$total_geral}");
            // ---- FIM DO CÓDIGO DE DEBUG ----
            
            // SQL para atualizar os totais na tabela ordens_servico
            $sql_update_os = "UPDATE ordens_servico SET total_pecas = ?, total_mao_obra = ?, total_geral = ? WHERE id = ?";
            if ($stmt_update = $conn->prepare($sql_update_os)) {
                $stmt_update->bind_param("dddi", $total_pecas, $total_mao_obra, $total_geral, $os_id);
                $stmt_update->execute();
                $stmt_update->close();
            }
        }
    }
}
?>