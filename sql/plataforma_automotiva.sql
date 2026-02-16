-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 16/02/2026 às 20:35
-- Versão do servidor: 8.0.37
-- Versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `plataforma_automotiva`
--
CREATE DATABASE IF NOT EXISTS `plataforma_automotiva` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `plataforma_automotiva`;

-- --------------------------------------------------------

--
-- Estrutura para tabela `agendamentos`
--

DROP TABLE IF EXISTS `agendamentos`;
CREATE TABLE `agendamentos` (
  `id` int NOT NULL,
  `cliente_id` int NOT NULL,
  `veiculo_id` int NOT NULL,
  `servico_id` int NOT NULL,
  `data_hora` datetime NOT NULL,
  `observacoes` text,
  `status` enum('pendente','confirmado','em_andamento','concluido','cancelado') NOT NULL DEFAULT 'pendente',
  `tipo_estabelecimento` enum('borracharia','mecanica') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `agendamentos`
--

INSERT INTO `agendamentos` (`id`, `cliente_id`, `veiculo_id`, `servico_id`, `data_hora`, `observacoes`, `status`, `tipo_estabelecimento`) VALUES
(1, 17, 108, 9, '2025-07-29 13:45:00', 'REalizar o serviço de troca de pneus.', 'concluido', 'borracharia');

-- --------------------------------------------------------

--
-- Estrutura para tabela `clientes`
--

DROP TABLE IF EXISTS `clientes`;
CREATE TABLE `clientes` (
  `id` int NOT NULL,
  `nome` varchar(100) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `cpf_cnpj` varchar(20) DEFAULT NULL,
  `tipo_cliente` enum('PF','PJ') DEFAULT NULL,
  `endereco` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `clientes`
--

INSERT INTO `clientes` (`id`, `nome`, `telefone`, `email`, `cpf_cnpj`, `tipo_cliente`, `endereco`) VALUES
(1, 'Pedro Mendes', '(41) 99123-4567', 'pedro.mendes@email.com', '123.456.789-01', 'PF', 'Rua X, 100 - Pinheirinho, Curitiba - PR'),
(2, 'Oficina Solução Ltda.', '(41) 3344-5566', 'contato@oficinasolucao.com', '01.234.567/0001-89', 'PJ', 'Av. Winston Churchill, 500 - Pinheirinho, Curitiba - PR'),
(3, 'Mariana Ferreira', '(41) 98876-5432', 'mariana.f@email.com', '234.567.890-12', 'PF', 'Rua Izaac Ferreira da Cruz, 250 - Pinheirinho, Curitiba - PR'),
(4, 'Auto Peças Rápida', '(41) 3210-9876', 'vendas@autopecasrapida.com', '12.345.678/0001-90', 'PJ', 'Rua da Cidadania, 123 - Pinheirinho, Curitiba - PR'),
(5, 'Carlos Alberto', '(41) 99987-6543', 'carlos.a@email.com', '345.678.901-23', 'PF', 'Rua André Ferreira, 30 - Pinheirinho, Curitiba - PR'),
(6, 'Transportadora Veloz S.A.', '(41) 3030-1234', 'logistica@transveloz.com.br', '23.456.789/0001-01', 'PJ', 'Rua B, 789 - Pinheirinho, Curitiba - PR'),
(7, 'Juliana Lima', '(41) 99234-5678', 'juliana.l@email.com', '456.789.012-34', 'PF', 'Rua Ciclano de Tal, 150 - Pinheirinho, Curitiba - PR'),
(8, 'Mecânica & Cia.', '(41) 3555-4321', 'mecanicaeciacwb@email.com', '34.567.890/0001-12', 'PJ', 'Rua Doutor Lauro Gentil Portugal, 400 - Pinheirinho, Curitiba - PR'),
(9, 'Rafaela Gomes', '(41) 98123-4567', 'rafaela.g@email.com', '567.890.123-45', 'PF', 'Rua E, 50 - Pinheirinho, Curitiba - PR'),
(10, 'Borrracharia Ponto Certo', '(41) 3000-1111', 'borracharia@pontocerto.com', '45.678.901/0001-23', 'PJ', 'Rua Nicola Pellanda, 600 - Pinheirinho, Curitiba - PR'),
(11, 'Thiago Costa', '(41) 99345-6789', 'thiago.c@email.com', '678.901.234-56', 'PF', 'Rua F, 200 - Pinheirinho, Curitiba - PR'),
(12, 'Distr. Autopeças Paraná', '(41) 3999-8888', 'distribuidora@pr.com.br', '56.789.012/0001-34', 'PJ', 'Rodovia Régis Bittencourt, 1000 - Pinheirinho, Curitiba - PR'),
(13, 'Aline Souza', '(41) 98234-5678', 'aline.s@email.com', '789.012.345-67', 'PF', 'Rua G, 350 - Pinheirinho, Curitiba - PR'),
(14, 'Frotas Rápidas Ltda.', '(41) 3111-2222', 'frota.rapida@email.com', '67.890.123/0001-45', 'PJ', 'Rua H, 45 - Pinheirinho, Curitiba - PR'),
(15, 'Gustavo Oliveira', '(41) 99456-7890', 'gustavo.o@email.com', '890.123.456-78', 'PF', 'Rua K, 120 - Pinheirinho, Curitiba - PR'),
(16, 'Serviços de Guincho CWB', '(41) 3222-3333', 'guincho@curitiba.com', '78.901.234/0001-56', 'PJ', 'Rua Marechal Rondon, 70 - Pinheirinho, Curitiba - PR'),
(17, 'Lívia Pereira', '(41) 98345-6789', 'livia.p@email.com', '901.234.567-89', 'PF', 'Rua L, 80 - Pinheirinho, Curitiba - PR'),
(18, 'Auto Elétrica Forte', '(41) 3444-5555', 'eletrica@forte.com', '89.012.345/0001-67', 'PJ', 'Rua M, 190 - Pinheirinho, Curitiba - PR'),
(19, 'Lucas Fernandes', '(41) 99567-8901', 'lucas.f@email.com', '012.345.678-90', 'PF', 'Rua N, 210 - Pinheirinho, Curitiba - PR'),
(20, 'Distribuidora de Pneus ABC', '(41) 3666-7777', 'vendas@pneusabc.com', '90.123.456/0001-78', 'PJ', 'Rua P, 300 - Pinheirinho, Curitiba - PR'),
(21, 'Marta Rodrigues', '(41) 98456-7890', 'marta.r@email.com', '101.112.131-41', 'PF', 'Rua Q, 450 - Pinheirinho, Curitiba - PR'),
(22, 'Lataria e Pintura Top', '(41) 3777-8888', 'top@latariapintura.com', '01.011.012/0001-89', 'PJ', 'Rua R, 15 - Pinheirinho, Curitiba - PR'),
(23, 'Natália Santos', '(41) 99678-9012', 'natalia.s@email.com', '112.131.415-52', 'PF', 'Rua S, 88 - Pinheirinho, Curitiba - PR'),
(24, 'Despachante Legal', '(41) 3888-9999', 'contato@despachante.com', '02.023.024/0001-90', 'PJ', 'Rua T, 220 - Pinheirinho, Curitiba - PR'),
(25, 'Otávio Pires', '(41) 98567-8901', 'otavio.p@email.com', '123.141.516-63', 'PF', 'Rua U, 330 - Pinheirinho, Curitiba - PR');

-- --------------------------------------------------------

--
-- Estrutura para tabela `itens_os`
--

DROP TABLE IF EXISTS `itens_os`;
CREATE TABLE `itens_os` (
  `id` int NOT NULL,
  `os_id` int NOT NULL,
  `quantidade` int DEFAULT NULL,
  `preco_unitario` decimal(10,2) DEFAULT NULL,
  `mao_de_obra` decimal(10,2) DEFAULT NULL,
  `peca_id` int DEFAULT NULL,
  `servico_id` int DEFAULT NULL,
  `itens_oscol` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `itens_os`
--

INSERT INTO `itens_os` (`id`, `os_id`, `quantidade`, `preco_unitario`, `mao_de_obra`, `peca_id`, `servico_id`, `itens_oscol`) VALUES
(52, 2, 1, 48.00, NULL, 12, NULL, NULL),
(53, 2, 1, 900.00, NULL, 27, NULL, NULL),
(57, 2, 1, 40.00, 50.00, NULL, 9, NULL),
(58, 2, 1, 250.00, 150.00, NULL, 2, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `ordens_servico`
--

DROP TABLE IF EXISTS `ordens_servico`;
CREATE TABLE `ordens_servico` (
  `id` int NOT NULL,
  `cliente_id` int NOT NULL,
  `data_abertura` datetime DEFAULT CURRENT_TIMESTAMP,
  `data_fechamento` datetime DEFAULT NULL,
  `problema_relatado` text,
  `diagnostico` text,
  `servicos_executados` text,
  `total_pecas` decimal(10,2) DEFAULT NULL,
  `total_mao_obra` decimal(10,2) DEFAULT NULL,
  `total_geral` decimal(10,2) DEFAULT NULL,
  `status` enum('aberta','em_andamento','aguardando_peca','concluida','cancelada') DEFAULT 'aberta',
  `veiculo_id` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `ordens_servico`
--

INSERT INTO `ordens_servico` (`id`, `cliente_id`, `data_abertura`, `data_fechamento`, `problema_relatado`, `diagnostico`, `servicos_executados`, `total_pecas`, `total_mao_obra`, `total_geral`, `status`, `veiculo_id`) VALUES
(1, 13, '2025-07-29 20:32:00', NULL, 'Cliente relata bastante ruído incomum proveniente do motor', NULL, NULL, 0.00, 0.00, 0.00, 'aberta', '1'),
(2, 15, '2025-08-05 20:52:15', NULL, 'Carro está apresentando muitos ruidos durante a utilização.', NULL, NULL, 948.00, 490.00, 1438.00, 'aberta', '106');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pecas`
--

DROP TABLE IF EXISTS `pecas`;
CREATE TABLE `pecas` (
  `id` int NOT NULL,
  `nome_peca` varchar(100) NOT NULL,
  `descricao` text,
  `fabricante` varchar(50) DEFAULT NULL,
  `codigo_peca` varchar(50) DEFAULT NULL,
  `quantidade` int NOT NULL,
  `preco_custo` decimal(10,2) DEFAULT NULL,
  `preco_venda` decimal(10,2) DEFAULT NULL,
  `localizacao_estoque` varchar(50) DEFAULT NULL,
  `tipo_item` enum('peca_automotiva','pneu','acessorio_borracharia','servico_borracharia') DEFAULT 'peca_automotiva'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `pecas`
--

INSERT INTO `pecas` (`id`, `nome_peca`, `descricao`, `fabricante`, `codigo_peca`, `quantidade`, `preco_custo`, `preco_venda`, `localizacao_estoque`, `tipo_item`) VALUES
(1, 'Filtro de Óleo', 'Filtro de óleo para motores a gasolina', 'Fram', 'FRM-PH5800', 50, 15.50, 32.90, 'A1-01', 'peca_automotiva'),
(2, 'Vela de Ignição', 'Vela de ignição padrão para diversos modelos', 'NGK', 'NGK-BKR6E', 100, 8.20, 19.90, 'A1-02', 'peca_automotiva'),
(3, 'Pastilha de Freio Dianteira', 'Pastilha de freio dianteira, cerâmica', 'Cobreq', 'COB-N1234', 30, 85.00, 179.90, 'B2-03', 'peca_automotiva'),
(4, 'Disco de Freio Dianteiro', 'Disco de freio ventilado', 'Fremax', 'FRX-BD2345', 20, 120.00, 250.00, 'B2-04', 'peca_automotiva'),
(5, 'Amortecedor Dianteiro', 'Amortecedor dianteiro a gás', 'Monroe', 'MON-SP3456', 15, 210.00, 420.00, 'C3-05', 'peca_automotiva'),
(6, 'Correia Dentada', 'Correia dentada para motor 1.0 8v', 'Gates', 'GTS-T123', 40, 55.00, 110.00, 'A1-06', 'peca_automotiva'),
(7, 'Bateria 60Ah', 'Bateria automotiva 60 Amperes', 'Moura', 'MOU-M60AD', 10, 350.00, 699.00, 'D4-07', 'peca_automotiva'),
(8, 'Pneu Aro 15 185/65 R15', 'Pneu para carros de passeio', 'Pirelli', 'PIR-CINTURATO', 25, 280.00, 550.00, 'P1-01', 'peca_automotiva'),
(9, 'Palheta Limpador Para-brisa', 'Palheta Aerofit 22 polegadas', 'Bosch', 'BOS-A001S', 60, 25.00, 59.90, 'A1-08', 'peca_automotiva'),
(10, 'Lâmpada Farol H7', 'Lâmpada halógena para farol baixo', 'Osram', 'OSR-H7NBP', 80, 18.00, 45.00, 'A1-09', 'peca_automotiva'),
(11, 'Óleo de Motor 5W30 Sintético', 'Óleo lubrificante para motores modernos', 'Castrol', 'CAS-EDGE5W30', 70, 45.00, 89.90, 'L5-10', 'peca_automotiva'),
(12, 'Aditivo Radiador Concentrado', 'Aditivo para sistema de arrefecimento', 'Bardahl', 'BAR-RADPLUS', 35, 22.00, 48.00, 'A1-11', 'peca_automotiva'),
(13, 'Cabo de Vela', 'Jogo de cabos de vela 4 cilindros', 'NGK', 'NGK-SC-C37', 20, 65.00, 130.00, 'A1-12', 'peca_automotiva'),
(14, 'Bomba de Água', 'Bomba de água para motor 1.6', 'Urba', 'URB-BA123', 12, 150.00, 300.00, 'B2-13', 'peca_automotiva'),
(15, 'Kit Embreagem', 'Kit completo de embreagem', 'Luk', 'LUK-620308200', 8, 480.00, 950.00, 'C3-14', 'peca_automotiva'),
(16, 'Rolamento de Roda', 'Rolamento de roda dianteira', 'SKF', 'SKF-VKBA3585', 25, 90.00, 185.00, 'B2-15', 'peca_automotiva'),
(17, 'Junta do Cabeçote', 'Junta do cabeçote de motor', 'Sabó', 'SAB-65432', 18, 70.00, 140.00, 'A1-16', 'peca_automotiva'),
(18, 'Filtro de Ar Condicionado', 'Filtro de cabine anti-pólen', 'Mann-Filter', 'MAN-CU2500', 45, 30.00, 65.00, 'A1-17', 'peca_automotiva'),
(19, 'Sensor de Rotação', 'Sensor de rotação do motor', 'MTE-Thomson', 'MTE-7023', 15, 75.00, 150.00, 'A1-18', 'peca_automotiva'),
(20, 'Termostato', 'Válvula termostática', 'Wahler', 'WAH-410078D', 20, 40.00, 80.00, 'A1-19', 'peca_automotiva'),
(21, 'Pneu Aro 15 185/65 R15', 'Pneu para carros de passeio, linha standard', 'Goodyear', 'GY-1856515', 30, 250.00, 499.00, 'PISO-A1', 'pneu'),
(22, 'Pneu Aro 16 205/55 R16', 'Pneu de alta performance para veículos médios', 'Michelin', 'MICH-2055516', 20, 380.00, 750.00, 'PISO-A2', 'pneu'),
(23, 'Câmara de Ar Aro 14', 'Câmara de ar para pneus aro 14', 'Rinaldi', 'RIN-CAM14', 50, 15.00, 35.00, 'BORR-B1', 'acessorio_borracharia'),
(24, 'Válvula de Pneu TR414', 'Válvula de borracha para pneus sem câmara', 'Schrader', 'SCHR-TR414', 100, 2.50, 8.00, 'BORR-B2', 'acessorio_borracharia'),
(25, 'Serviço de Alinhamento e Balanceamento', 'Serviço completo de alinhamento e balanceamento de rodas', 'Serviço Interno', 'SERV-ALBAL', 9999, 50.00, 120.00, 'BORR-SERV', 'servico_borracharia'),
(26, 'Filtro de Ar', NULL, 'Fram', 'FRM-FILT-AR', 30, 25.00, 50.00, NULL, 'peca_automotiva'),
(27, 'Pneu Michelin Aro 18', NULL, 'Michelin', 'MICHELIN-18', 15, 450.00, 900.00, NULL, 'pneu');

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `pecas_formatadas`
-- (Veja abaixo para a visão atual)
--
DROP VIEW IF EXISTS `pecas_formatadas`;
CREATE TABLE `pecas_formatadas` (
`id` int
,`nome_peca` varchar(100)
,`descricao` text
,`fabricante` varchar(50)
,`codigo_peca` varchar(50)
,`quantidade` int
,`preco_custo_formatado` varchar(48)
,`preco_venda_formatado` varchar(48)
,`localizacao_estoque` varchar(50)
,`tipo_item` enum('peca_automotiva','pneu','acessorio_borracharia','servico_borracharia')
);

-- --------------------------------------------------------

--
-- Estrutura para tabela `servicos`
--

DROP TABLE IF EXISTS `servicos`;
CREATE TABLE `servicos` (
  `id` int NOT NULL,
  `nome_servico` varchar(100) NOT NULL,
  `descricao` text,
  `preco` decimal(10,2) DEFAULT NULL,
  `tipo_estabelecimento` enum('borracharia','mecanica') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `servicos`
--

INSERT INTO `servicos` (`id`, `nome_servico`, `descricao`, `preco`, `tipo_estabelecimento`) VALUES
(1, 'Troca de Óleo e Filtro', 'Substituição do óleo do motor e filtro de óleo.', 150.00, 'mecanica'),
(2, 'Revisão Básica', 'Verificação de 30 itens, fluídos, correias e suspensão.', 250.00, 'mecanica'),
(3, 'Troca de Pastilhas de Freio', 'Substituição das pastilhas de freio dianteiras/traseiras.', 120.00, 'mecanica'),
(4, 'Alinhamento e Balanceamento', 'Ajuste da geometria da suspensão e balanceamento das rodas.', 120.00, 'mecanica'),
(5, 'Diagnóstico Eletrônico', 'Leitura de códigos de falha e diagnóstico de sistemas eletrônicos.', 180.00, 'mecanica'),
(6, 'Manutenção do Ar Condicionado', 'Limpeza do sistema, carga de gás e verificação de vazamentos.', 280.00, 'mecanica'),
(7, 'Troca de Correia Dentada', 'Substituição da correia dentada e tensor.', 350.00, 'mecanica'),
(8, 'Revisão Geral', 'Revisão completa do veículo, incluindo motor, freios, suspensão e elétrica.', 600.00, 'mecanica'),
(9, 'Troca de Pneus (serviço)', 'Serviço de montagem e desmontagem de pneus.', 40.00, 'borracharia');

-- --------------------------------------------------------

--
-- Estrutura para tabela `solicitacoes_exclusao_clientes`
--

DROP TABLE IF EXISTS `solicitacoes_exclusao_clientes`;
CREATE TABLE `solicitacoes_exclusao_clientes` (
  `id` int NOT NULL,
  `cliente_id` int NOT NULL,
  `solicitante_tipo_servico` varchar(50) NOT NULL,
  `data_solicitacao` datetime DEFAULT CURRENT_TIMESTAMP,
  `status_admin` enum('pendente','aprovado','rejeitado') DEFAULT 'pendente',
  `status_borracharia` enum('pendente','aprovado','rejeitado') DEFAULT 'pendente',
  `status_autopecas` enum('pendente','aprovado','rejeitado') DEFAULT 'pendente',
  `status_mecanica` enum('pendente','aprovado','rejeitado') DEFAULT 'pendente',
  `data_conclusao` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `solicitacoes_exclusao_clientes`
--

INSERT INTO `solicitacoes_exclusao_clientes` (`id`, `cliente_id`, `solicitante_tipo_servico`, `data_solicitacao`, `status_admin`, `status_borracharia`, `status_autopecas`, `status_mecanica`, `data_conclusao`) VALUES
(2, 4, 'autopecas', '2025-08-06 21:09:32', 'pendente', 'pendente', 'aprovado', 'pendente', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `tipo_servico` enum('borracharia','autopecas','mecanica','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nome_estabelecimento` varchar(100) DEFAULT NULL,
  `data_cadastro` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `username`, `password`, `email`, `tipo_servico`, `nome_estabelecimento`, `data_cadastro`) VALUES
(1, 'user_borracharia', '$2y$10$clkPrtvB6RtVOxg3XRJHWeeKqdGcAjXNDrbgamQSo/6SJ7vcPh/Ya', 'borracharia@seusite.com', 'borracharia', 'Borracharia do Bahiano', '2025-06-17 01:20:54'),
(2, 'user_autopecas', '$2y$10$an.0SkyapNDrM11ApLCqNeWK9dbAJqEi5GZK1FuQx8kwSVIXhRwz.', 'autopecas@autopecas.com', 'autopecas', 'Pinheiro Auto Peças', '2025-06-17 01:34:34'),
(3, 'user_mecanica', '$2y$10$ht0NVMTH46p0j5Q1Eera7umzI9ElGet15HupgFGaggMwpUVabFtV2', 'jetha@oficina.com.br', 'mecanica', 'Jetha Oficina Mecânica', '2025-06-17 01:40:36'),
(4, 'admin', '$2y$10$lakczAvh/y2lJ1A4jxhZeuTItBU4tJ7Yl.9dUsTiWu4leVknWK63K', 'zanardi_rafael@hotmail.com', 'admin', 'HelloInfo Informática', '2025-06-17 01:41:47');

-- --------------------------------------------------------

--
-- Estrutura para tabela `veiculos`
--

DROP TABLE IF EXISTS `veiculos`;
CREATE TABLE `veiculos` (
  `id` int NOT NULL,
  `cliente_id` int NOT NULL,
  `marca` varchar(50) DEFAULT NULL,
  `modelo` varchar(50) DEFAULT NULL,
  `ano` int DEFAULT NULL,
  `placa` varchar(10) DEFAULT NULL,
  `chassi` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `veiculos`
--

INSERT INTO `veiculos` (`id`, `cliente_id`, `marca`, `modelo`, `ano`, `placa`, `chassi`) VALUES
(1, 13, 'Volkswagem', 'Fox', 2009, 'ABC3X14', '93HJK5A1C50076543'),
(72, 1, 'Volkswagen', 'Gol', 2015, 'ABC1B23', '9BWZZZ5X0FP012345'),
(73, 2, 'Fiat', 'Palio', 2013, 'DEF4C56', '9BD12345678901234'),
(74, 3, 'Chevrolet', 'Onix', 2018, 'GHI7D89', '9BG01234567890123'),
(75, 4, 'Ford', 'Ka', 2019, 'JKL0E12', '9BFEDCBA987654321'),
(76, 5, 'Hyundai', 'HB20', 2017, 'MNO3F45', '9BD09876543210987'),
(77, 6, 'Toyota', 'Corolla', 2020, 'PQR6G78', '9BRFE5E5L67890123'),
(78, 7, 'Honda', 'Civic', 2016, 'STU9H01', '9T123456789012345'),
(79, 8, 'Renault', 'Kwid', 2021, 'VWX2I34', '93Y78901234567890'),
(80, 9, 'Jeep', 'Renegade', 2022, 'YZA5J67', '9C421098765432109'),
(81, 10, 'Nissan', 'Kicks', 2019, 'BCD8K90', '9N543210987654321'),
(82, 11, 'Volkswagen', 'Fox', 2014, 'EFG1L23', '9BWZZZ5X0FP098765'),
(83, 12, 'Fiat', 'Argo', 2020, 'HIJ4M56', '9BD67890123456789'),
(84, 13, 'Chevrolet', 'Tracker', 2021, 'KLM7N89', '9BG98765432109876'),
(85, 14, 'Ford', 'EcoSport', 2017, 'NOP0P12', '9BFABCDEF01234567'),
(86, 15, 'Hyundai', 'Creta', 2018, 'QRS3Q45', '9BD54321098765432'),
(87, 16, 'Toyota', 'Hilux', 2022, 'TUV6R78', '9BRAB1B1L12345678'),
(88, 17, 'Honda', 'HR-V', 2019, 'WXY9S01', '9T109876543210987'),
(89, 18, 'Renault', 'Captur', 2020, 'ZAB2T34', '93Y90123456789012'),
(90, 19, 'Jeep', 'Compass', 2023, 'CDE5U67', '9C409876543210987'),
(91, 20, 'Nissan', 'Frontier', 2021, 'FGH8V90', '9N876543210987654'),
(92, 1, 'Volkswagen', 'Polo', 2019, 'IJK1W23', '9BWZZZ5X0FP000111'),
(93, 2, 'Fiat', 'Cronos', 2021, 'LMN4X56', '9BD00011122233344'),
(94, 3, 'Chevrolet', 'S10', 2020, 'OPQ7Y89', '9BG00000011122233'),
(95, 4, 'Ford', 'Ranger', 2022, 'RST0Z12', '9BF00011122233344'),
(96, 5, 'Hyundai', 'HB20S', 2018, 'UVW3A45', '9BD00011122255566'),
(97, 6, 'Toyota', 'Etios', 2016, 'XYZ6B78', '9BR00011122277788'),
(98, 7, 'Honda', 'Fit', 2015, 'ABC9C01', '9T000111222999000'),
(99, 8, 'Renault', 'Duster', 2017, 'DEF2D34', '93Y00011122200011'),
(100, 9, 'Jeep', 'Commander', 2023, 'GHI5E67', '9C400011122200022'),
(101, 10, 'Nissan', 'Versa', 2020, 'JKL8F90', '9N000111222000333'),
(102, 11, 'Volkswagen', 'Virtus', 2021, 'MNO1G23', '9BWZZZ5X0FP000444'),
(103, 12, 'Fiat', 'Mobi', 2019, 'PQR4H56', '9BD00011122255566'),
(104, 13, 'Chevrolet', 'Cruze', 2018, 'STU7I89', '9BG00011122277788'),
(105, 14, 'Ford', 'Focus', 2016, 'VWX0J12', '9BF00011122299900'),
(106, 15, 'Hyundai', 'HB20X', 2019, 'YZA3K45', '9BD00011122200011'),
(107, 16, 'Toyota', 'Corolla Cross', 2022, 'BCD6L78', '9BR00011122200022'),
(108, 17, 'Honda', 'City', 2020, 'EFG9M01', '9T000111222000333'),
(109, 18, 'Renault', 'Logan', 2016, 'HIJ2N34', '93Y00011122200044'),
(110, 19, 'Jeep', 'Commander', 2023, 'KLM5O67', '9C400011122200055'),
(111, 20, 'Nissan', 'Sentra', 2021, 'NOP8P90', '9N000111222000666'),
(112, 21, 'Volkswagen', 'Nivus', 2022, 'QRS1Q23', '9BWZZZ5X0FP000777'),
(113, 22, 'Fiat', 'Pulse', 2023, 'TUV4R56', '9BD00011122288899'),
(114, 23, 'Chevrolet', 'Montana', 2023, 'WXY7S89', '9BG00011122200000'),
(115, 24, 'Ford', 'Maverick', 2022, 'ZAB0T12', '9BF00011122211111'),
(116, 25, 'Hyundai', 'HB20 Sense', 2021, 'CDE3U45', '9BD00011122222222'),
(117, 1, 'Toyota', 'Yaris Hatch', 2020, 'FGH6V78', '9BR00011122233333'),
(118, 2, 'Honda', 'CR-V', 2019, 'IJK9W01', '9T000111222444444'),
(119, 3, 'Renault', 'Sandero', 2018, 'LMN2X34', '93Y00011122255555'),
(120, 4, 'Jeep', 'Gladiator', 2023, 'OPQ5Y67', '9C400011122266666'),
(121, 5, 'Nissan', 'Kicks Exclusive', 2022, 'RST8Z90', '9N000111222777777'),
(122, 6, 'Volkswagen', 'Taos', 2022, 'UVW1A01', '9BWZZZ5X0FP000888'),
(123, 7, 'Fiat', 'Fastback', 2023, 'XYZ4B12', '9BD00011122299999'),
(124, 8, 'Chevrolet', 'Spin', 2021, 'ABC7C34', '9BG00011122200001'),
(125, 9, 'Ford', 'Bronco', 2022, 'DEF0D56', '9BF00011122211112'),
(126, 10, 'Hyundai', 'HB20 Platinum', 2023, 'GHI3E78', '9BD00011122222223'),
(127, 11, 'Toyota', 'SW4', 2021, 'JKL6F90', '9BR00011122233334'),
(128, 12, 'Honda', 'WR-V', 2020, 'MNO9G12', '9T000111222444445'),
(129, 13, 'Renault', 'Stepway', 2021, 'PQR2H34', '93Y00011122255556'),
(130, 14, 'Jeep', 'Wrangler', 2022, 'STU5I67', '9C400011122266667'),
(131, 15, 'Nissan', 'Versa Advance', 2023, 'VWX8J90', '9N000111222777778'),
(132, 16, 'Volkswagen', 'Saveiro', 2020, 'YZA1K23', '9BWZZZ5X0FP000999'),
(133, 17, 'Fiat', 'Strada', 2021, 'BCD4L56', '9BD00011122200002'),
(134, 18, 'Chevrolet', 'Onix Plus', 2022, 'EFG7M89', '9BG00011122200003'),
(135, 19, 'Ford', 'Territory', 2023, 'HIJ0N12', '9BF00011122211113'),
(136, 20, 'Hyundai', 'HB20 Comfort', 2022, 'KLM3O45', '9BD00011122222224'),
(137, 21, 'Toyota', 'Corolla Altis', 2021, 'NOP6P78', '9BR00011122233335'),
(138, 22, 'Honda', 'Civic Touring', 2020, 'QRS9Q01', '9T000111222444446'),
(139, 23, 'Renault', 'Megane Grand Tour', 2011, 'TUV2R34', '93Y00011122255557'),
(140, 24, 'Jeep', 'Commander Overland', 2024, 'WXY5S67', '9C400011122266668'),
(141, 25, 'Nissan', 'Kicks Exclusive', 2023, 'ZAB8T90', '9N000111222777779');

-- --------------------------------------------------------

--
-- Estrutura da view `pecas_formatadas` exportado como tabela
--
DROP TABLE IF EXISTS `pecas_formatadas`;
CREATE TABLE`pecas_formatadas`(
    `id` int NOT NULL DEFAULT '0',
    `nome_peca` varchar(100) COLLATE utf8mb4_0900_ai_ci NOT NULL,
    `descricao` text COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
    `fabricante` varchar(50) COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
    `codigo_peca` varchar(50) COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
    `quantidade` int NOT NULL,
    `preco_custo_formatado` varchar(48) COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
    `preco_venda_formatado` varchar(48) COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
    `localizacao_estoque` varchar(50) COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
    `tipo_item` enum('peca_automotiva','pneu','acessorio_borracharia','servico_borracharia') COLLATE utf8mb4_0900_ai_ci DEFAULT 'peca_automotiva'
);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_agendamentos_cliente_idx` (`cliente_id`),
  ADD KEY `fk_agendamentos_veiculo_idx` (`veiculo_id`),
  ADD KEY `fk_agendamentos_servico_idx` (`servico_id`);

--
-- Índices de tabela `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cpf_cnpj_UNIQUE` (`cpf_cnpj`);

--
-- Índices de tabela `itens_os`
--
ALTER TABLE `itens_os`
  ADD PRIMARY KEY (`id`),
  ADD KEY `peca_id_idx` (`peca_id`),
  ADD KEY `os_id_idx` (`os_id`),
  ADD KEY `fk_itens_os_servicos` (`servico_id`);

--
-- Índices de tabela `ordens_servico`
--
ALTER TABLE `ordens_servico`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `pecas`
--
ALTER TABLE `pecas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_peca_UNIQUE` (`codigo_peca`);

--
-- Índices de tabela `servicos`
--
ALTER TABLE `servicos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `solicitacoes_exclusao_clientes`
--
ALTER TABLE `solicitacoes_exclusao_clientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cliente_id` (`cliente_id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username_UNIQUE` (`username`),
  ADD UNIQUE KEY `email_UNIQUE` (`email`);

--
-- Índices de tabela `veiculos`
--
ALTER TABLE `veiculos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `placa_UNIQUE` (`placa`),
  ADD KEY `fk_veiculos_clientes` (`cliente_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de tabela `itens_os`
--
ALTER TABLE `itens_os`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT de tabela `ordens_servico`
--
ALTER TABLE `ordens_servico`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `pecas`
--
ALTER TABLE `pecas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de tabela `servicos`
--
ALTER TABLE `servicos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `solicitacoes_exclusao_clientes`
--
ALTER TABLE `solicitacoes_exclusao_clientes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `veiculos`
--
ALTER TABLE `veiculos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=142;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `agendamentos`
--
ALTER TABLE `agendamentos`
  ADD CONSTRAINT `fk_agendamentos_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_agendamentos_servico` FOREIGN KEY (`servico_id`) REFERENCES `servicos` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_agendamentos_veiculo` FOREIGN KEY (`veiculo_id`) REFERENCES `veiculos` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Restrições para tabelas `itens_os`
--
ALTER TABLE `itens_os`
  ADD CONSTRAINT `fk_itens_os_servicos` FOREIGN KEY (`servico_id`) REFERENCES `servicos` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `os_id` FOREIGN KEY (`os_id`) REFERENCES `ordens_servico` (`id`),
  ADD CONSTRAINT `peca_id` FOREIGN KEY (`peca_id`) REFERENCES `pecas` (`id`);

--
-- Restrições para tabelas `solicitacoes_exclusao_clientes`
--
ALTER TABLE `solicitacoes_exclusao_clientes`
  ADD CONSTRAINT `solicitacoes_exclusao_clientes_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `veiculos`
--
ALTER TABLE `veiculos`
  ADD CONSTRAINT `fk_veiculos_clientes` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
