<?php
require 'bootstrap.php';
$statement0 = <<<EOS
DROP TABLE IF EXISTS email_corporativo;
DROP TABLE IF EXISTS telefone_corporativo;
DROP TABLE IF EXISTS contatos_corporativos;
DROP TABLE IF EXISTS dados_corporativos;
DROP TABLE IF EXISTS email_pessoal;
DROP TABLE IF EXISTS telefone_pessoal;
DROP TABLE IF EXISTS contatos_pessoais;
DROP TABLE IF EXISTS dados_pessoais;
DROP TABLE IF EXISTS dados_funcionario;
DROP TABLE IF EXISTS calcula_horas;
DROP TABLE IF EXISTS conta_folgas;
DROP TABLE IF EXISTS ponto;
DROP TABLE IF EXISTS ferias_abonos;
DROP TABLE IF EXISTS outros;
DROP TABLE IF EXISTS faltas_folgas;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS senhas;
DROP TABLE IF EXISTS seguranca;
DROP TABLE IF EXISTS administrativo;
DROP TABLE IF EXISTS funcionario;
EOS;

$statement1 = <<<EOS
CREATE TABLE IF NOT EXISTS `funcionario` (
    `matricula` int(11) NOT NULL,
    PRIMARY KEY (`matricula`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  
  CREATE TABLE IF NOT EXISTS `administrativo` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `matricula_funcionario` int(11) DEFAULT NULL,
    `data_cadastro` TIMESTAMP NULL,
    `data_alteracao` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `matricula_funcionario` (`matricula_funcionario`),
    CONSTRAINT `administrativo_ibfk_1` FOREIGN KEY (`matricula_funcionario`) REFERENCES `funcionario` (`matricula`)
  ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
  
  CREATE TABLE IF NOT EXISTS `dados_funcionario` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `matricula_funcionario` int(11) DEFAULT NULL,
    `data_cadastro` TIMESTAMP NULL,
    `data_alteracao` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `matricula_funcionario` (`matricula_funcionario`),
    CONSTRAINT `dados_funcionario_ibfk_1` FOREIGN KEY (`matricula_funcionario`) REFERENCES `funcionario` (`matricula`)
  ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
  
  CREATE TABLE IF NOT EXISTS `dados_pessoais` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `id_funcionario` int(11) DEFAULT NULL,
    `nome` varchar(255) DEFAULT NULL,
    `cpf` varchar(255) DEFAULT NULL,
    `rg` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `id_funcionario` (`id_funcionario`),
    CONSTRAINT `dados_pessoais_ibfk_1` FOREIGN KEY (`id_funcionario`) REFERENCES `dados_funcionario` (`id`)
  ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
  
  CREATE TABLE IF NOT EXISTS `faltas_folgas` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `id_administrativo` int(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `id_administrativo` (`id_administrativo`),
    CONSTRAINT `faltas_folgas_ibfk_1` FOREIGN KEY (`id_administrativo`) REFERENCES `administrativo` (`id`)
  ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
  
  CREATE TABLE IF NOT EXISTS `ferias_abonos` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `id_faltas_folgas` int(11) DEFAULT NULL,
    `numero_abonos` int(2) DEFAULT NULL,
    `adiantamento` BOOL,
    `data_inicio` TIMESTAMP NULL,
    `data_fim` TIMESTAMP NULL,
    `status` varchar(255) DEFAULT NULL,
    `tipo` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `id_faltas_folgas` (`id_faltas_folgas`),
    CONSTRAINT `ferias_ibfk_1` FOREIGN KEY (`id_faltas_folgas`) REFERENCES `faltas_folgas` (`id`)
  ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

  CREATE TABLE IF NOT EXISTS `outros` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `id_faltas_folgas` int(11) DEFAULT NULL,
    `tipo` varchar(255) DEFAULT NULL,
    `data_inicio` TIMESTAMP NULL,
    `data_fim` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `id_faltas_folgas` (`id_faltas_folgas`),
    CONSTRAINT `outros_ibfk_1` FOREIGN KEY (`id_faltas_folgas`) REFERENCES `faltas_folgas` (`id`)
  ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

  CREATE TABLE IF NOT EXISTS `ponto` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `id_administrativo` int(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `id_administrativo` (`id_administrativo`),
    CONSTRAINT `ponto_ibfk_1` FOREIGN KEY (`id_administrativo`) REFERENCES `administrativo` (`id`)
  ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

  CREATE TABLE IF NOT EXISTS `seguranca` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `id_administrativo` int(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `id_administrativo` (`id_administrativo`),
    CONSTRAINT `seguranca_ibfk_1` FOREIGN KEY (`id_administrativo`) REFERENCES `administrativo` (`id`)
  ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

  CREATE TABLE IF NOT EXISTS `senhas` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `id_seguranca` int(11) DEFAULT NULL,
    `senhas` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `id_seguranca` (`id_seguranca`),
    CONSTRAINT `senhas_ibfk_1` FOREIGN KEY (`id_seguranca`) REFERENCES `seguranca` (`id`)
  ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

  CREATE TABLE IF NOT EXISTS `calcula_horas` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `id_ponto` int(11) DEFAULT NULL,
    `adicional_noturno` int(11) DEFAULT NULL,
    `hora_extra` int(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `id_ponto` (`id_ponto`),
    CONSTRAINT `calcula_horas_ibfk_1` FOREIGN KEY (`id_ponto`) REFERENCES `ponto` (`id`)
  ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

  CREATE TABLE IF NOT EXISTS `conta_folgas` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `id_ponto` int(11) DEFAULT NULL,
    `ferias_restantes` int(11) DEFAULT NULL,
    `abonos_restantes` int(11) DEFAULT NULL,
    `qtd_outros` int(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `id_ponto` (`id_ponto`),
    CONSTRAINT `conta_folgas_ibfk_1` FOREIGN KEY (`id_ponto`) REFERENCES `ponto` (`id`)
  ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

  CREATE TABLE IF NOT EXISTS `contatos_pessoais` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `id_pessoais` int(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `id_pessoais` (`id`),
    KEY `contatos_pessoais_ibfk_1` (`id_pessoais`),
    CONSTRAINT `contatos_pessoais_ibfk_1` FOREIGN KEY (`id_pessoais`) REFERENCES `dados_pessoais` (`id`)
  ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

  CREATE TABLE IF NOT EXISTS `dados_corporativos` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `id_funcionario` int(11) DEFAULT NULL,
    `chave_c` varchar(255) DEFAULT NULL,
    `cartao_bb` varchar(255) DEFAULT NULL,
    `cartao_capital` varchar(255) DEFAULT NULL,
    `cartao_bbts` varchar(255) DEFAULT NULL,
    `num_contrato` varchar(255) DEFAULT NULL,
    `data_contratacao` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `id_funcionario` (`id_funcionario`),
    CONSTRAINT `dados_corporativos_ibfk_1` FOREIGN KEY (`id_funcionario`) REFERENCES `dados_funcionario` (`id`)
  ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

  CREATE TABLE IF NOT EXISTS `email_pessoal` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `id_pessoal` int(11) DEFAULT NULL,
    `email` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `id_pessoal` (`id_pessoal`),
    CONSTRAINT `email_pessoal_ibfk_1` FOREIGN KEY (`id_pessoal`) REFERENCES `contatos_pessoais` (`id`)
  ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
  
  CREATE TABLE IF NOT EXISTS `roles` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `id_seguranca` int(11) DEFAULT NULL,
    `role` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `id_seguranca` (`id_seguranca`),
    CONSTRAINT `roles_ibfk_1` FOREIGN KEY (`id_seguranca`) REFERENCES `seguranca` (`id`)
  ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

  CREATE TABLE IF NOT EXISTS `telefone_pessoal` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `id_telefone_pessoal` int(11) DEFAULT NULL,
    `telefone` int(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `id_telefone_pessoal` (`id_telefone_pessoal`),
    CONSTRAINT `telefone_pessoal_ibfk_1` FOREIGN KEY (`id_telefone_pessoal`) REFERENCES `contatos_pessoais` (`id`)
  ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

  CREATE TABLE IF NOT EXISTS `contatos_corporativos` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `id_corporativos` int(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `id_corporativos` (`id_corporativos`),
    CONSTRAINT `contatos_corporativos_ibfk_1` FOREIGN KEY (`id_corporativos`) REFERENCES `dados_corporativos` (`id`)
  ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

  CREATE TABLE IF NOT EXISTS `email_corporativo` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `id_corporativos` int(11) DEFAULT NULL,
    `email` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `id_corporativos` (`id_corporativos`),
    CONSTRAINT `email_corporativo_ibfk_1` FOREIGN KEY (`id_corporativos`) REFERENCES `contatos_corporativos` (`id`)
  ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

  CREATE TABLE IF NOT EXISTS `telefone_corporativo` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `id_telefone_corporativo` int(11) DEFAULT NULL,
    `telefone` int(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `id_telefone_corporativo` (`id_telefone_corporativo`),
    CONSTRAINT `telefone_corporativo_ibfk_1` FOREIGN KEY (`id_telefone_corporativo`) REFERENCES `contatos_corporativos` (`id`)
  ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
EOS;

$statement2 = <<<EOS
    INSERT INTO funcionario (matricula) VALUES (112243);
    INSERT INTO dados_funcionario (matricula_funcionario , data_cadastro ,data_alteracao ) values (112243,'2020-01-01','2020-01-01');
    INSERT INTO dados_pessoais (id_funcionario, nome , cpf,rg ) values (LAST_INSERT_ID(),'maxwell','03340717125','2864054');
    INSERT INTO administrativo (matricula_funcionario ,data_cadastro ,data_alteracao ) VALUES (112243,'2022-01-01','2022-01-01');
    INSERT INTO faltas_folgas (id_administrativo) VALUES (LAST_INSERT_ID());
    INSERT INTO ferias_abonos (id_faltas_folgas,numero_abonos ,data_inicio, data_fim, adiantamento ,status,tipo ) VALUES (LAST_INSERT_ID(),0,'2022-09-01','2022-10-01',TRUE,'pendente','ferias');
    INSERT INTO administrativo (matricula_funcionario ,data_cadastro ,data_alteracao ) VALUES (112243,'2020-01-01','2020-01-01');
    INSERT INTO seguranca  (id_administrativo ) VALUES (LAST_INSERT_ID());
    INSERT INTO senhas (id_seguranca,senhas) VALUES (LAST_INSERT_ID(),md5('12345'));

    INSERT INTO dados_funcionario (matricula_funcionario , data_cadastro ,data_alteracao ) values (112243,'2020-01-01','2020-01-01');
    INSERT INTO dados_corporativos (id_funcionario, chave_c, cartao_bb, cartao_capital, cartao_bbts, num_contrato, data_contratacao ) values (LAST_INSERT_ID(),'112243','111111','111112','111113','111114','2020-01-01');
EOS;

try {
    $dbConnection->exec($statement0);
    $dbConnection->exec($statement1);
    $dbConnection->exec($statement2);
    echo "Success!\n";
} catch (\PDOException $e) {
    exit($e->getMessage());
}