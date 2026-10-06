-- Migração não destrutiva para integração de preços reais IsThereAnyDeal.
-- Execute uma vez no banco `tcc`, depois de importar o esquema principal.
-- Nenhuma tabela legada de preços ou lojas simuladas é removida ou alterada.

CREATE TABLE IF NOT EXISTS `jogo_provedores` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `jogo_id` INT(10) UNSIGNED NOT NULL,
  `provedor` VARCHAR(40) NOT NULL,
  `external_id` CHAR(36) DEFAULT NULL,
  `external_slug` VARCHAR(180) DEFAULT NULL,
  `precos_atualizados_em` DATETIME DEFAULT NULL,
  `data_atualizacao` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_jogo_provedor` (`jogo_id`, `provedor`),
  UNIQUE KEY `uq_provedor_external_id` (`provedor`, `external_id`),
  KEY `idx_jogo_provedores_external_id` (`external_id`),
  CONSTRAINT `fk_jogo_provedores_jogo` FOREIGN KEY (`jogo_id`) REFERENCES `jogos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `precos_cache_itad` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `jogo_id` INT(10) UNSIGNED NOT NULL,
  `loja_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `loja` VARCHAR(120) NOT NULL,
  `plataforma` VARCHAR(100) NOT NULL DEFAULT 'PC',
  `preco` DECIMAL(12,2) NOT NULL,
  `preco_antigo` DECIMAL(12,2) DEFAULT NULL,
  `desconto` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `moeda` CHAR(3) NOT NULL,
  `disponibilidade` VARCHAR(24) NOT NULL DEFAULT 'disponivel',
  `disponivel` TINYINT(1) NOT NULL DEFAULT 1,
  `url` VARCHAR(1000) NOT NULL,
  `url_oferta` VARCHAR(1000) NOT NULL,
  `external_id` VARCHAR(120) NOT NULL DEFAULT '',
  `data_atualizacao` DATETIME NOT NULL,
  `cache_atualizado_em` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_itad_oferta` (`jogo_id`, `loja_id`, `plataforma`, `moeda`),
  KEY `idx_itad_cache_ttl` (`jogo_id`, `cache_atualizado_em`),
  KEY `idx_itad_disponivel_preco` (`jogo_id`, `disponivel`, `preco`),
  CONSTRAINT `fk_precos_cache_itad_jogo` FOREIGN KEY (`jogo_id`) REFERENCES `jogos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
