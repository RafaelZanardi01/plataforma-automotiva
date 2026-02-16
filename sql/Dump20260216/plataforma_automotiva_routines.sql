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
-- Temporary view structure for view `pecas_formatadas`
--

DROP TABLE IF EXISTS `pecas_formatadas`;
/*!50001 DROP VIEW IF EXISTS `pecas_formatadas`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `pecas_formatadas` AS SELECT 
 1 AS `id`,
 1 AS `nome_peca`,
 1 AS `descricao`,
 1 AS `fabricante`,
 1 AS `codigo_peca`,
 1 AS `quantidade`,
 1 AS `preco_custo_formatado`,
 1 AS `preco_venda_formatado`,
 1 AS `localizacao_estoque`,
 1 AS `tipo_item`*/;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `pecas_formatadas`
--

/*!50001 DROP VIEW IF EXISTS `pecas_formatadas`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `pecas_formatadas` AS select `pecas`.`id` AS `id`,`pecas`.`nome_peca` AS `nome_peca`,`pecas`.`descricao` AS `descricao`,`pecas`.`fabricante` AS `fabricante`,`pecas`.`codigo_peca` AS `codigo_peca`,`pecas`.`quantidade` AS `quantidade`,replace(format(`pecas`.`preco_custo`,2),'.',',') AS `preco_custo_formatado`,replace(format(`pecas`.`preco_venda`,2),'.',',') AS `preco_venda_formatado`,`pecas`.`localizacao_estoque` AS `localizacao_estoque`,`pecas`.`tipo_item` AS `tipo_item` from `pecas` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Dumping events for database 'plataforma_automotiva'
--

--
-- Dumping routines for database 'plataforma_automotiva'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-16 16:37:26
