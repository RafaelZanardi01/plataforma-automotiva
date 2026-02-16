-- MySQL dump 10.13  Distrib 8.0.44, for Win64 (x86_64)
--
-- Host: localhost    Database: plataforma_automotiva
-- ------------------------------------------------------
-- Server version	8.0.37

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `pecas`
--

DROP TABLE IF EXISTS `pecas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pecas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome_peca` varchar(100) NOT NULL,
  `descricao` text,
  `fabricante` varchar(50) DEFAULT NULL,
  `codigo_peca` varchar(50) DEFAULT NULL,
  `quantidade` int NOT NULL,
  `preco_custo` decimal(10,2) DEFAULT NULL,
  `preco_venda` decimal(10,2) DEFAULT NULL,
  `localizacao_estoque` varchar(50) DEFAULT NULL,
  `tipo_item` enum('peca_automotiva','pneu','acessorio_borracharia','servico_borracharia') DEFAULT 'peca_automotiva',
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo_peca_UNIQUE` (`codigo_peca`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pecas`
--

LOCK TABLES `pecas` WRITE;
/*!40000 ALTER TABLE `pecas` DISABLE KEYS */;
INSERT INTO `pecas` VALUES (1,'Filtro de Óleo','Filtro de óleo para motores a gasolina','Fram','FRM-PH5800',50,15.50,32.90,'A1-01','peca_automotiva'),(2,'Vela de Ignição','Vela de ignição padrão para diversos modelos','NGK','NGK-BKR6E',100,8.20,19.90,'A1-02','peca_automotiva'),(3,'Pastilha de Freio Dianteira','Pastilha de freio dianteira, cerâmica','Cobreq','COB-N1234',30,85.00,179.90,'B2-03','peca_automotiva'),(4,'Disco de Freio Dianteiro','Disco de freio ventilado','Fremax','FRX-BD2345',20,120.00,250.00,'B2-04','peca_automotiva'),(5,'Amortecedor Dianteiro','Amortecedor dianteiro a gás','Monroe','MON-SP3456',15,210.00,420.00,'C3-05','peca_automotiva'),(6,'Correia Dentada','Correia dentada para motor 1.0 8v','Gates','GTS-T123',40,55.00,110.00,'A1-06','peca_automotiva'),(7,'Bateria 60Ah','Bateria automotiva 60 Amperes','Moura','MOU-M60AD',10,350.00,699.00,'D4-07','peca_automotiva'),(8,'Pneu Aro 15 185/65 R15','Pneu para carros de passeio','Pirelli','PIR-CINTURATO',25,280.00,550.00,'P1-01','peca_automotiva'),(9,'Palheta Limpador Para-brisa','Palheta Aerofit 22 polegadas','Bosch','BOS-A001S',60,25.00,59.90,'A1-08','peca_automotiva'),(10,'Lâmpada Farol H7','Lâmpada halógena para farol baixo','Osram','OSR-H7NBP',80,18.00,45.00,'A1-09','peca_automotiva'),(11,'Óleo de Motor 5W30 Sintético','Óleo lubrificante para motores modernos','Castrol','CAS-EDGE5W30',70,45.00,89.90,'L5-10','peca_automotiva'),(12,'Aditivo Radiador Concentrado','Aditivo para sistema de arrefecimento','Bardahl','BAR-RADPLUS',35,22.00,48.00,'A1-11','peca_automotiva'),(13,'Cabo de Vela','Jogo de cabos de vela 4 cilindros','NGK','NGK-SC-C37',20,65.00,130.00,'A1-12','peca_automotiva'),(14,'Bomba de Água','Bomba de água para motor 1.6','Urba','URB-BA123',12,150.00,300.00,'B2-13','peca_automotiva'),(15,'Kit Embreagem','Kit completo de embreagem','Luk','LUK-620308200',8,480.00,950.00,'C3-14','peca_automotiva'),(16,'Rolamento de Roda','Rolamento de roda dianteira','SKF','SKF-VKBA3585',25,90.00,185.00,'B2-15','peca_automotiva'),(17,'Junta do Cabeçote','Junta do cabeçote de motor','Sabó','SAB-65432',18,70.00,140.00,'A1-16','peca_automotiva'),(18,'Filtro de Ar Condicionado','Filtro de cabine anti-pólen','Mann-Filter','MAN-CU2500',45,30.00,65.00,'A1-17','peca_automotiva'),(19,'Sensor de Rotação','Sensor de rotação do motor','MTE-Thomson','MTE-7023',15,75.00,150.00,'A1-18','peca_automotiva'),(20,'Termostato','Válvula termostática','Wahler','WAH-410078D',20,40.00,80.00,'A1-19','peca_automotiva'),(21,'Pneu Aro 15 185/65 R15','Pneu para carros de passeio, linha standard','Goodyear','GY-1856515',30,250.00,499.00,'PISO-A1','pneu'),(22,'Pneu Aro 16 205/55 R16','Pneu de alta performance para veículos médios','Michelin','MICH-2055516',20,380.00,750.00,'PISO-A2','pneu'),(23,'Câmara de Ar Aro 14','Câmara de ar para pneus aro 14','Rinaldi','RIN-CAM14',50,15.00,35.00,'BORR-B1','acessorio_borracharia'),(24,'Válvula de Pneu TR414','Válvula de borracha para pneus sem câmara','Schrader','SCHR-TR414',100,2.50,8.00,'BORR-B2','acessorio_borracharia'),(25,'Serviço de Alinhamento e Balanceamento','Serviço completo de alinhamento e balanceamento de rodas','Serviço Interno','SERV-ALBAL',9999,50.00,120.00,'BORR-SERV','servico_borracharia'),(26,'Filtro de Ar',NULL,'Fram','FRM-FILT-AR',30,25.00,50.00,NULL,'peca_automotiva'),(27,'Pneu Michelin Aro 18',NULL,'Michelin','MICHELIN-18',15,450.00,900.00,NULL,'pneu');
/*!40000 ALTER TABLE `pecas` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-16 16:37:23
