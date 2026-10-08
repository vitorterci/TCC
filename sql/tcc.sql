-- ============================================================
-- GAME SEARCH / TCC
-- SCRIPT CONSOLIDADO DOS 4 BANCOS
-- Bancos: tcc, fake_epic, fake_steam, fake_gog
-- Gerado a partir do dump atual enviado.
-- ============================================================

SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET time_zone = '+00:00';
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS = 0;

-- ============================================================
-- BANCO: tcc
-- ============================================================

CREATE DATABASE IF NOT EXISTS `tcc`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `tcc`;

SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS = 0;

-- Host: 127.0.0.1    Database: tcc
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

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
-- Table structure for table `comentarios`
--

DROP TABLE IF EXISTS `comentarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comentarios` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned NOT NULL,
  `jogo_id` int(10) unsigned NOT NULL,
  `comentario` text NOT NULL,
  `data_criacao` datetime NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_comentarios_usuario` (`usuario_id`),
  KEY `idx_comentarios_jogo` (`jogo_id`),
  KEY `idx_comentarios_jogo_data` (`jogo_id`,`data_criacao`),
  CONSTRAINT `comentarios_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comentarios_ibfk_2` FOREIGN KEY (`jogo_id`) REFERENCES `jogos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comentarios`
--

LOCK TABLES `comentarios` WRITE;
/*!40000 ALTER TABLE `comentarios` DISABLE KEYS */;
/*!40000 ALTER TABLE `comentarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feedback`
--

DROP TABLE IF EXISTS `feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `feedback` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned DEFAULT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `tipo_feedback` enum('sugestao','reclamacao','elogio','duvida') NOT NULL DEFAULT 'sugestao',
  `mensagem` text NOT NULL,
  `avaliacao` int(11) NOT NULL DEFAULT 5,
  `status` enum('pendente','novo','lido','respondido') NOT NULL DEFAULT 'pendente',
  `data_envio` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `idx_feedback_status` (`status`),
  CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `feedback`
--

LOCK TABLES `feedback` WRITE;
/*!40000 ALTER TABLE `feedback` DISABLE KEYS */;
INSERT INTO `feedback` VALUES (1,NULL,'','','sugestao','efe',5,'pendente','2026-09-30 13:50:04'),(3,NULL,'pinto','pinto@gmail.com','reclamacao','Não Gostei',1,'pendente','2026-10-01 13:46:59');
/*!40000 ALTER TABLE `feedback` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `generos`
--

DROP TABLE IF EXISTS `generos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `generos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `data_cadastro` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_generos_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `generos`
--

LOCK TABLES `generos` WRITE;
/*!40000 ALTER TABLE `generos` DISABLE KEYS */;
INSERT INTO `generos` VALUES (1,'Action RPG','action-rpg','2026-09-18 18:23:04'),(2,'Action Adventure','action-adventure','2026-09-18 18:23:04'),(3,'CRPG','crpg','2026-09-18 18:23:04'),(4,'Metroidvania','metroidvania','2026-09-18 18:23:04'),(5,'Roguelike','roguelike','2026-09-18 18:23:04'),(6,'Platformer','platformer','2026-09-18 18:23:04'),(7,'Simulation','simulation','2026-09-18 18:23:04'),(8,'Sandbox','sandbox','2026-09-18 18:23:04'),(9,'Survival Horror','survival-horror','2026-09-18 18:23:04'),(10,'JRPG','jrpg','2026-09-18 18:23:04'),(11,'Run and Gun','run-and-gun','2026-09-18 18:23:04'),(12,'Co-op Adventure','co-op-adventure','2026-09-18 18:23:04'),(13,'Soulslike','soulslike','2026-09-18 18:23:04'),(14,'FPS','fps','2026-09-18 18:23:04'),(15,'Estratégia','estrategia','2026-09-18 18:23:04');
/*!40000 ALTER TABLE `generos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jogo_genero`
--

DROP TABLE IF EXISTS `jogo_genero`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jogo_genero` (
  `jogo_id` int(10) unsigned NOT NULL,
  `genero_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`jogo_id`,`genero_id`),
  KEY `idx_jg_genero` (`genero_id`),
  CONSTRAINT `fk_jg_genero` FOREIGN KEY (`genero_id`) REFERENCES `generos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_jg_jogo` FOREIGN KEY (`jogo_id`) REFERENCES `jogos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jogo_genero`
--

LOCK TABLES `jogo_genero` WRITE;
/*!40000 ALTER TABLE `jogo_genero` DISABLE KEYS */;
/*!40000 ALTER TABLE `jogo_genero` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jogo_plataforma`
--

DROP TABLE IF EXISTS `jogo_plataforma`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jogo_plataforma` (
  `jogo_id` int(10) unsigned NOT NULL,
  `plataforma_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`jogo_id`,`plataforma_id`),
  KEY `idx_jp_plataforma` (`plataforma_id`),
  CONSTRAINT `fk_jp_jogo` FOREIGN KEY (`jogo_id`) REFERENCES `jogos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_jp_plataforma` FOREIGN KEY (`plataforma_id`) REFERENCES `plataformas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jogo_plataforma`
--

LOCK TABLES `jogo_plataforma` WRITE;
/*!40000 ALTER TABLE `jogo_plataforma` DISABLE KEYS */;
/*!40000 ALTER TABLE `jogo_plataforma` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jogos`
--

DROP TABLE IF EXISTS `jogos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jogos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) NOT NULL,
  `slug` varchar(180) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `img` varchar(255) DEFAULT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `plataforma` varchar(255) DEFAULT NULL,
  `genero` varchar(255) DEFAULT NULL,
  `ano` int(11) DEFAULT NULL,
  `etaria` varchar(10) DEFAULT NULL,
  `status` enum('ativo','inativo') NOT NULL DEFAULT 'ativo',
  `avaliacao_gamplay` decimal(3,1) NOT NULL DEFAULT 0.0,
  `avaliacao_graficos` decimal(3,1) NOT NULL DEFAULT 0.0,
  `avaliacao_historia` decimal(3,1) NOT NULL DEFAULT 0.0,
  `data_lancamento` date DEFAULT NULL,
  `data_cadastro` datetime NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_slug` (`slug`),
  KEY `idx_jogos_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jogos`
--

LOCK TABLES `jogos` WRITE;
/*!40000 ALTER TABLE `jogos` DISABLE KEYS */;
INSERT INTO `jogos` VALUES (1,'Alan Wake 2','alan-wake-2','Survival horror narrativo da Remedy Entertainment que acompanha o escritor Alan Wake e a agente do FBI Saga Anderson em uma investigação sobrenatural.','games/alan-wake-2.webp','','PC','',2023,'18','ativo',0.0,0.0,0.0,'2023-10-27','2026-09-29 14:37:55','2026-09-30 13:52:23'),(2,'Baldur\'s Gate 3','baldurs-gate-3','RPG baseado no universo de Dungeons & Dragons, com exploração, combate por turnos, escolhas narrativas e grande liberdade de criação de personagens.','games/baldurs-gate-3.webp','CRPG','PC, PS5, Xbox Series','CRPG',2023,'14','ativo',0.0,0.0,0.0,'2023-08-03','2026-09-29 14:37:55','2026-09-29 14:37:55'),(3,'Bloodborne','bloodborne','Action RPG ambientado na cidade gótica de Yharnam, onde o jogador assume o papel de um Caçador durante uma misteriosa noite de caça.','games/bloodborne.webp','Action RPG','PS4','Action RPG',2015,'16','ativo',0.0,0.0,0.0,'2015-03-24','2026-09-29 14:37:55','2026-09-29 14:37:55'),(4,'Celeste','celeste','Plataforma de precisão que acompanha Madeline durante sua escalada da Montanha Celeste, combinando desafios de movimentação com uma narrativa pessoal.','games/celeste.webp','Platformer','PC, PS4, Xbox One, Switch','Platformer',2018,'10','ativo',0.0,0.0,0.0,'2018-01-25','2026-09-29 14:37:55','2026-09-29 14:37:55'),(5,'Cuphead','cuphead','Run and gun inspirado em animações dos anos 1930, conhecido por batalhas contra chefes, visual desenhado à mão e trilha sonora de jazz.','games/cuphead.webp','Run and Gun','PC, PS4, Xbox One, Switch','Run and Gun',2017,'L','ativo',0.0,0.0,0.0,'2017-09-29','2026-09-29 14:37:55','2026-09-29 14:37:55'),(6,'Cyberpunk 2077','cyberpunk-2077','RPG de ação em mundo aberto ambientado em Night City, onde o mercenário V busca construir sua própria história em uma sociedade dominada por tecnologia e megacorporações.','games/cyberpunk-2077.webp','Action RPG','PC, PS5, Xbox Series, PS4, Xbox One','Action RPG',2020,'18','ativo',0.0,0.0,0.0,'2020-12-10','2026-09-29 14:37:55','2026-09-29 14:37:55'),(7,'Dark Souls III','dark-souls-3','Action RPG da FromSoftware ambientado no decadente reino de Lothric, com combate desafiador, exploração e narrativa construída através do mundo do jogo.','games/dark-souls-3.webp','Action RPG','PC, PS4, Xbox One','Soulslike',2016,'16','ativo',0.0,0.0,0.0,'2016-03-24','2026-09-29 14:37:55','2026-09-29 14:37:55'),(8,'Elden Ring','elden-ring','Action RPG de mundo aberto desenvolvido pela FromSoftware, ambientado nas Terras Intermédias e focado em exploração, combate e construção livre de personagem.','games/elden-ring.webp','Action RPG','PC, PS4, PS5, Xbox One, Xbox Series','Soulslike',2022,'16','ativo',0.0,0.0,0.0,'2022-02-25','2026-09-29 14:37:55','2026-09-29 14:37:55'),(9,'Final Fantasy XVI','final-fantasy-xvi','RPG de ação da série Final Fantasy ambientado em Valisthea, acompanhando Clive Rosfield em uma história marcada pelos Dominantes e pelos Eikons.','games/final-fantasy-xvi.webp','Action RPG','PS5, PC','Action RPG',2023,'16','ativo',0.0,0.0,0.0,'2023-06-22','2026-09-29 14:37:55','2026-09-29 14:37:55'),(10,'Ghost of Tsushima','ghost-of-tsushima','Aventura de ação em mundo aberto ambientada na ilha de Tsushima durante a invasão mongol, combinando combate com espada, arco e furtividade.','games/ghost-of-tsushima.webp','Action Adventure','PS4, PS5, PC','Action Adventure',2020,'16','ativo',0.0,0.0,0.0,'2020-07-17','2026-09-29 14:37:55','2026-09-29 14:37:55'),(11,'God of War Ragnarök','god-of-war-ragnarok','Aventura de ação que acompanha Kratos e Atreus durante os acontecimentos que antecedem o Ragnarök nos Nove Reinos da mitologia nórdica.','games/god-of-war-ragnarok.webp','Action Adventure','PS4, PS5, PC','Action Adventure',2022,'18','ativo',0.0,0.0,0.0,'2022-11-09','2026-09-29 14:37:55','2026-09-29 14:37:55'),(12,'Grand Theft Auto V','gta-v','Jogo de ação em mundo aberto ambientado em Los Santos, acompanhando três protagonistas envolvidos em crimes, perseguições e grandes golpes.','games/gta-v.webp','Sandbox','PC, PS4, PS5, Xbox One, Xbox Series','Sandbox',2013,'18','ativo',0.0,0.0,0.0,'2013-09-17','2026-09-29 14:37:55','2026-09-29 14:37:55'),(13,'Hades','hades','Roguelike de ação da Supergiant Games no qual Zagreus tenta escapar do submundo enfrentando inimigos e chefes enquanto descobre novas partes da história.','games/hades.webp','Roguelike','PC, PS4, PS5, Xbox One, Xbox Series, Switch','Roguelike',2020,'12','ativo',0.0,0.0,0.0,'2020-09-17','2026-09-29 14:37:55','2026-09-29 14:37:55'),(14,'Hollow Knight','hollow-knight','Metroidvania de ação ambientado no reino subterrâneo de Hallownest, combinando exploração, combate, chefes e descoberta de habilidades.','games/hollow-knight.webp','Metroidvania','PC, PS4, Xbox One, Switch','Metroidvania',2017,'10','ativo',0.0,0.0,0.0,'2017-02-24','2026-09-29 14:37:55','2026-09-29 14:37:55'),(15,'Horizon Forbidden West','horizon-forbidden-west','Aventura de ação em mundo aberto que acompanha Aloy em uma jornada por uma região pós-apocalíptica dominada por máquinas.','games/horizon-forbidden-west.webp','Action Adventure','PS4, PS5, PC','Action Adventure',2022,'14','ativo',0.0,0.0,0.0,'2022-02-18','2026-09-29 14:37:55','2026-09-29 14:37:55'),(16,'It Takes Two','it-takes-two','Aventura cooperativa criada especificamente para dois jogadores, combinando plataforma, quebra-cabeças, ação e diferentes mecânicas ao longo da jornada.','games/it-takes-two.webp','Co-op Adventure','PC, PS4, PS5, Xbox One, Xbox Series, Switch','Co-op Adventure',2021,'10','ativo',0.0,0.0,0.0,'2021-03-25','2026-09-29 14:37:55','2026-09-29 14:37:55'),(17,'Minecraft','minecraft','Jogo sandbox baseado em exploração, coleta de recursos, construção e sobrevivência em mundos gerados proceduralmente.','games/minecraft.webp','Sandbox','PC, PS4, PS5, Xbox One, Xbox Series, Switch, Mobile','Sandbox',2011,'L','ativo',0.0,0.0,0.0,'2011-11-18','2026-09-29 14:37:55','2026-09-29 14:37:55'),(18,'NieR: Automata','nier-automata','RPG de ação ambientado em uma Terra devastada por máquinas, acompanhando os androides 2B, 9S e A2.','games/nier-automata.webp','Action RPG','PC, PS4, Xbox One','Action RPG',2017,'14','ativo',0.0,0.0,0.0,'2017-02-23','2026-09-29 14:37:55','2026-09-29 14:37:55'),(19,'Ori and the Will of the Wisps','ori-and-the-will-of-the-wisps','Aventura de plataforma e exploração em estilo Metroidvania, com combate, habilidades de movimentação e um mundo interconectado.','games/ori-and-the-will-of-the-wisps.webp','Metroidvania','PC, Xbox One, Xbox Series, Switch','Metroidvania',2020,'L','ativo',0.0,0.0,0.0,'2020-03-11','2026-09-29 14:37:55','2026-09-29 14:37:55'),(20,'Persona 5 Royal','persona-5-royal','JRPG que combina vida escolar, relacionamentos e exploração de masmorras, acompanhando os Phantom Thieves em uma história sobrenatural.','games/persona-5-royal.webp','JRPG','PS4, PS5, Xbox One, Xbox Series, Switch, PC','JRPG',2019,'16','ativo',0.0,0.0,0.0,'2019-10-31','2026-09-29 14:37:55','2026-09-29 14:37:55'),(21,'Red Dead Redemption 2','red-dead-redemption-2','Aventura de ação em mundo aberto ambientada no Velho Oeste, acompanhando Arthur Morgan e a gangue Van der Linde.','games/red-dead-redemption-2.webp','Action Adventure','PC, PS4, Xbox One','Action Adventure',2018,'18','ativo',0.0,0.0,0.0,'2018-10-26','2026-09-29 14:37:55','2026-09-29 14:37:55'),(22,'Resident Evil 4','resident-evil-4-remake','Remake do survival horror de ação em que Leon S. Kennedy é enviado para uma região rural da Europa em uma missão para resgatar Ashley Graham.','games/resident-evil-4-remake.webp','Survival Horror','PC, PS4, PS5, Xbox Series','Survival Horror',2023,'18','ativo',0.0,0.0,0.0,'2023-03-24','2026-09-29 14:37:55','2026-09-29 14:37:55'),(23,'Sekiro: Shadows Die Twice','sekiro','Action RPG de ação desenvolvido pela FromSoftware, centrado em combate com espada, furtividade e habilidades de um shinobi.','games/sekiro.webp','Action RPG','PC, PS4, Xbox One','Soulslike',2019,'16','ativo',0.0,0.0,0.0,'2019-03-22','2026-09-29 14:37:55','2026-09-29 14:37:55'),(24,'Marvel\'s Spider-Man 2','spider-man-2','Aventura de ação em mundo aberto que coloca Peter Parker e Miles Morales contra novas ameaças em Nova York.','games/spider-man-2.webp','Action Adventure','PS5, PC','Action Adventure',2023,'12','ativo',0.0,0.0,0.0,'2023-10-20','2026-09-29 14:37:55','2026-09-29 14:37:55'),(25,'Stardew Valley','stardew-valley','Simulação de fazenda com exploração, agricultura, pesca, criação de animais, relacionamentos e descoberta de uma comunidade rural.','games/stardew-valley.webp','Simulation','PC, PS4, Xbox One, Switch, Mobile','Simulation',2016,'L','ativo',0.0,0.0,0.0,'2016-02-26','2026-09-29 14:37:55','2026-09-29 14:37:55'),(26,'Starfield','starfield','RPG espacial da Bethesda ambientado em um universo futurista de exploração, descoberta, combate espacial e personalização de personagens.','games/starfield.webp','CRPG','PC, Xbox Series','CRPG',2023,'16','ativo',0.0,0.0,0.0,'2023-09-06','2026-09-29 14:37:55','2026-09-29 14:37:55'),(27,'Terraria','terraria','Sandbox de aventura em 2D focado em exploração, mineração, construção, criação de equipamentos e combate contra inimigos e chefes.','games/terraria.webp','Sandbox','PC, PS4, Xbox One, Switch, Mobile','Sandbox',2011,'12','ativo',0.0,0.0,0.0,'2011-05-16','2026-09-29 14:37:55','2026-09-29 14:37:55'),(28,'The Last of Us Part II','the-last-of-us-part-ii','Aventura de ação narrativa ambientada em um mundo pós-apocalíptico, acompanhando Ellie em uma jornada marcada por conflitos e sobrevivência.','games/the-last-of-us-part-ii.webp','Action Adventure','PS4, PS5','Action Adventure',2020,'18','ativo',0.0,0.0,0.0,'2020-06-19','2026-09-29 14:37:55','2026-09-29 14:37:55'),(29,'The Legend of Zelda: Tears of the Kingdom','the-legend-of-zelda-totk','Aventura de ação em mundo aberto que expande Hyrule para ilhas celestes e regiões subterrâneas, com novas habilidades de exploração e construção.','games/the-legend-of-zelda-totk.webp','Action Adventure','Nintendo Switch','Action Adventure',2023,'10','ativo',0.0,0.0,0.0,'2023-05-12','2026-09-29 14:37:55','2026-09-29 14:37:55'),(30,'The Witcher 3: Wild Hunt','the-witcher-3','RPG de mundo aberto baseado na série literária de Andrzej Sapkowski, acompanhando Geralt de Rívia em uma busca por Ciri.','games/the-witcher-3.webp','Action RPG','PC, PS4, PS5, Xbox One, Xbox Series, Switch','Action RPG',2015,'16','ativo',0.0,0.0,0.0,'2015-05-19','2026-09-29 14:37:55','2026-09-29 14:37:55');
/*!40000 ALTER TABLE `jogos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plataformas`
--

DROP TABLE IF EXISTS `plataformas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plataformas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `data_cadastro` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_plataformas_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plataformas`
--

LOCK TABLES `plataformas` WRITE;
/*!40000 ALTER TABLE `plataformas` DISABLE KEYS */;
INSERT INTO `plataformas` VALUES (1,'PC','pc','2026-09-18 18:23:04'),(2,'PS5','ps5','2026-09-18 18:23:04'),(3,'PS4','ps4','2026-09-18 18:23:04'),(4,'Xbox Series','xbox-series','2026-09-18 18:23:04'),(5,'Xbox One','xbox-one','2026-09-18 18:23:04'),(6,'Nintendo Switch','nintendo-switch','2026-09-18 18:23:04'),(7,'Mobile','mobile','2026-09-18 18:23:04');
/*!40000 ALTER TABLE `plataformas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `precos`
--

DROP TABLE IF EXISTS `precos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `precos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `jogo_id` int(10) unsigned NOT NULL,
  `loja` varchar(100) NOT NULL,
  `plataforma` varchar(100) DEFAULT NULL,
  `preco` decimal(10,2) NOT NULL DEFAULT 0.00,
  `preco_antigo` decimal(10,2) DEFAULT NULL,
  `desconto` decimal(5,2) NOT NULL DEFAULT 0.00,
  `desconto_percentual` decimal(5,2) NOT NULL DEFAULT 0.00,
  `moeda` char(3) NOT NULL DEFAULT 'BRL',
  `disponibilidade` enum('disponivel','indisponivel') NOT NULL DEFAULT 'disponivel',
  `url` varchar(255) DEFAULT NULL,
  `url_oferta` varchar(500) DEFAULT NULL,
  `disponivel` tinyint(1) NOT NULL DEFAULT 1,
  `data_atualizacao` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_precos_jogo` (`jogo_id`),
  KEY `idx_precos_disponibilidade` (`disponibilidade`),
  CONSTRAINT `precos_ibfk_1` FOREIGN KEY (`jogo_id`) REFERENCES `jogos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `precos`
--

LOCK TABLES `precos` WRITE;
/*!40000 ALTER TABLE `precos` DISABLE KEYS */;
/*!40000 ALTER TABLE `precos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario_jogos`
--

DROP TABLE IF EXISTS `usuario_jogos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario_jogos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned NOT NULL,
  `jogo_id` int(10) unsigned NOT NULL,
  `status` enum('possuo','jogando','zerado','abandonado') DEFAULT 'possuo',
  `data_adicionado` datetime DEFAULT current_timestamp(),
  `data_atualizacao` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_usuario_jogo` (`usuario_id`,`jogo_id`),
  KEY `jogo_id` (`jogo_id`),
  CONSTRAINT `usuario_jogos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `usuario_jogos_ibfk_2` FOREIGN KEY (`jogo_id`) REFERENCES `jogos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario_jogos`
--

LOCK TABLES `usuario_jogos` WRITE;
/*!40000 ALTER TABLE `usuario_jogos` DISABLE KEYS */;
INSERT INTO `usuario_jogos` VALUES (3,3,29,'possuo','2026-09-30 13:49:18','2026-09-30 13:49:18'),(4,1,29,'possuo','2026-10-01 12:50:59','2026-10-01 12:53:17');
/*!40000 ALTER TABLE `usuario_jogos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario_lista`
--

DROP TABLE IF EXISTS `usuario_lista`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario_lista` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned NOT NULL,
  `jogo_id` int(10) unsigned NOT NULL,
  `notificar_promocao` tinyint(1) DEFAULT 1,
  `notificar_preco` tinyint(1) DEFAULT 1,
  `preco_alvo` decimal(10,2) DEFAULT NULL,
  `desconto_minimo` decimal(5,2) DEFAULT NULL,
  `data_adicionado` datetime DEFAULT current_timestamp(),
  `data_atualizacao` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_usuario_lista` (`usuario_id`,`jogo_id`),
  KEY `jogo_id` (`jogo_id`),
  CONSTRAINT `usuario_lista_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `usuario_lista_ibfk_2` FOREIGN KEY (`jogo_id`) REFERENCES `jogos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario_lista`
--

LOCK TABLES `usuario_lista` WRITE;
/*!40000 ALTER TABLE `usuario_lista` DISABLE KEYS */;
INSERT INTO `usuario_lista` VALUES (2,3,29,0,0,NULL,NULL,'2026-09-30 13:49:17','2026-09-30 13:49:17'),(3,1,29,0,0,NULL,NULL,'2026-10-01 08:08:27','2026-10-01 08:08:27');
/*!40000 ALTER TABLE `usuario_lista` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario_preferencias`
--

DROP TABLE IF EXISTS `usuario_preferencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario_preferencias` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned NOT NULL,
  `desconto_minimo` decimal(5,2) DEFAULT 0.00,
  `preco_maximo` decimal(10,2) DEFAULT NULL,
  `notificar_promocoes` tinyint(1) DEFAULT 1,
  `notificar_queda_preco` tinyint(1) DEFAULT 1,
  `plataformas_interesse` text DEFAULT NULL,
  `generos_interesse` text DEFAULT NULL,
  `lojas_interesse` text DEFAULT NULL,
  `data_atualizacao` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_usuario_preferencias` (`usuario_id`),
  CONSTRAINT `usuario_preferencias_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario_preferencias`
--

LOCK TABLES `usuario_preferencias` WRITE;
/*!40000 ALTER TABLE `usuario_preferencias` DISABLE KEYS */;
/*!40000 ALTER TABLE `usuario_preferencias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `role` enum('usuario','admin') NOT NULL DEFAULT 'usuario',
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `preferencias_cor` varchar(20) DEFAULT NULL,
  `preferencias_animacoes` tinyint(1) NOT NULL DEFAULT 0,
  `data_cadastro` datetime NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_usuario` (`usuario`),
  UNIQUE KEY `unique_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'1','1','123@gmail.com','$2y$10$Zbji9u8VFowXIm3aVA0SgOo6NtqIZeL/ZqbPW7FEyu9Vjw9W6n9IK','admin',1,'uploads/perfil/usuario_1_bbaed7d0a2bf60caa642f69f.gif',NULL,0,'2026-09-18 16:13:20','2026-09-30 11:45:16'),(2,'abc','abc','abc@gmail.com','$2y$10$Iach7TRP/ewAyAicEZA7q.IzFcoQmtYygC78L9yexmOEetNaKD6QO','usuario',1,NULL,NULL,0,'2026-09-30 08:15:40','2026-09-30 08:15:40'),(3,'vitorxcxzczx','vitor','v@gmail.com','$2y$10$Q6kKBirGOw5adoPeEzF0heg9q4ViAgaPmZ0fQ6xxNCO/WvKB7Ypfy','usuario',1,'uploads/perfil/usuario_3_86910b3da5706ba27f8e1888.gif',NULL,0,'2026-09-30 13:43:37','2026-09-30 13:49:01'),(4,'Pinto Grosso','pinto','pinto@gmail.com','$2y$10$GLQVfwFoDruCcWsXCXj4jezbBeJaoNxtIB8gi5Qs3tyh.epWccm/2','admin',1,NULL,NULL,0,'2026-10-01 13:44:39','2026-10-01 13:47:54');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-05 11:38:26
-- MySQL dump 10.13  Distrib 8.0.38, for Win64 (x86_64)
--

SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS = 1;

-- ============================================================
-- BANCO: fake_epic
-- ============================================================

CREATE DATABASE IF NOT EXISTS `fake_epic`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `fake_epic`;

SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS = 0;

-- Host: 127.0.0.1    Database: fake_epic
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

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
-- Table structure for table `descontos`
--

DROP TABLE IF EXISTS `descontos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `descontos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `preco_id` int(10) unsigned NOT NULL,
  `percentual` decimal(5,2) NOT NULL DEFAULT 0.00,
  `inicio` datetime DEFAULT NULL,
  `fim` datetime DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `data_atualizacao` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_descontos_preco` (`preco_id`),
  KEY `idx_descontos_ativo` (`ativo`),
  KEY `idx_descontos_percentual` (`percentual`),
  KEY `idx_descontos_periodo` (`inicio`,`fim`),
  CONSTRAINT `fk_descontos_preco` FOREIGN KEY (`preco_id`) REFERENCES `precos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `descontos`
--

LOCK TABLES `descontos` WRITE;
/*!40000 ALTER TABLE `descontos` DISABLE KEYS */;
INSERT INTO `descontos` VALUES (1,1,50.00,'2026-08-01 00:00:00','2026-12-31 23:59:59',1,'2026-08-27 11:01:38'),(2,2,50.00,'2026-08-01 00:00:00','2026-12-31 23:59:59',1,'2026-08-27 11:01:38'),(3,3,20.00,'2026-08-01 00:00:00','2026-12-31 23:59:59',1,'2026-08-27 11:01:38'),(4,4,30.00,'2026-08-01 00:00:00','2026-12-31 23:59:59',1,'2026-08-27 11:01:38'),(5,5,35.00,'2026-08-01 00:00:00','2026-12-31 23:59:59',1,'2026-08-27 11:01:38'),(6,6,40.00,'2026-08-01 00:00:00','2026-12-31 23:59:59',1,'2026-08-27 11:01:38'),(7,7,20.00,'2026-08-01 00:00:00','2026-12-31 23:59:59',1,'2026-08-27 11:01:38'),(8,8,50.00,'2026-08-01 00:00:00','2026-12-31 23:59:59',1,'2026-08-27 11:01:38');
/*!40000 ALTER TABLE `descontos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `epicfake_jogos`
--

DROP TABLE IF EXISTS `epicfake_jogos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `epicfake_jogos` (
  `id` int(11) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `nome` varchar(180) NOT NULL,
  `descricao` text NOT NULL,
  `plataforma` varchar(100) NOT NULL,
  `genero` varchar(100) NOT NULL,
  `avaliacao` decimal(3,1) NOT NULL DEFAULT 0.0,
  `preco_original` decimal(10,2) NOT NULL DEFAULT 0.00,
  `preco_atual` decimal(10,2) NOT NULL DEFAULT 0.00,
  `desconto` int(11) NOT NULL DEFAULT 0,
  `imagem` varchar(255) NOT NULL,
  `categoria` varchar(30) NOT NULL DEFAULT 'popular',
  `gratuito` tinyint(1) NOT NULL DEFAULT 0,
  `lancamento` tinyint(1) NOT NULL DEFAULT 0,
  `popularidade` int(11) NOT NULL DEFAULT 0,
  `desenvolvedora` varchar(180) NOT NULL,
  `tamanho` varchar(30) NOT NULL,
  `classificacao` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `epicfake_jogos`
--

LOCK TABLES `epicfake_jogos` WRITE;
/*!40000 ALTER TABLE `epicfake_jogos` DISABLE KEYS */;
INSERT INTO `epicfake_jogos` VALUES (1,'cyberpunk-2077','Cyberpunk 2077','Um RPG de ação em mundo aberto ambientado em Night City, uma megalópole obcecada por poder, glamour e modificações corporais.','PC','RPG / Ação',4.7,199.90,89.90,55,'epicfake/assets/img/cyberpunk-2077.svg','promocao',0,0,100,'CD PROJEKT RED','70 GB','18 anos'),(2,'red-dead-redemption-2','Red Dead Redemption 2','Acompanhe Arthur Morgan e a gangue Van der Linde em uma jornada épica pelo coração da América no fim da era dos fora da lei.','PC','Ação / Aventura',4.9,249.90,139.90,44,'epicfake/assets/img/red-dead-redemption-2.svg','promocao',0,0,99,'Rockstar Games','150 GB','18 anos'),(3,'hogwarts-legacy','Hogwarts Legacy','Explore o mundo bruxo do século XIX, descubra uma habilidade ancestral e escreva sua própria história em Hogwarts.','PC','RPG / Aventura',4.6,249.90,99.90,60,'epicfake/assets/img/hogwarts-legacy.svg','promocao',0,0,96,'Avalanche Software','85 GB','12 anos'),(4,'forza-horizon-5','Forza Horizon 5','Dirija pelas paisagens vibrantes do México em uma celebração automotiva repleta de liberdade, velocidade e descobertas.','PC / Xbox','Corrida',4.8,249.90,124.90,50,'epicfake/assets/img/forza-horizon-5.svg','promocao',0,0,95,'Playground Games','110 GB','L'),(5,'elden-ring','Elden Ring','Atravesse as Terras Intermédias em uma fantasia sombria criada em colaboração por Hidetaka Miyazaki e George R. R. Martin.','PC','RPG / Soulslike',4.9,229.90,159.90,30,'epicfake/assets/img/elden-ring.svg','popular',0,0,98,'FromSoftware','60 GB','16 anos'),(6,'baldurs-gate-3','Baldur\'s Gate 3','Reúna seu grupo e retorne aos Reinos Esquecidos em uma aventura de RPG de nova geração, com escolhas que moldam tudo.','PC / Mac','RPG / Estratégia',4.9,199.90,129.90,35,'epicfake/assets/img/baldurs-gate-3.svg','popular',0,0,97,'Larian Studios','150 GB','16 anos'),(7,'the-witcher-3','The Witcher 3: Wild Hunt','Torne-se Geralt de Rívia, um caçador de monstros profissional em busca de uma criança da profecia.','PC','RPG / Aventura',4.8,119.90,39.90,67,'epicfake/assets/img/the-witcher-3.svg','promocao',0,0,94,'CD PROJEKT RED','50 GB','18 anos'),(8,'minecraft','Minecraft','Crie, explore e sobreviva em um universo de blocos onde sua imaginação é a única fronteira.','PC / Console','Sandbox / Sobrevivência',4.8,129.90,99.90,23,'epicfake/assets/img/minecraft.svg','popular',0,0,93,'Mojang Studios','4 GB','L'),(9,'gta-v','Grand Theft Auto V','Viva uma história de crime e ambição em Los Santos, com campanha cinematográfica e mundo online em constante evolução.','PC','Ação / Mundo Aberto',4.7,149.90,74.90,50,'epicfake/assets/img/gta-v.svg','promocao',0,0,92,'Rockstar Games','110 GB','18 anos'),(10,'resident-evil-4','Resident Evil 4','Uma releitura moderna do clássico de terror e sobrevivência que redefiniu o gênero com ação intensa e suspense.','PC','Terror / Ação',4.8,199.90,119.90,40,'epicfake/assets/img/resident-evil-4.svg','promocao',0,0,91,'Capcom','67 GB','18 anos'),(11,'assassins-creed-mirage','Assassin\'s Creed Mirage','Viva a transformação de Basim, um ladrão de rua que se torna um Mestre Assassino na Bagdá do século IX.','PC','Ação / Furtividade',4.4,199.90,89.90,55,'epicfake/assets/img/assassins-creed-mirage.svg','promocao',0,0,86,'Ubisoft Bordeaux','40 GB','16 anos'),(12,'starfield','Starfield','Crie seu personagem e explore a galáxia em uma nova geração de RPG espacial dos criadores de Skyrim.','PC / Xbox','RPG / Ficção científica',4.2,299.90,179.90,40,'epicfake/assets/img/starfield.svg','lancamento',0,1,88,'Bethesda Game Studios','125 GB','16 anos'),(13,'helldivers-2','Helldivers 2','A união faz a força em um tiro em terceira pessoa cooperativo, caótico e repleto de batalhas pela liberdade.','PC / PS5','Ação / Cooperativo',4.7,199.90,149.90,25,'epicfake/assets/img/helldivers-2.svg','lancamento',0,1,89,'Arrowhead Game Studios','100 GB','16 anos'),(14,'hades','Hades','Desafie o deus dos mortos em uma fuga do submundo que mistura ação veloz, narrativa dinâmica e mitologia grega.','PC / Switch','Ação / Roguelike',4.9,99.90,49.90,50,'epicfake/assets/img/hades.svg','promocao',0,0,90,'Supergiant Games','15 GB','12 anos'),(15,'dead-by-daylight','Dead by Daylight','Um terror multiplayer assimétrico em que um assassino enfrenta quatro sobreviventes em uma luta pela vida.','PC / Console','Terror / Multiplayer',4.5,79.90,31.90,60,'epicfake/assets/img/dead-by-daylight.svg','promocao',0,0,85,'Behaviour Interactive','50 GB','18 anos'),(16,'rocket-league','Rocket League','Futebol com carros movidos a foguete, partidas rápidas e habilidade para jogadores de todos os níveis.','PC / Console','Esporte / Multiplayer',4.6,0.00,0.00,0,'epicfake/assets/img/rocket-league.svg','gratuito',1,0,87,'Psyonix','25 GB','L'),(17,'fortnite','Fortnite','Construa, lute e crie em um universo social que reúne battle royale, experiências e eventos ao vivo.','PC / Console / Mobile','Ação / Battle Royale',4.4,0.00,0.00,0,'epicfake/assets/img/fortnite.svg','gratuito',1,0,100,'EpicFake Studios','45 GB','12 anos'),(18,'fall-guys','Fall Guys','Supere pistas absurdas e concorrentes coloridos em uma gincana multiplayer cheia de quedas e risadas.','PC / Console','Party / Multiplayer',4.3,0.00,0.00,0,'epicfake/assets/img/fall-guys.svg','gratuito',1,0,82,'Mediatonic','2 GB','L'),(19,'warframe','Warframe','Desperte como um guerreiro Tenno e domine armaduras biomecânicas em combates cooperativos de ficção científica.','PC / Console','Ação / RPG',4.5,0.00,0.00,0,'epicfake/assets/img/warframe.svg','gratuito',1,0,79,'Digital Extremes','50 GB','16 anos'),(20,'destiny-2','Destiny 2','Defenda a última cidade segura da humanidade e descubra novos poderes em um universo de ação compartilhado.','PC / Console','FPS / MMO',4.3,0.00,0.00,0,'epicfake/assets/img/destiny-2.svg','gratuito',1,0,84,'Bungie','105 GB','14 anos'),(21,'dragon-age-the-veilguard','Dragon Age: The Veilguard','Monte sua equipe de heróis e enfrente uma ameaça ancestral em uma nova aventura de fantasia narrativa.','PC / Console','RPG / Aventura',4.5,299.90,209.90,30,'epicfake/assets/img/dragon-age-the-veilguard.svg','lancamento',0,1,80,'BioWare','100 GB','16 anos'),(22,'black-myth-wukong','Black Myth: Wukong','Encare criaturas lendárias e desvende a verdade por trás de uma lenda chinesa em um RPG de ação cinematográfico.','PC / PS5','RPG / Ação',4.6,249.90,189.90,24,'epicfake/assets/img/black-myth-wukong.svg','lancamento',0,1,91,'Game Science','130 GB','16 anos'),(23,'palworld','Palworld','Colecione criaturas misteriosas, construa sua base e sobreviva em um mundo aberto repleto de possibilidades.','PC / Xbox','Sobrevivência / Aventura',4.3,99.90,69.90,30,'epicfake/assets/img/palworld.svg','popular',0,0,83,'Pocketpair','40 GB','12 anos'),(24,'stardew-valley','Stardew Valley','Transforme um terreno abandonado em um lar, cultive relações e descubra os segredos de um vale acolhedor.','PC / Console / Mobile','Simulação / RPG',4.9,39.90,24.90,38,'epicfake/assets/img/stardew-valley.svg','popular',0,0,88,'ConcernedApe','1 GB','L'),(25,'it-takes-two','It Takes Two','Uma aventura cooperativa criada exclusivamente para dois, com desafios que transformam a relação entre Cody e May.','PC / Console','Aventura / Cooperativo',4.8,149.90,59.90,60,'epicfake/assets/img/it-takes-two.svg','promocao',0,0,89,'Hazelight Studios','50 GB','12 anos'),(26,'doom-eternal','DOOM Eternal','Torne-se o Slayer e abra caminho por hordas demoníacas em uma campanha frenética e brutal.','PC','FPS / Ação',4.8,149.90,44.90,70,'epicfake/assets/img/doom-eternal.svg','promocao',0,0,86,'id Software','80 GB','18 anos'),(27,'civilization-vi','Sid Meier’s Civilization VI','Construa um império capaz de resistir ao teste do tempo em um clássico de estratégia por turnos.','PC / Mac','Estratégia / Simulação',4.5,129.90,19.90,85,'epicfake/assets/img/civilization-vi.svg','promocao',0,0,78,'Firaxis Games','12 GB','L'),(28,'among-us','Among Us','Trabalhe em equipe, mas fique atento: entre os tripulantes há impostores decididos a sabotar a missão.','PC / Mobile','Party / Dedução',4.4,19.90,9.90,50,'epicfake/assets/img/among-us.svg','promocao',0,0,76,'Innersloth','1 GB','L'),(29,'control','Control','Domine poderes sobrenaturais e enfrente uma ameaça inexplicável dentro da enigmática Agência Federal de Controle.','PC','Ação / Ficção científica',4.5,149.90,34.90,77,'epicfake/assets/img/control.svg','promocao',0,0,81,'Remedy Entertainment','42 GB','16 anos'),(30,'alan-wake-2','Alan Wake 2','Dois protagonistas enfrentam uma história de terror psicológico que atravessa a fronteira entre ficção e realidade.','PC / PS5 / Xbox','Terror / Aventura',4.7,249.90,169.90,32,'epicfake/assets/img/alan-wake-2.svg','lancamento',0,1,87,'Remedy Entertainment','90 GB','16 anos');
/*!40000 ALTER TABLE `epicfake_jogos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `epicfake_ofertas`
--

DROP TABLE IF EXISTS `epicfake_ofertas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `epicfake_ofertas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `jogo_id` int(10) unsigned NOT NULL,
  `slug` varchar(150) NOT NULL,
  `plataforma` varchar(60) NOT NULL DEFAULT 'PC',
  `preco_original` decimal(10,2) NOT NULL,
  `preco_atual` decimal(10,2) NOT NULL,
  `desconto` decimal(5,2) NOT NULL DEFAULT 0.00,
  `url` varchar(500) NOT NULL,
  `loja` varchar(40) NOT NULL DEFAULT 'EpicFake',
  `data_atualizacao` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_epicfake_oferta_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `epicfake_ofertas`
--

LOCK TABLES `epicfake_ofertas` WRITE;
/*!40000 ALTER TABLE `epicfake_ofertas` DISABLE KEYS */;
INSERT INTO `epicfake_ofertas` VALUES (1,1,'elden-ring','PC',86.90,86.90,0.00,'/tcc/simulados/epicfake/jogo.php?slug=elden-ring','EpicFake','2026-09-11 15:13:17'),(2,2,'god-of-war-ragnarok','PC',123.90,74.34,40.00,'/tcc/simulados/epicfake/jogo.php?slug=god-of-war-ragnarok','EpicFake','2026-09-11 15:13:17'),(3,3,'the-legend-of-zelda-totk','PC',160.90,128.72,20.00,'/tcc/simulados/epicfake/jogo.php?slug=the-legend-of-zelda-totk','EpicFake','2026-09-11 15:13:17'),(4,4,'baldurs-gate-3','PC',197.90,197.90,0.00,'/tcc/simulados/epicfake/jogo.php?slug=baldurs-gate-3','EpicFake','2026-09-11 15:13:17'),(5,5,'cyberpunk-2077','PC',234.90,234.90,0.00,'/tcc/simulados/epicfake/jogo.php?slug=cyberpunk-2077','EpicFake','2026-09-11 15:13:17'),(6,6,'red-dead-redemption-2','PC',271.90,163.14,40.00,'/tcc/simulados/epicfake/jogo.php?slug=red-dead-redemption-2','EpicFake','2026-09-11 15:13:17'),(7,7,'the-witcher-3','PC',57.90,46.32,20.00,'/tcc/simulados/epicfake/jogo.php?slug=the-witcher-3','EpicFake','2026-09-11 15:13:17'),(8,8,'hollow-knight','PC',94.90,94.90,0.00,'/tcc/simulados/epicfake/jogo.php?slug=hollow-knight','EpicFake','2026-09-11 15:13:17'),(9,9,'hades','PC',131.90,131.90,0.00,'/tcc/simulados/epicfake/jogo.php?slug=hades','EpicFake','2026-09-11 15:13:17'),(10,10,'celeste','PC',168.90,101.34,40.00,'/tcc/simulados/epicfake/jogo.php?slug=celeste','EpicFake','2026-09-11 15:13:17'),(11,11,'stardew-valley','PC',205.90,164.72,20.00,'/tcc/simulados/epicfake/jogo.php?slug=stardew-valley','EpicFake','2026-09-11 15:13:17'),(12,12,'terraria','PC',242.90,242.90,0.00,'/tcc/simulados/epicfake/jogo.php?slug=terraria','EpicFake','2026-09-11 15:13:17'),(13,13,'minecraft','PC',279.90,279.90,0.00,'/tcc/simulados/epicfake/jogo.php?slug=minecraft','EpicFake','2026-09-11 15:13:17'),(14,14,'gta-v','PC',65.90,39.54,40.00,'/tcc/simulados/epicfake/jogo.php?slug=gta-v','EpicFake','2026-09-11 15:13:17'),(15,15,'the-last-of-us-part-ii','PC',102.90,82.32,20.00,'/tcc/simulados/epicfake/jogo.php?slug=the-last-of-us-part-ii','EpicFake','2026-09-11 15:13:17'),(16,16,'ghost-of-tsushima','PC',139.90,139.90,0.00,'/tcc/simulados/epicfake/jogo.php?slug=ghost-of-tsushima','EpicFake','2026-09-11 15:13:17'),(17,17,'horizon-forbidden-west','PC',176.90,176.90,0.00,'/tcc/simulados/epicfake/jogo.php?slug=horizon-forbidden-west','EpicFake','2026-09-11 15:13:17'),(18,18,'spider-man-2','PC',213.90,128.34,40.00,'/tcc/simulados/epicfake/jogo.php?slug=spider-man-2','EpicFake','2026-09-11 15:13:17'),(19,19,'resident-evil-4-remake','PC',250.90,200.72,20.00,'/tcc/simulados/epicfake/jogo.php?slug=resident-evil-4-remake','EpicFake','2026-09-11 15:13:17'),(20,20,'alan-wake-2','PC',287.90,287.90,0.00,'/tcc/simulados/epicfake/jogo.php?slug=alan-wake-2','EpicFake','2026-09-11 15:13:17'),(21,21,'starfield','PC',73.90,73.90,0.00,'/tcc/simulados/epicfake/jogo.php?slug=starfield','EpicFake','2026-09-11 15:13:17'),(22,22,'final-fantasy-xvi','PC',110.90,66.54,40.00,'/tcc/simulados/epicfake/jogo.php?slug=final-fantasy-xvi','EpicFake','2026-09-11 15:13:17'),(23,23,'persona-5-royal','PC',147.90,118.32,20.00,'/tcc/simulados/epicfake/jogo.php?slug=persona-5-royal','EpicFake','2026-09-11 15:13:17'),(24,24,'nier-automata','PC',184.90,184.90,0.00,'/tcc/simulados/epicfake/jogo.php?slug=nier-automata','EpicFake','2026-09-11 15:13:17'),(25,25,'dark-souls-3','PC',221.90,221.90,0.00,'/tcc/simulados/epicfake/jogo.php?slug=dark-souls-3','EpicFake','2026-09-11 15:13:17'),(26,26,'sekiro','PC',258.90,155.34,40.00,'/tcc/simulados/epicfake/jogo.php?slug=sekiro','EpicFake','2026-09-11 15:13:17'),(27,27,'bloodborne','PC',295.90,236.72,20.00,'/tcc/simulados/epicfake/jogo.php?slug=bloodborne','EpicFake','2026-09-11 15:13:17'),(28,28,'cuphead','PC',81.90,81.90,0.00,'/tcc/simulados/epicfake/jogo.php?slug=cuphead','EpicFake','2026-09-11 15:13:17'),(29,29,'ori-and-the-will-of-the-wisps','PC',118.90,118.90,0.00,'/tcc/simulados/epicfake/jogo.php?slug=ori-and-the-will-of-the-wisps','EpicFake','2026-09-11 15:13:17'),(30,30,'it-takes-two','PC',155.90,93.54,40.00,'/tcc/simulados/epicfake/jogo.php?slug=it-takes-two','EpicFake','2026-09-11 15:13:17');
/*!40000 ALTER TABLE `epicfake_ofertas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jogo_plataformas`
--

DROP TABLE IF EXISTS `jogo_plataformas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jogo_plataformas` (
  `jogo_id` int(10) unsigned NOT NULL,
  `plataforma_id` smallint(5) unsigned NOT NULL,
  `disponivel` tinyint(1) NOT NULL DEFAULT 1,
  `data_atualizacao` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`jogo_id`,`plataforma_id`),
  KEY `idx_jp_plataforma` (`plataforma_id`),
  KEY `idx_jp_disponivel` (`disponivel`),
  CONSTRAINT `fk_jp_jogo` FOREIGN KEY (`jogo_id`) REFERENCES `jogos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_jp_plataforma` FOREIGN KEY (`plataforma_id`) REFERENCES `plataformas` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jogo_plataformas`
--

LOCK TABLES `jogo_plataformas` WRITE;
/*!40000 ALTER TABLE `jogo_plataformas` DISABLE KEYS */;
INSERT INTO `jogo_plataformas` VALUES (1,1,1,'2026-08-27 11:01:38'),(1,4,1,'2026-08-27 11:01:38'),(2,1,1,'2026-08-27 11:01:38'),(2,4,1,'2026-08-27 11:01:38'),(3,1,1,'2026-08-27 11:01:38'),(3,4,1,'2026-08-27 11:01:38'),(4,1,1,'2026-08-27 11:01:38'),(4,4,1,'2026-08-27 11:01:38'),(5,1,1,'2026-08-27 11:01:38'),(5,4,1,'2026-08-27 11:01:38'),(6,1,1,'2026-08-27 11:01:38'),(6,4,1,'2026-08-27 11:01:38'),(7,1,1,'2026-08-27 11:01:38'),(8,1,1,'2026-08-27 11:01:38');
/*!40000 ALTER TABLE `jogo_plataformas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jogos`
--

DROP TABLE IF EXISTS `jogos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jogos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(150) NOT NULL,
  `nome` varchar(180) NOT NULL,
  `descricao` text NOT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  `genero` varchar(100) NOT NULL,
  `avaliacao` decimal(3,1) NOT NULL DEFAULT 0.0,
  `categoria` enum('popular','promocao','lancamento','gratuito') NOT NULL DEFAULT 'popular',
  `gratuito` tinyint(1) NOT NULL DEFAULT 0,
  `disponivel` tinyint(1) NOT NULL DEFAULT 1,
  `desenvolvedora` varchar(180) DEFAULT NULL,
  `tamanho` varchar(30) DEFAULT NULL,
  `classificacao` varchar(30) DEFAULT NULL,
  `data_atualizacao` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_jogos_slug` (`slug`),
  KEY `idx_jogos_nome` (`nome`),
  KEY `idx_jogos_genero` (`genero`),
  KEY `idx_jogos_disponivel` (`disponivel`),
  KEY `idx_jogos_atualizacao` (`data_atualizacao`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jogos`
--

LOCK TABLES `jogos` WRITE;
/*!40000 ALTER TABLE `jogos` DISABLE KEYS */;
INSERT INTO `jogos` VALUES (1,'cyberpunk-2077','Cyberpunk 2077','RPG de ação em mundo aberto ambientado em Night City.','epicfake/assets/img/cyberpunk-2077.svg','RPG / Ação',9.1,'popular',0,1,'CD Projekt Red','70 GB','18','2026-08-27 11:01:38'),(2,'the-witcher-3','The Witcher 3: Wild Hunt','Uma aventura épica de fantasia em um mundo aberto repleto de escolhas.','epicfake/assets/img/the-witcher-3.svg','RPG / Fantasia',9.5,'popular',0,1,'CD Projekt Red','50 GB','16','2026-08-27 11:01:38'),(3,'baldurs-gate-3','Baldur\'s Gate 3','RPG narrativo baseado em Dungeons & Dragons com liberdade de decisões.','epicfake/assets/img/baldurs-gate-3.svg','RPG',9.7,'popular',0,1,'Larian Studios','150 GB','16','2026-08-27 11:01:38'),(4,'elden-ring','Elden Ring','Uma jornada de fantasia sombria em um vasto mundo aberto.','epicfake/assets/img/elden-ring.svg','RPG / Ação',9.4,'popular',0,1,'FromSoftware','60 GB','16','2026-08-27 11:01:38'),(5,'hogwarts-legacy','Hogwarts Legacy','Explore Hogwarts e descubra sua própria história no mundo bruxo.','epicfake/assets/img/hogwarts-legacy.svg','Ação / Aventura',8.8,'lancamento',0,1,'Avalanche Software','85 GB','12','2026-08-27 11:01:38'),(6,'resident-evil-4','Resident Evil 4','Remake de uma missão de resgate em uma vila tomada pelo terror.','epicfake/assets/img/resident-evil-4.svg','Terror / Ação',9.0,'promocao',0,1,'Capcom','67 GB','18','2026-08-27 11:01:38'),(7,'stardew-valley','Stardew Valley','Construa uma nova vida em uma fazenda cercada por uma comunidade acolhedora.','epicfake/assets/img/stardew-valley.svg','Simulação / RPG',9.2,'popular',0,1,'ConcernedApe','1 GB','L','2026-08-27 11:01:38'),(8,'among-us','Among Us','Descubra o impostor em partidas cooperativas de investigação e estratégia.','epicfake/assets/img/among-us.svg','Ação / Social',8.1,'popular',0,1,'Innersloth','250 MB','L','2026-08-27 11:01:38');
/*!40000 ALTER TABLE `jogos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `links_jogos`
--

DROP TABLE IF EXISTS `links_jogos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `links_jogos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `jogo_id` int(10) unsigned NOT NULL,
  `plataforma_id` smallint(5) unsigned DEFAULT NULL,
  `url` varchar(500) NOT NULL,
  `tipo` enum('produto','oferta','suporte') NOT NULL DEFAULT 'produto',
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `data_atualizacao` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_links_url` (`url`),
  KEY `idx_links_jogo` (`jogo_id`),
  KEY `idx_links_plataforma` (`plataforma_id`),
  KEY `idx_links_ativo` (`ativo`),
  CONSTRAINT `fk_links_jogo` FOREIGN KEY (`jogo_id`) REFERENCES `jogos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_links_plataforma` FOREIGN KEY (`plataforma_id`) REFERENCES `plataformas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `links_jogos`
--

LOCK TABLES `links_jogos` WRITE;
/*!40000 ALTER TABLE `links_jogos` DISABLE KEYS */;
INSERT INTO `links_jogos` VALUES (1,1,1,'epicfake/jogo.php?slug=cyberpunk-2077','produto',1,'2026-08-27 11:01:38'),(2,2,1,'epicfake/jogo.php?slug=the-witcher-3','produto',1,'2026-08-27 11:01:38'),(3,3,1,'epicfake/jogo.php?slug=baldurs-gate-3','produto',1,'2026-08-27 11:01:38'),(4,4,1,'epicfake/jogo.php?slug=elden-ring','produto',1,'2026-08-27 11:01:38'),(5,5,1,'epicfake/jogo.php?slug=hogwarts-legacy','produto',1,'2026-08-27 11:01:38'),(6,6,1,'epicfake/jogo.php?slug=resident-evil-4','produto',1,'2026-08-27 11:01:38'),(7,7,1,'epicfake/jogo.php?slug=stardew-valley','produto',1,'2026-08-27 11:01:38'),(8,8,1,'epicfake/jogo.php?slug=among-us','produto',1,'2026-08-27 11:01:38');
/*!40000 ALTER TABLE `links_jogos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plataformas`
--

DROP TABLE IF EXISTS `plataformas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plataformas` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(80) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_plataformas_nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plataformas`
--

LOCK TABLES `plataformas` WRITE;
/*!40000 ALTER TABLE `plataformas` DISABLE KEYS */;
INSERT INTO `plataformas` VALUES (3,'Linux'),(2,'Mac'),(1,'PC'),(5,'PlayStation 5'),(4,'Steam Deck'),(6,'Xbox Series X|S');
/*!40000 ALTER TABLE `plataformas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `precos`
--

DROP TABLE IF EXISTS `precos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `precos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `jogo_id` int(10) unsigned NOT NULL,
  `plataforma_id` smallint(5) unsigned NOT NULL,
  `preco_original` decimal(10,2) NOT NULL DEFAULT 0.00,
  `preco_atual` decimal(10,2) NOT NULL DEFAULT 0.00,
  `moeda` char(3) NOT NULL DEFAULT 'BRL',
  `disponibilidade` enum('disponivel','indisponivel','pre_venda') NOT NULL DEFAULT 'disponivel',
  `data_atualizacao` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_precos_jogo_plataforma` (`jogo_id`,`plataforma_id`),
  KEY `idx_precos_jogo` (`jogo_id`),
  KEY `idx_precos_plataforma` (`plataforma_id`),
  KEY `idx_precos_atual` (`preco_atual`),
  KEY `idx_precos_disponibilidade` (`disponibilidade`),
  KEY `idx_precos_atualizacao` (`data_atualizacao`),
  CONSTRAINT `fk_precos_jogo` FOREIGN KEY (`jogo_id`) REFERENCES `jogos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_precos_plataforma` FOREIGN KEY (`plataforma_id`) REFERENCES `plataformas` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `precos`
--

LOCK TABLES `precos` WRITE;
/*!40000 ALTER TABLE `precos` DISABLE KEYS */;
INSERT INTO `precos` VALUES (1,1,1,249.90,124.95,'BRL','disponivel','2026-08-27 11:01:38'),(2,2,1,179.90,89.95,'BRL','disponivel','2026-08-27 11:01:38'),(3,3,1,249.99,199.99,'BRL','disponivel','2026-08-27 11:01:38'),(4,4,1,279.90,195.93,'BRL','disponivel','2026-08-27 11:01:38'),(5,5,1,249.99,162.49,'BRL','disponivel','2026-08-27 11:01:38'),(6,6,1,219.90,131.94,'BRL','disponivel','2026-08-27 11:01:38'),(7,7,1,29.99,23.99,'BRL','disponivel','2026-08-27 11:01:38'),(8,8,1,24.90,12.45,'BRL','disponivel','2026-08-27 11:01:38');
/*!40000 ALTER TABLE `precos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `vw_catalogo`
--

DROP TABLE IF EXISTS `vw_catalogo`;
/*!50001 DROP VIEW IF EXISTS `vw_catalogo`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `vw_catalogo` AS SELECT 
 1 AS `id`,
 1 AS `slug`,
 1 AS `nome`,
 1 AS `descricao`,
 1 AS `imagem`,
 1 AS `genero`,
 1 AS `avaliacao`,
 1 AS `plataforma`,
 1 AS `preco_original`,
 1 AS `preco_atual`,
 1 AS `desconto`,
 1 AS `disponibilidade`,
 1 AS `disponivel`,
 1 AS `url`,
 1 AS `data_atualizacao`*/;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `vw_catalogo`
--

/*!50001 DROP VIEW IF EXISTS `vw_catalogo`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_catalogo` AS select `j`.`id` AS `id`,`j`.`slug` AS `slug`,`j`.`nome` AS `nome`,`j`.`descricao` AS `descricao`,`j`.`imagem` AS `imagem`,`j`.`genero` AS `genero`,`j`.`avaliacao` AS `avaliacao`,`p`.`nome` AS `plataforma`,`pr`.`preco_original` AS `preco_original`,`pr`.`preco_atual` AS `preco_atual`,coalesce(`d`.`percentual`,0) AS `desconto`,`pr`.`disponibilidade` AS `disponibilidade`,`j`.`disponivel` AS `disponivel`,`l`.`url` AS `url`,`j`.`data_atualizacao` AS `data_atualizacao` from ((((`jogos` `j` join `precos` `pr` on(`pr`.`jogo_id` = `j`.`id`)) join `plataformas` `p` on(`p`.`id` = `pr`.`plataforma_id`)) left join `descontos` `d` on(`d`.`preco_id` = `pr`.`id`)) left join `links_jogos` `l` on(`l`.`jogo_id` = `j`.`id` and `l`.`plataforma_id` = `pr`.`plataforma_id` and `l`.`tipo` = 'produto' and `l`.`ativo` = 1)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-05 11:38:26
-- MySQL dump 10.13  Distrib 8.0.38, for Win64 (x86_64)
--

SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS = 1;

-- ============================================================
-- BANCO: fake_steam
-- ============================================================

CREATE DATABASE IF NOT EXISTS `fake_steam`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `fake_steam`;

SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS = 0;

-- Host: 127.0.0.1    Database: fake_steam
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

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
-- Table structure for table `descontos`
--

DROP TABLE IF EXISTS `descontos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `descontos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `preco_id` int(10) unsigned NOT NULL,
  `percentual` decimal(5,2) NOT NULL DEFAULT 0.00,
  `inicio` datetime DEFAULT NULL,
  `fim` datetime DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `data_atualizacao` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_descontos_preco` (`preco_id`),
  KEY `idx_descontos_ativo` (`ativo`),
  KEY `idx_descontos_percentual` (`percentual`),
  KEY `idx_descontos_periodo` (`inicio`,`fim`),
  CONSTRAINT `fk_descontos_preco` FOREIGN KEY (`preco_id`) REFERENCES `precos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `descontos`
--

LOCK TABLES `descontos` WRITE;
/*!40000 ALTER TABLE `descontos` DISABLE KEYS */;
/*!40000 ALTER TABLE `descontos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jogo_plataformas`
--

DROP TABLE IF EXISTS `jogo_plataformas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jogo_plataformas` (
  `jogo_id` int(10) unsigned NOT NULL,
  `plataforma_id` smallint(5) unsigned NOT NULL,
  `disponivel` tinyint(1) NOT NULL DEFAULT 1,
  `data_atualizacao` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`jogo_id`,`plataforma_id`),
  KEY `idx_jp_plataforma` (`plataforma_id`),
  KEY `idx_jp_disponivel` (`disponivel`),
  CONSTRAINT `fk_jp_jogo` FOREIGN KEY (`jogo_id`) REFERENCES `jogos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_jp_plataforma` FOREIGN KEY (`plataforma_id`) REFERENCES `plataformas` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jogo_plataformas`
--

LOCK TABLES `jogo_plataformas` WRITE;
/*!40000 ALTER TABLE `jogo_plataformas` DISABLE KEYS */;
/*!40000 ALTER TABLE `jogo_plataformas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jogos`
--

DROP TABLE IF EXISTS `jogos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jogos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(150) NOT NULL,
  `nome` varchar(180) NOT NULL,
  `descricao` text NOT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  `genero` varchar(100) NOT NULL,
  `avaliacao` decimal(3,1) NOT NULL DEFAULT 0.0,
  `categoria` enum('popular','promocao','lancamento','gratuito') NOT NULL DEFAULT 'popular',
  `gratuito` tinyint(1) NOT NULL DEFAULT 0,
  `disponivel` tinyint(1) NOT NULL DEFAULT 1,
  `desenvolvedora` varchar(180) DEFAULT NULL,
  `tamanho` varchar(30) DEFAULT NULL,
  `classificacao` varchar(30) DEFAULT NULL,
  `data_atualizacao` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_jogos_slug` (`slug`),
  KEY `idx_jogos_nome` (`nome`),
  KEY `idx_jogos_genero` (`genero`),
  KEY `idx_jogos_disponivel` (`disponivel`),
  KEY `idx_jogos_atualizacao` (`data_atualizacao`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jogos`
--

LOCK TABLES `jogos` WRITE;
/*!40000 ALTER TABLE `jogos` DISABLE KEYS */;
/*!40000 ALTER TABLE `jogos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `links_jogos`
--

DROP TABLE IF EXISTS `links_jogos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `links_jogos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `jogo_id` int(10) unsigned NOT NULL,
  `plataforma_id` smallint(5) unsigned DEFAULT NULL,
  `url` varchar(500) NOT NULL,
  `tipo` enum('produto','oferta','suporte') NOT NULL DEFAULT 'produto',
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `data_atualizacao` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_links_url` (`url`),
  KEY `idx_links_jogo` (`jogo_id`),
  KEY `idx_links_plataforma` (`plataforma_id`),
  KEY `idx_links_ativo` (`ativo`),
  CONSTRAINT `fk_links_jogo` FOREIGN KEY (`jogo_id`) REFERENCES `jogos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_links_plataforma` FOREIGN KEY (`plataforma_id`) REFERENCES `plataformas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `links_jogos`
--

LOCK TABLES `links_jogos` WRITE;
/*!40000 ALTER TABLE `links_jogos` DISABLE KEYS */;
/*!40000 ALTER TABLE `links_jogos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plataformas`
--

DROP TABLE IF EXISTS `plataformas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plataformas` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(80) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_plataformas_nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plataformas`
--

LOCK TABLES `plataformas` WRITE;
/*!40000 ALTER TABLE `plataformas` DISABLE KEYS */;
INSERT INTO `plataformas` VALUES (3,'Linux'),(2,'Mac'),(1,'PC'),(5,'PlayStation 5'),(4,'Steam Deck'),(6,'Xbox Series X|S');
/*!40000 ALTER TABLE `plataformas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `precos`
--

DROP TABLE IF EXISTS `precos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `precos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `jogo_id` int(10) unsigned NOT NULL,
  `plataforma_id` smallint(5) unsigned NOT NULL,
  `preco_original` decimal(10,2) NOT NULL DEFAULT 0.00,
  `preco_atual` decimal(10,2) NOT NULL DEFAULT 0.00,
  `moeda` char(3) NOT NULL DEFAULT 'BRL',
  `disponibilidade` enum('disponivel','indisponivel','pre_venda') NOT NULL DEFAULT 'disponivel',
  `data_atualizacao` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_precos_jogo_plataforma` (`jogo_id`,`plataforma_id`),
  KEY `idx_precos_jogo` (`jogo_id`),
  KEY `idx_precos_plataforma` (`plataforma_id`),
  KEY `idx_precos_atual` (`preco_atual`),
  KEY `idx_precos_disponibilidade` (`disponibilidade`),
  KEY `idx_precos_atualizacao` (`data_atualizacao`),
  CONSTRAINT `fk_precos_jogo` FOREIGN KEY (`jogo_id`) REFERENCES `jogos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_precos_plataforma` FOREIGN KEY (`plataforma_id`) REFERENCES `plataformas` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `precos`
--

LOCK TABLES `precos` WRITE;
/*!40000 ALTER TABLE `precos` DISABLE KEYS */;
/*!40000 ALTER TABLE `precos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `steamfake_jogos`
--

DROP TABLE IF EXISTS `steamfake_jogos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `steamfake_jogos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) NOT NULL,
  `slug` varchar(160) NOT NULL,
  `descricao` text NOT NULL,
  `imagem` varchar(255) NOT NULL,
  `plataforma` varchar(60) NOT NULL DEFAULT 'PC',
  `genero` varchar(100) NOT NULL,
  `avaliacao` decimal(2,1) NOT NULL DEFAULT 0.0,
  `preco_original` decimal(10,2) NOT NULL,
  `preco_atual` decimal(10,2) NOT NULL,
  `desconto` decimal(5,2) NOT NULL DEFAULT 0.00,
  `promocao_status` varchar(80) NOT NULL DEFAULT 'Preço regular',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_steamfake_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `steamfake_jogos`
--

LOCK TABLES `steamfake_jogos` WRITE;
/*!40000 ALTER TABLE `steamfake_jogos` DISABLE KEYS */;
INSERT INTO `steamfake_jogos` VALUES (1,'Cyberpunk 2077','cyberpunk-2077','RPG de ação em mundo aberto ambientado em Night City.','assets/img/capa-padrao.svg','PC','RPG / Ação',4.7,199.90,99.95,50.00,'Oferta por tempo limitado','2026-08-27 14:14:38','2026-08-27 14:14:38'),(2,'Red Dead Redemption 2','red-dead-redemption-2','Uma jornada épica pelo Velho Oeste americano.','assets/img/capa-padrao.svg','PC','Ação / Aventura',4.9,249.90,124.95,50.00,'Oferta por tempo limitado','2026-08-27 14:14:38','2026-08-27 14:14:38'),(3,'Hogwarts Legacy','hogwarts-legacy','Explore Hogwarts no século XIX e aprenda feitiços.','assets/img/capa-padrao.svg','PC','RPG / Aventura',4.6,249.90,124.95,50.00,'Oferta por tempo limitado','2026-08-27 14:14:38','2026-08-27 14:14:38'),(4,'Forza Horizon 5','forza-horizon-5','Corridas pelas paisagens vibrantes do México.','assets/img/capa-padrao.svg','PC','Corrida',4.8,249.90,149.94,40.00,'Oferta ativa','2026-08-27 14:14:38','2026-08-27 14:14:38'),(5,'Elden Ring','elden-ring','Fantasia sombria com exploração livre e combates intensos.','assets/img/capa-padrao.svg','PC','RPG / Soulslike',4.9,229.90,160.93,30.00,'Oferta ativa','2026-08-27 14:14:38','2026-08-27 14:14:38'),(6,'Baldur’s Gate 3','baldurs-gate-3','RPG baseado em escolhas, dados e consequências.','assets/img/capa-padrao.svg','PC','RPG / Estratégia',4.9,199.90,139.93,30.00,'Oferta ativa','2026-08-27 14:14:38','2026-08-27 14:14:38'),(7,'Minecraft','minecraft','Construa, explore e sobreviva em um universo de blocos.','assets/img/capa-padrao.svg','PC','Sandbox / Aventura',4.8,119.90,119.90,0.00,'Preço regular','2026-08-27 14:14:38','2026-08-27 14:14:38'),(8,'The Witcher 3: Wild Hunt','the-witcher-3-wild-hunt','Geralt de Rívia rastreia monstros em um continente em guerra.','assets/img/capa-padrao.svg','PC','RPG / Ação',4.9,149.90,44.97,70.00,'Oferta relâmpago','2026-08-27 14:14:38','2026-08-27 14:14:38'),(9,'Grand Theft Auto V','grand-theft-auto-v','Três criminosos se unem em golpes na cidade de Los Santos.','assets/img/capa-padrao.svg','PC','Ação / Mundo aberto',4.7,99.90,24.97,75.00,'Oferta relâmpago','2026-08-27 14:14:38','2026-08-27 14:14:38'),(10,'Hades','hades','Desafie o deus dos mortos em uma fuga pelo submundo.','assets/img/capa-padrao.svg','PC','Roguelike / Ação',4.9,89.90,44.95,50.00,'Oferta ativa','2026-08-27 14:14:38','2026-08-27 14:14:38'),(11,'Hollow Knight','hollow-knight','Desbrave um reino subterrâneo repleto de segredos.','assets/img/capa-padrao.svg','PC','Metroidvania',4.8,57.90,28.95,50.00,'Oferta ativa','2026-08-27 14:14:38','2026-08-27 14:14:38'),(12,'Dead Cells','dead-cells','Roguelite de ação com combate veloz e exploração ramificada.','assets/img/capa-padrao.svg','PC','Roguelite / Ação',4.7,59.90,35.94,40.00,'Oferta ativa','2026-08-27 14:14:38','2026-08-27 14:14:38'),(13,'Stardew Valley','stardew-valley','Cultive sua fazenda, faça amizades e descubra o vale.','assets/img/capa-padrao.svg','PC','Simulação / RPG',4.9,39.90,19.95,50.00,'Oferta ativa','2026-08-27 14:14:38','2026-08-27 14:14:38'),(14,'Resident Evil 4','resident-evil-4','Leon enfrenta uma missão de resgate em uma vila isolada.','assets/img/capa-padrao.svg','PC','Terror / Ação',4.8,249.90,174.93,30.00,'Oferta ativa','2026-08-27 14:14:38','2026-08-27 14:14:38'),(15,'Street Fighter 6','street-fighter-6','A nova geração dos jogos de luta.','assets/img/capa-padrao.svg','PC','Luta',4.6,249.90,174.93,30.00,'Oferta ativa','2026-08-27 14:14:38','2026-08-27 14:14:38'),(16,'Bioshock Infinite','bioshock-infinite','Uma aventura de ficção científica na cidade aérea de Columbia.','assets/img/capa-padrao.svg','PC','FPS / Narrativa',4.6,79.90,15.98,80.00,'Oferta relâmpago','2026-08-27 14:14:38','2026-08-27 14:14:38'),(17,'Portal 2','portal-2','Resolva quebra-cabeças com portais e muita ironia.','assets/img/capa-padrao.svg','PC','Puzzle / Cooperação',4.9,36.90,7.38,80.00,'Oferta relâmpago','2026-08-27 14:14:38','2026-08-27 14:14:38'),(18,'The Sims 4','the-sims-4','Crie personagens, construa casas e conte novas histórias.','assets/img/capa-padrao.svg','PC','Simulação',4.3,99.90,49.95,50.00,'Oferta ativa','2026-08-27 14:14:38','2026-08-27 14:14:38'),(19,'Terraria','terraria','Cave, lute, explore e construa em um mundo 2D.','assets/img/capa-padrao.svg','PC','Sandbox / Aventura',4.8,39.90,19.95,50.00,'Oferta ativa','2026-08-27 14:14:38','2026-08-27 14:14:38'),(20,'Sekiro: Shadows Die Twice','sekiro-shadows-die-twice','Domine a espada de um shinobi no Japão Sengoku.','assets/img/capa-padrao.svg','PC','Ação / Soulslike',4.8,229.90,160.93,30.00,'Oferta ativa','2026-08-27 14:14:38','2026-08-27 14:14:38'),(21,'Death Stranding','death-stranding','Reconecte uma América fragmentada.','assets/img/capa-padrao.svg','PC','Ação / Aventura',4.4,199.90,69.96,65.00,'Oferta relâmpago','2026-08-27 14:14:38','2026-08-27 14:14:38'),(22,'No Man’s Sky','no-mans-sky','Explore um universo praticamente infinito.','assets/img/capa-padrao.svg','PC','Exploração / Sobrevivência',4.5,199.90,99.95,50.00,'Oferta ativa','2026-08-27 14:14:38','2026-08-27 14:14:38'),(23,'Sea of Thieves','sea-of-thieves','Aventuras piratas em alto-mar com sua tripulação.','assets/img/capa-padrao.svg','PC','Ação / Multiplayer',4.4,149.90,74.95,50.00,'Oferta ativa','2026-08-27 14:14:38','2026-08-27 14:14:38'),(24,'Cuphead','cuphead','Chefes desafiadores em animação clássica.','assets/img/capa-padrao.svg','PC','Ação / Plataforma',4.7,79.90,39.95,50.00,'Oferta ativa','2026-08-27 14:14:38','2026-08-27 14:14:38'),(25,'Celeste','celeste','Ajude Madeline a escalar uma montanha.','assets/img/capa-padrao.svg','PC','Plataforma / Narrativa',4.9,59.90,29.95,50.00,'Oferta ativa','2026-08-27 14:14:38','2026-08-27 14:14:38'),(26,'Control','control','Habilidades sobrenaturais em uma agência secreta.','assets/img/capa-padrao.svg','PC','Ação / Ficção científica',4.5,149.90,44.97,70.00,'Oferta relâmpago','2026-08-27 14:14:38','2026-08-27 14:14:38'),(27,'Doom Eternal','doom-eternal','Destrua hordas demoníacas em combate explosivo.','assets/img/capa-padrao.svg','PC','FPS / Ação',4.8,199.90,99.95,50.00,'Oferta ativa','2026-08-27 14:14:38','2026-08-27 14:14:38'),(28,'Final Fantasy VII Remake','final-fantasy-vii-remake','A releitura visualmente impressionante de Midgar.','assets/img/capa-padrao.svg','PC','RPG / Ação',4.6,349.90,244.93,30.00,'Oferta ativa','2026-08-27 14:14:38','2026-08-27 14:14:38'),(29,'Batalha Estelar: Origens','batalha-estelar-origens','Comande uma frota rebelde em uma campanha tática.','assets/img/capa-padrao.svg','PC','Estratégia / Sci-Fi',4.2,79.90,39.95,50.00,'Oferta ativa','2026-08-27 14:14:38','2026-08-27 14:14:38'),(30,'Neon Drift Racing','neon-drift-racing','Corridas arcade em pistas futuristas.','assets/img/capa-padrao.svg','PC','Corrida / Arcade',4.1,69.90,34.95,50.00,'Oferta ativa','2026-08-27 14:14:38','2026-08-27 14:14:38'),(31,'Alan Wake 2','alan-wake-2','Dois protagonistas enfrentam uma história de terror psicológico que atravessa a fronteira entre ficção e realidade.','assets/img/capa-padrao.svg','PC','Terror / Aventura',4.7,249.90,174.93,30.00,'Oferta ativa','2026-08-27 14:14:38','2026-08-27 14:14:38');
/*!40000 ALTER TABLE `steamfake_jogos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `steamfake_ofertas`
--

DROP TABLE IF EXISTS `steamfake_ofertas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `steamfake_ofertas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `jogo_id` int(10) unsigned NOT NULL,
  `slug` varchar(150) NOT NULL,
  `plataforma` varchar(60) NOT NULL DEFAULT 'PC',
  `preco_original` decimal(10,2) NOT NULL,
  `preco_atual` decimal(10,2) NOT NULL,
  `desconto` decimal(5,2) NOT NULL DEFAULT 0.00,
  `url` varchar(500) NOT NULL,
  `loja` varchar(40) NOT NULL DEFAULT 'SteamFake',
  `data_atualizacao` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_steamfake_oferta_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `steamfake_ofertas`
--

LOCK TABLES `steamfake_ofertas` WRITE;
/*!40000 ALTER TABLE `steamfake_ofertas` DISABLE KEYS */;
INSERT INTO `steamfake_ofertas` VALUES (1,1,'elden-ring','PC',86.90,73.87,14.99,'/tcc/simulados/steamfake/produto.php?slug=elden-ring','SteamFake','2026-09-11 15:13:38'),(2,2,'god-of-war-ragnarok','PC',123.90,123.90,0.00,'/tcc/simulados/steamfake/produto.php?slug=god-of-war-ragnarok','SteamFake','2026-09-11 15:13:38'),(3,3,'the-legend-of-zelda-totk','PC',160.90,160.90,0.00,'/tcc/simulados/steamfake/produto.php?slug=the-legend-of-zelda-totk','SteamFake','2026-09-11 15:13:38'),(4,4,'baldurs-gate-3','PC',197.90,138.53,30.00,'/tcc/simulados/steamfake/produto.php?slug=baldurs-gate-3','SteamFake','2026-09-11 15:13:38'),(5,5,'cyberpunk-2077','PC',234.90,199.67,15.00,'/tcc/simulados/steamfake/produto.php?slug=cyberpunk-2077','SteamFake','2026-09-11 15:13:38'),(6,6,'red-dead-redemption-2','PC',271.90,271.90,0.00,'/tcc/simulados/steamfake/produto.php?slug=red-dead-redemption-2','SteamFake','2026-09-11 15:13:38'),(7,7,'the-witcher-3','PC',57.90,57.90,0.00,'/tcc/simulados/steamfake/produto.php?slug=the-witcher-3','SteamFake','2026-09-11 15:13:38'),(8,8,'hollow-knight','PC',94.90,66.43,30.00,'/tcc/simulados/steamfake/produto.php?slug=hollow-knight','SteamFake','2026-09-11 15:13:38'),(9,9,'hades','PC',131.90,112.12,15.00,'/tcc/simulados/steamfake/produto.php?slug=hades','SteamFake','2026-09-11 15:13:38'),(10,10,'celeste','PC',168.90,168.90,0.00,'/tcc/simulados/steamfake/produto.php?slug=celeste','SteamFake','2026-09-11 15:13:38'),(11,11,'stardew-valley','PC',205.90,205.90,0.00,'/tcc/simulados/steamfake/produto.php?slug=stardew-valley','SteamFake','2026-09-11 15:13:38'),(12,12,'terraria','PC',242.90,170.03,30.00,'/tcc/simulados/steamfake/produto.php?slug=terraria','SteamFake','2026-09-11 15:13:38'),(13,13,'minecraft','PC',279.90,237.92,15.00,'/tcc/simulados/steamfake/produto.php?slug=minecraft','SteamFake','2026-09-11 15:13:38'),(14,14,'gta-v','PC',65.90,65.90,0.00,'/tcc/simulados/steamfake/produto.php?slug=gta-v','SteamFake','2026-09-11 15:13:38'),(15,15,'the-last-of-us-part-ii','PC',102.90,102.90,0.00,'/tcc/simulados/steamfake/produto.php?slug=the-last-of-us-part-ii','SteamFake','2026-09-11 15:13:38'),(16,16,'ghost-of-tsushima','PC',139.90,97.93,30.00,'/tcc/simulados/steamfake/produto.php?slug=ghost-of-tsushima','SteamFake','2026-09-11 15:13:38'),(17,17,'horizon-forbidden-west','PC',176.90,150.37,15.00,'/tcc/simulados/steamfake/produto.php?slug=horizon-forbidden-west','SteamFake','2026-09-11 15:13:38'),(18,18,'spider-man-2','PC',213.90,213.90,0.00,'/tcc/simulados/steamfake/produto.php?slug=spider-man-2','SteamFake','2026-09-11 15:13:38'),(19,19,'resident-evil-4-remake','PC',250.90,250.90,0.00,'/tcc/simulados/steamfake/produto.php?slug=resident-evil-4-remake','SteamFake','2026-09-11 15:13:38'),(20,20,'alan-wake-2','PC',287.90,201.53,30.00,'/tcc/simulados/steamfake/produto.php?slug=alan-wake-2','SteamFake','2026-09-11 15:13:38'),(21,21,'starfield','PC',73.90,62.82,14.99,'/tcc/simulados/steamfake/produto.php?slug=starfield','SteamFake','2026-09-11 15:13:38'),(22,22,'final-fantasy-xvi','PC',110.90,110.90,0.00,'/tcc/simulados/steamfake/produto.php?slug=final-fantasy-xvi','SteamFake','2026-09-11 15:13:38'),(23,23,'persona-5-royal','PC',147.90,147.90,0.00,'/tcc/simulados/steamfake/produto.php?slug=persona-5-royal','SteamFake','2026-09-11 15:13:38'),(24,24,'nier-automata','PC',184.90,129.43,30.00,'/tcc/simulados/steamfake/produto.php?slug=nier-automata','SteamFake','2026-09-11 15:13:38'),(25,25,'dark-souls-3','PC',221.90,188.62,15.00,'/tcc/simulados/steamfake/produto.php?slug=dark-souls-3','SteamFake','2026-09-11 15:13:38'),(26,26,'sekiro','PC',258.90,258.90,0.00,'/tcc/simulados/steamfake/produto.php?slug=sekiro','SteamFake','2026-09-11 15:13:38'),(27,27,'bloodborne','PC',295.90,295.90,0.00,'/tcc/simulados/steamfake/produto.php?slug=bloodborne','SteamFake','2026-09-11 15:13:38'),(28,28,'cuphead','PC',81.90,57.33,30.00,'/tcc/simulados/steamfake/produto.php?slug=cuphead','SteamFake','2026-09-11 15:13:38'),(29,29,'ori-and-the-will-of-the-wisps','PC',118.90,101.07,15.00,'/tcc/simulados/steamfake/produto.php?slug=ori-and-the-will-of-the-wisps','SteamFake','2026-09-11 15:13:38'),(30,30,'it-takes-two','PC',155.90,155.90,0.00,'/tcc/simulados/steamfake/produto.php?slug=it-takes-two','SteamFake','2026-09-11 15:13:38');
/*!40000 ALTER TABLE `steamfake_ofertas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `vw_catalogo`
--

DROP TABLE IF EXISTS `vw_catalogo`;
/*!50001 DROP VIEW IF EXISTS `vw_catalogo`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `vw_catalogo` AS SELECT 
 1 AS `id`,
 1 AS `slug`,
 1 AS `nome`,
 1 AS `descricao`,
 1 AS `imagem`,
 1 AS `genero`,
 1 AS `avaliacao`,
 1 AS `plataforma`,
 1 AS `preco_original`,
 1 AS `preco_atual`,
 1 AS `desconto`,
 1 AS `disponibilidade`,
 1 AS `disponivel`,
 1 AS `url`,
 1 AS `data_atualizacao`*/;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `vw_catalogo`
--

/*!50001 DROP VIEW IF EXISTS `vw_catalogo`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_catalogo` AS select `j`.`id` AS `id`,`j`.`slug` AS `slug`,`j`.`nome` AS `nome`,`j`.`descricao` AS `descricao`,`j`.`imagem` AS `imagem`,`j`.`genero` AS `genero`,`j`.`avaliacao` AS `avaliacao`,`p`.`nome` AS `plataforma`,`pr`.`preco_original` AS `preco_original`,`pr`.`preco_atual` AS `preco_atual`,coalesce(`d`.`percentual`,0) AS `desconto`,`pr`.`disponibilidade` AS `disponibilidade`,`j`.`disponivel` AS `disponivel`,`l`.`url` AS `url`,`j`.`data_atualizacao` AS `data_atualizacao` from ((((`jogos` `j` join `precos` `pr` on(`pr`.`jogo_id` = `j`.`id`)) join `plataformas` `p` on(`p`.`id` = `pr`.`plataforma_id`)) left join `descontos` `d` on(`d`.`preco_id` = `pr`.`id`)) left join `links_jogos` `l` on(`l`.`jogo_id` = `j`.`id` and `l`.`plataforma_id` = `pr`.`plataforma_id` and `l`.`tipo` = 'produto' and `l`.`ativo` = 1)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-05 11:38:26
-- MySQL dump 10.13  Distrib 8.0.38, for Win64 (x86_64)
--

SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS = 1;

-- ============================================================
-- BANCO: fake_gog
-- ============================================================

CREATE DATABASE IF NOT EXISTS `fake_gog`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `fake_gog`;

SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS = 0;

-- Host: 127.0.0.1    Database: fake_gog
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

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
-- Table structure for table `descontos`
--

DROP TABLE IF EXISTS `descontos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `descontos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `preco_id` int(10) unsigned NOT NULL,
  `percentual` decimal(5,2) NOT NULL DEFAULT 0.00,
  `inicio` datetime DEFAULT NULL,
  `fim` datetime DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `data_atualizacao` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_descontos_preco` (`preco_id`),
  KEY `idx_descontos_ativo` (`ativo`),
  KEY `idx_descontos_percentual` (`percentual`),
  KEY `idx_descontos_periodo` (`inicio`,`fim`),
  CONSTRAINT `fk_descontos_preco` FOREIGN KEY (`preco_id`) REFERENCES `precos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `descontos`
--

LOCK TABLES `descontos` WRITE;
/*!40000 ALTER TABLE `descontos` DISABLE KEYS */;
INSERT INTO `descontos` VALUES (1,1,50.00,'2026-08-01 00:00:00','2026-12-31 23:59:59',1,'2026-08-27 11:04:51'),(2,2,50.00,'2026-08-01 00:00:00','2026-12-31 23:59:59',1,'2026-08-27 11:04:51'),(3,3,10.00,'2026-08-01 00:00:00','2026-12-31 23:59:59',1,'2026-08-27 11:04:51'),(4,4,30.00,'2026-08-01 00:00:00','2026-12-31 23:59:59',1,'2026-08-27 11:04:51'),(5,5,30.00,'2026-08-01 00:00:00','2026-12-31 23:59:59',1,'2026-08-27 11:04:51'),(6,6,40.00,'2026-08-01 00:00:00','2026-12-31 23:59:59',1,'2026-08-27 11:04:51'),(7,7,20.00,'2026-08-01 00:00:00','2026-12-31 23:59:59',1,'2026-08-27 11:04:51'),(8,8,50.00,'2026-08-01 00:00:00','2026-12-31 23:59:59',1,'2026-08-27 11:04:51');
/*!40000 ALTER TABLE `descontos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gogfake_ofertas`
--

DROP TABLE IF EXISTS `gogfake_ofertas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gogfake_ofertas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `jogo_id` int(10) unsigned NOT NULL,
  `slug` varchar(150) NOT NULL,
  `plataforma` varchar(60) NOT NULL DEFAULT 'PC',
  `preco_original` decimal(10,2) NOT NULL,
  `preco_atual` decimal(10,2) NOT NULL,
  `desconto` decimal(5,2) NOT NULL DEFAULT 0.00,
  `url` varchar(500) NOT NULL,
  `loja` varchar(40) NOT NULL DEFAULT 'GOGFake',
  `data_atualizacao` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_gogfake_oferta_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gogfake_ofertas`
--

LOCK TABLES `gogfake_ofertas` WRITE;
/*!40000 ALTER TABLE `gogfake_ofertas` DISABLE KEYS */;
INSERT INTO `gogfake_ofertas` VALUES (1,1,'elden-ring','PC',86.90,56.49,34.99,'/tcc/simulados/gogfake/detalhes.php?slug=elden-ring','GOGFake','2026-09-11 15:13:31'),(2,2,'god-of-war-ragnarok','PC',123.90,111.51,10.00,'/tcc/simulados/gogfake/detalhes.php?slug=god-of-war-ragnarok','GOGFake','2026-09-11 15:13:31'),(3,3,'the-legend-of-zelda-totk','PC',160.90,160.90,0.00,'/tcc/simulados/gogfake/detalhes.php?slug=the-legend-of-zelda-totk','GOGFake','2026-09-11 15:13:31'),(4,4,'baldurs-gate-3','PC',197.90,197.90,0.00,'/tcc/simulados/gogfake/detalhes.php?slug=baldurs-gate-3','GOGFake','2026-09-11 15:13:31'),(5,5,'cyberpunk-2077','PC',234.90,152.69,35.00,'/tcc/simulados/gogfake/detalhes.php?slug=cyberpunk-2077','GOGFake','2026-09-11 15:13:31'),(6,6,'red-dead-redemption-2','PC',271.90,244.71,10.00,'/tcc/simulados/gogfake/detalhes.php?slug=red-dead-redemption-2','GOGFake','2026-09-11 15:13:31'),(7,7,'the-witcher-3','PC',57.90,57.90,0.00,'/tcc/simulados/gogfake/detalhes.php?slug=the-witcher-3','GOGFake','2026-09-11 15:13:31'),(8,8,'hollow-knight','PC',94.90,94.90,0.00,'/tcc/simulados/gogfake/detalhes.php?slug=hollow-knight','GOGFake','2026-09-11 15:13:31'),(9,9,'hades','PC',131.90,85.74,35.00,'/tcc/simulados/gogfake/detalhes.php?slug=hades','GOGFake','2026-09-11 15:13:31'),(10,10,'celeste','PC',168.90,152.01,10.00,'/tcc/simulados/gogfake/detalhes.php?slug=celeste','GOGFake','2026-09-11 15:13:31'),(11,11,'stardew-valley','PC',205.90,205.90,0.00,'/tcc/simulados/gogfake/detalhes.php?slug=stardew-valley','GOGFake','2026-09-11 15:13:31'),(12,12,'terraria','PC',242.90,242.90,0.00,'/tcc/simulados/gogfake/detalhes.php?slug=terraria','GOGFake','2026-09-11 15:13:31'),(13,13,'minecraft','PC',279.90,181.94,35.00,'/tcc/simulados/gogfake/detalhes.php?slug=minecraft','GOGFake','2026-09-11 15:13:31'),(14,14,'gta-v','PC',65.90,59.31,10.00,'/tcc/simulados/gogfake/detalhes.php?slug=gta-v','GOGFake','2026-09-11 15:13:31'),(15,15,'the-last-of-us-part-ii','PC',102.90,102.90,0.00,'/tcc/simulados/gogfake/detalhes.php?slug=the-last-of-us-part-ii','GOGFake','2026-09-11 15:13:31'),(16,16,'ghost-of-tsushima','PC',139.90,139.90,0.00,'/tcc/simulados/gogfake/detalhes.php?slug=ghost-of-tsushima','GOGFake','2026-09-11 15:13:31'),(17,17,'horizon-forbidden-west','PC',176.90,114.99,35.00,'/tcc/simulados/gogfake/detalhes.php?slug=horizon-forbidden-west','GOGFake','2026-09-11 15:13:31'),(18,18,'spider-man-2','PC',213.90,192.51,10.00,'/tcc/simulados/gogfake/detalhes.php?slug=spider-man-2','GOGFake','2026-09-11 15:13:31'),(19,19,'resident-evil-4-remake','PC',250.90,250.90,0.00,'/tcc/simulados/gogfake/detalhes.php?slug=resident-evil-4-remake','GOGFake','2026-09-11 15:13:31'),(20,20,'alan-wake-2','PC',287.90,287.90,0.00,'/tcc/simulados/gogfake/detalhes.php?slug=alan-wake-2','GOGFake','2026-09-11 15:13:31'),(21,21,'starfield','PC',73.90,48.04,34.99,'/tcc/simulados/gogfake/detalhes.php?slug=starfield','GOGFake','2026-09-11 15:13:31'),(22,22,'final-fantasy-xvi','PC',110.90,99.81,10.00,'/tcc/simulados/gogfake/detalhes.php?slug=final-fantasy-xvi','GOGFake','2026-09-11 15:13:31'),(23,23,'persona-5-royal','PC',147.90,147.90,0.00,'/tcc/simulados/gogfake/detalhes.php?slug=persona-5-royal','GOGFake','2026-09-11 15:13:31'),(24,24,'nier-automata','PC',184.90,184.90,0.00,'/tcc/simulados/gogfake/detalhes.php?slug=nier-automata','GOGFake','2026-09-11 15:13:31'),(25,25,'dark-souls-3','PC',221.90,144.24,35.00,'/tcc/simulados/gogfake/detalhes.php?slug=dark-souls-3','GOGFake','2026-09-11 15:13:31'),(26,26,'sekiro','PC',258.90,233.01,10.00,'/tcc/simulados/gogfake/detalhes.php?slug=sekiro','GOGFake','2026-09-11 15:13:31'),(27,27,'bloodborne','PC',295.90,295.90,0.00,'/tcc/simulados/gogfake/detalhes.php?slug=bloodborne','GOGFake','2026-09-11 15:13:31'),(28,28,'cuphead','PC',81.90,81.90,0.00,'/tcc/simulados/gogfake/detalhes.php?slug=cuphead','GOGFake','2026-09-11 15:13:31'),(29,29,'ori-and-the-will-of-the-wisps','PC',118.90,77.29,35.00,'/tcc/simulados/gogfake/detalhes.php?slug=ori-and-the-will-of-the-wisps','GOGFake','2026-09-11 15:13:31'),(30,30,'it-takes-two','PC',155.90,140.31,10.00,'/tcc/simulados/gogfake/detalhes.php?slug=it-takes-two','GOGFake','2026-09-11 15:13:31');
/*!40000 ALTER TABLE `gogfake_ofertas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jogo_plataformas`
--

DROP TABLE IF EXISTS `jogo_plataformas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jogo_plataformas` (
  `jogo_id` int(10) unsigned NOT NULL,
  `plataforma_id` smallint(5) unsigned NOT NULL,
  `disponivel` tinyint(1) NOT NULL DEFAULT 1,
  `data_atualizacao` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`jogo_id`,`plataforma_id`),
  KEY `idx_jp_plataforma` (`plataforma_id`),
  KEY `idx_jp_disponivel` (`disponivel`),
  CONSTRAINT `fk_jp_jogo` FOREIGN KEY (`jogo_id`) REFERENCES `jogos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_jp_plataforma` FOREIGN KEY (`plataforma_id`) REFERENCES `plataformas` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jogo_plataformas`
--

LOCK TABLES `jogo_plataformas` WRITE;
/*!40000 ALTER TABLE `jogo_plataformas` DISABLE KEYS */;
INSERT INTO `jogo_plataformas` VALUES (1,1,1,'2026-08-27 11:04:51'),(1,4,1,'2026-08-27 11:04:51'),(2,1,1,'2026-08-27 11:04:51'),(2,4,1,'2026-08-27 11:04:51'),(3,1,1,'2026-08-27 11:04:51'),(3,4,1,'2026-08-27 11:04:51'),(4,1,1,'2026-08-27 11:04:51'),(4,4,1,'2026-08-27 11:04:51'),(5,1,1,'2026-08-27 11:04:51'),(5,4,1,'2026-08-27 11:04:51'),(6,1,1,'2026-08-27 11:04:51'),(6,4,1,'2026-08-27 11:04:51'),(7,1,1,'2026-08-27 11:04:51'),(8,1,1,'2026-08-27 11:04:51');
/*!40000 ALTER TABLE `jogo_plataformas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jogos`
--

DROP TABLE IF EXISTS `jogos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jogos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(150) NOT NULL,
  `nome` varchar(180) NOT NULL,
  `descricao` text NOT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  `genero` varchar(100) NOT NULL,
  `avaliacao` decimal(3,1) NOT NULL DEFAULT 0.0,
  `categoria` enum('popular','promocao','lancamento','gratuito') NOT NULL DEFAULT 'popular',
  `gratuito` tinyint(1) NOT NULL DEFAULT 0,
  `disponivel` tinyint(1) NOT NULL DEFAULT 1,
  `desenvolvedora` varchar(180) DEFAULT NULL,
  `tamanho` varchar(30) DEFAULT NULL,
  `classificacao` varchar(30) DEFAULT NULL,
  `data_atualizacao` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_jogos_slug` (`slug`),
  KEY `idx_jogos_nome` (`nome`),
  KEY `idx_jogos_genero` (`genero`),
  KEY `idx_jogos_disponivel` (`disponivel`),
  KEY `idx_jogos_atualizacao` (`data_atualizacao`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jogos`
--

LOCK TABLES `jogos` WRITE;
/*!40000 ALTER TABLE `jogos` DISABLE KEYS */;
INSERT INTO `jogos` VALUES (1,'cyberpunk-2077','Cyberpunk 2077','RPG de ação em mundo aberto ambientado em Night City.','gogfake/assets/img/cyberpunk-2077.svg','RPG / Ação',9.1,'popular',0,1,'CD Projekt Red','70 GB','18','2026-08-27 11:04:51'),(2,'the-witcher-3','The Witcher 3: Wild Hunt','Uma aventura épica de fantasia em um mundo aberto repleto de escolhas.','gogfake/assets/img/the-witcher-3.svg','RPG / Fantasia',9.5,'popular',0,1,'CD Projekt Red','50 GB','16','2026-08-27 11:04:51'),(3,'baldurs-gate-3','Baldur\'s Gate 3','RPG narrativo baseado em Dungeons & Dragons com liberdade de decisões.','gogfake/assets/img/baldurs-gate-3.svg','RPG',9.7,'popular',0,1,'Larian Studios','150 GB','16','2026-08-27 11:04:51'),(4,'elden-ring','Elden Ring','Uma jornada de fantasia sombria em um vasto mundo aberto.','gogfake/assets/img/elden-ring.svg','RPG / Ação',9.4,'popular',0,1,'FromSoftware','60 GB','16','2026-08-27 11:04:51'),(5,'hogwarts-legacy','Hogwarts Legacy','Explore Hogwarts e descubra sua própria história no mundo bruxo.','gogfake/assets/img/hogwarts-legacy.svg','Ação / Aventura',8.8,'lancamento',0,1,'Avalanche Software','85 GB','12','2026-08-27 11:04:51'),(6,'resident-evil-4','Resident Evil 4','Remake de uma missão de resgate em uma vila tomada pelo terror.','gogfake/assets/img/resident-evil-4.svg','Terror / Ação',9.0,'promocao',0,1,'Capcom','67 GB','18','2026-08-27 11:04:51'),(7,'stardew-valley','Stardew Valley','Construa uma nova vida em uma fazenda cercada por uma comunidade acolhedora.','gogfake/assets/img/stardew-valley.svg','Simulação / RPG',9.2,'popular',0,1,'ConcernedApe','1 GB','L','2026-08-27 11:04:51'),(8,'among-us','Among Us','Descubra o impostor em partidas cooperativas de investigação e estratégia.','gogfake/assets/img/among-us.svg','Ação / Social',8.1,'popular',0,1,'Innersloth','250 MB','L','2026-08-27 11:04:51');
/*!40000 ALTER TABLE `jogos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `links_jogos`
--

DROP TABLE IF EXISTS `links_jogos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `links_jogos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `jogo_id` int(10) unsigned NOT NULL,
  `plataforma_id` smallint(5) unsigned DEFAULT NULL,
  `url` varchar(500) NOT NULL,
  `tipo` enum('produto','oferta','suporte') NOT NULL DEFAULT 'produto',
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `data_atualizacao` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_links_url` (`url`),
  KEY `idx_links_jogo` (`jogo_id`),
  KEY `idx_links_plataforma` (`plataforma_id`),
  KEY `idx_links_ativo` (`ativo`),
  CONSTRAINT `fk_links_jogo` FOREIGN KEY (`jogo_id`) REFERENCES `jogos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_links_plataforma` FOREIGN KEY (`plataforma_id`) REFERENCES `plataformas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `links_jogos`
--

LOCK TABLES `links_jogos` WRITE;
/*!40000 ALTER TABLE `links_jogos` DISABLE KEYS */;
INSERT INTO `links_jogos` VALUES (1,1,1,'gogfake/jogo.php?slug=cyberpunk-2077','produto',1,'2026-08-27 11:04:51'),(2,2,1,'gogfake/jogo.php?slug=the-witcher-3','produto',1,'2026-08-27 11:04:51'),(3,3,1,'gogfake/jogo.php?slug=baldurs-gate-3','produto',1,'2026-08-27 11:04:51'),(4,4,1,'gogfake/jogo.php?slug=elden-ring','produto',1,'2026-08-27 11:04:51'),(5,5,1,'gogfake/jogo.php?slug=hogwarts-legacy','produto',1,'2026-08-27 11:04:51'),(6,6,1,'gogfake/jogo.php?slug=resident-evil-4','produto',1,'2026-08-27 11:04:51'),(7,7,1,'gogfake/jogo.php?slug=stardew-valley','produto',1,'2026-08-27 11:04:51'),(8,8,1,'gogfake/jogo.php?slug=among-us','produto',1,'2026-08-27 11:04:51');
/*!40000 ALTER TABLE `links_jogos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plataformas`
--

DROP TABLE IF EXISTS `plataformas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plataformas` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(80) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_plataformas_nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plataformas`
--

LOCK TABLES `plataformas` WRITE;
/*!40000 ALTER TABLE `plataformas` DISABLE KEYS */;
INSERT INTO `plataformas` VALUES (3,'Linux'),(2,'Mac'),(1,'PC'),(5,'PlayStation 5'),(4,'Steam Deck'),(6,'Xbox Series X|S');
/*!40000 ALTER TABLE `plataformas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `precos`
--

DROP TABLE IF EXISTS `precos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `precos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `jogo_id` int(10) unsigned NOT NULL,
  `plataforma_id` smallint(5) unsigned NOT NULL,
  `preco_original` decimal(10,2) NOT NULL DEFAULT 0.00,
  `preco_atual` decimal(10,2) NOT NULL DEFAULT 0.00,
  `moeda` char(3) NOT NULL DEFAULT 'BRL',
  `disponibilidade` enum('disponivel','indisponivel','pre_venda') NOT NULL DEFAULT 'disponivel',
  `data_atualizacao` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_precos_jogo_plataforma` (`jogo_id`,`plataforma_id`),
  KEY `idx_precos_jogo` (`jogo_id`),
  KEY `idx_precos_plataforma` (`plataforma_id`),
  KEY `idx_precos_atual` (`preco_atual`),
  KEY `idx_precos_disponibilidade` (`disponibilidade`),
  KEY `idx_precos_atualizacao` (`data_atualizacao`),
  CONSTRAINT `fk_precos_jogo` FOREIGN KEY (`jogo_id`) REFERENCES `jogos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_precos_plataforma` FOREIGN KEY (`plataforma_id`) REFERENCES `plataformas` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `precos`
--

LOCK TABLES `precos` WRITE;
/*!40000 ALTER TABLE `precos` DISABLE KEYS */;
INSERT INTO `precos` VALUES (1,1,1,179.90,89.95,'BRL','disponivel','2026-08-27 11:04:51'),(2,2,1,129.90,64.95,'BRL','disponivel','2026-08-27 11:04:51'),(3,3,1,239.90,215.91,'BRL','disponivel','2026-08-27 11:04:51'),(4,4,1,219.90,153.93,'BRL','disponivel','2026-08-27 11:04:51'),(5,5,1,239.90,167.93,'BRL','disponivel','2026-08-27 11:04:51'),(6,6,1,189.90,113.94,'BRL','disponivel','2026-08-27 11:04:51'),(7,7,1,21.99,17.59,'BRL','disponivel','2026-08-27 11:04:51'),(8,8,1,14.90,7.45,'BRL','disponivel','2026-08-27 11:04:51');
/*!40000 ALTER TABLE `precos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `vw_catalogo`
--

DROP TABLE IF EXISTS `vw_catalogo`;
/*!50001 DROP VIEW IF EXISTS `vw_catalogo`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `vw_catalogo` AS SELECT 
 1 AS `id`,
 1 AS `slug`,
 1 AS `nome`,
 1 AS `descricao`,
 1 AS `imagem`,
 1 AS `genero`,
 1 AS `avaliacao`,
 1 AS `plataforma`,
 1 AS `preco_original`,
 1 AS `preco_atual`,
 1 AS `desconto`,
 1 AS `disponibilidade`,
 1 AS `disponivel`,
 1 AS `url`,
 1 AS `data_atualizacao`*/;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `vw_catalogo`
--

/*!50001 DROP VIEW IF EXISTS `vw_catalogo`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_catalogo` AS select `j`.`id` AS `id`,`j`.`slug` AS `slug`,`j`.`nome` AS `nome`,`j`.`descricao` AS `descricao`,`j`.`imagem` AS `imagem`,`j`.`genero` AS `genero`,`j`.`avaliacao` AS `avaliacao`,`p`.`nome` AS `plataforma`,`pr`.`preco_original` AS `preco_original`,`pr`.`preco_atual` AS `preco_atual`,coalesce(`d`.`percentual`,0) AS `desconto`,`pr`.`disponibilidade` AS `disponibilidade`,`j`.`disponivel` AS `disponivel`,`l`.`url` AS `url`,`j`.`data_atualizacao` AS `data_atualizacao` from ((((`jogos` `j` join `precos` `pr` on(`pr`.`jogo_id` = `j`.`id`)) join `plataformas` `p` on(`p`.`id` = `pr`.`plataforma_id`)) left join `descontos` `d` on(`d`.`preco_id` = `pr`.`id`)) left join `links_jogos` `l` on(`l`.`jogo_id` = `j`.`id` and `l`.`plataforma_id` = `pr`.`plataforma_id` and `l`.`tipo` = 'produto' and `l`.`ativo` = 1)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-05 11:38:27

SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS = 1;

-- ============================================================
-- FINAL
-- ============================================================

SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS = 1;
