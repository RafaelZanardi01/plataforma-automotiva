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
-- Table structure for table `clientes`
--

DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clientes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `cpf_cnpj` varchar(20) DEFAULT NULL,
  `tipo_cliente` enum('PF','PJ') DEFAULT NULL,
  `endereco` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cpf_cnpj_UNIQUE` (`cpf_cnpj`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
INSERT INTO `clientes` VALUES (1,'Pedro Mendes','(41) 99123-4567','pedro.mendes@email.com','123.456.789-01','PF','Rua X, 100 - Pinheirinho, Curitiba - PR'),(2,'Oficina Solução Ltda.','(41) 3344-5566','contato@oficinasolucao.com','01.234.567/0001-89','PJ','Av. Winston Churchill, 500 - Pinheirinho, Curitiba - PR'),(3,'Mariana Ferreira','(41) 98876-5432','mariana.f@email.com','234.567.890-12','PF','Rua Izaac Ferreira da Cruz, 250 - Pinheirinho, Curitiba - PR'),(4,'Auto Peças Rápida','(41) 3210-9876','vendas@autopecasrapida.com','12.345.678/0001-90','PJ','Rua da Cidadania, 123 - Pinheirinho, Curitiba - PR'),(5,'Carlos Alberto','(41) 99987-6543','carlos.a@email.com','345.678.901-23','PF','Rua André Ferreira, 30 - Pinheirinho, Curitiba - PR'),(6,'Transportadora Veloz S.A.','(41) 3030-1234','logistica@transveloz.com.br','23.456.789/0001-01','PJ','Rua B, 789 - Pinheirinho, Curitiba - PR'),(7,'Juliana Lima','(41) 99234-5678','juliana.l@email.com','456.789.012-34','PF','Rua Ciclano de Tal, 150 - Pinheirinho, Curitiba - PR'),(8,'Mecânica & Cia.','(41) 3555-4321','mecanicaeciacwb@email.com','34.567.890/0001-12','PJ','Rua Doutor Lauro Gentil Portugal, 400 - Pinheirinho, Curitiba - PR'),(9,'Rafaela Gomes','(41) 98123-4567','rafaela.g@email.com','567.890.123-45','PF','Rua E, 50 - Pinheirinho, Curitiba - PR'),(10,'Borrracharia Ponto Certo','(41) 3000-1111','borracharia@pontocerto.com','45.678.901/0001-23','PJ','Rua Nicola Pellanda, 600 - Pinheirinho, Curitiba - PR'),(11,'Thiago Costa','(41) 99345-6789','thiago.c@email.com','678.901.234-56','PF','Rua F, 200 - Pinheirinho, Curitiba - PR'),(12,'Distr. Autopeças Paraná','(41) 3999-8888','distribuidora@pr.com.br','56.789.012/0001-34','PJ','Rodovia Régis Bittencourt, 1000 - Pinheirinho, Curitiba - PR'),(13,'Aline Souza','(41) 98234-5678','aline.s@email.com','789.012.345-67','PF','Rua G, 350 - Pinheirinho, Curitiba - PR'),(14,'Frotas Rápidas Ltda.','(41) 3111-2222','frota.rapida@email.com','67.890.123/0001-45','PJ','Rua H, 45 - Pinheirinho, Curitiba - PR'),(15,'Gustavo Oliveira','(41) 99456-7890','gustavo.o@email.com','890.123.456-78','PF','Rua K, 120 - Pinheirinho, Curitiba - PR'),(16,'Serviços de Guincho CWB','(41) 3222-3333','guincho@curitiba.com','78.901.234/0001-56','PJ','Rua Marechal Rondon, 70 - Pinheirinho, Curitiba - PR'),(17,'Lívia Pereira','(41) 98345-6789','livia.p@email.com','901.234.567-89','PF','Rua L, 80 - Pinheirinho, Curitiba - PR'),(18,'Auto Elétrica Forte','(41) 3444-5555','eletrica@forte.com','89.012.345/0001-67','PJ','Rua M, 190 - Pinheirinho, Curitiba - PR'),(19,'Lucas Fernandes','(41) 99567-8901','lucas.f@email.com','012.345.678-90','PF','Rua N, 210 - Pinheirinho, Curitiba - PR'),(20,'Distribuidora de Pneus ABC','(41) 3666-7777','vendas@pneusabc.com','90.123.456/0001-78','PJ','Rua P, 300 - Pinheirinho, Curitiba - PR'),(21,'Marta Rodrigues','(41) 98456-7890','marta.r@email.com','101.112.131-41','PF','Rua Q, 450 - Pinheirinho, Curitiba - PR'),(22,'Lataria e Pintura Top','(41) 3777-8888','top@latariapintura.com','01.011.012/0001-89','PJ','Rua R, 15 - Pinheirinho, Curitiba - PR'),(23,'Natália Santos','(41) 99678-9012','natalia.s@email.com','112.131.415-52','PF','Rua S, 88 - Pinheirinho, Curitiba - PR'),(24,'Despachante Legal','(41) 3888-9999','contato@despachante.com','02.023.024/0001-90','PJ','Rua T, 220 - Pinheirinho, Curitiba - PR'),(25,'Otávio Pires','(41) 98567-8901','otavio.p@email.com','123.141.516-63','PF','Rua U, 330 - Pinheirinho, Curitiba - PR');
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
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
