
CREATE DATABASE IF NOT EXISTS `gestionprojetsentreprise`;
USE `gestionprojetsentreprise`;

-- Dumping structure for table gestionprojetsentreprise.affectation
CREATE TABLE IF NOT EXISTS `affectation` (
  `IdAffectation` int NOT NULL AUTO_INCREMENT,
  `DateAffectation` date DEFAULT NULL,
  `HeureAffectation` time DEFAULT NULL,
  `FonctionAffectation` varchar(255) DEFAULT NULL,
  `MatriculePersonnel` varchar(255) DEFAULT NULL,
  `IdTache` int DEFAULT NULL,
  PRIMARY KEY (`IdAffectation`),
  KEY `MatriculePersonnel` (`MatriculePersonnel`),
  KEY `IdTache` (`IdTache`),
  CONSTRAINT `affectation_ibfk_1` FOREIGN KEY (`MatriculePersonnel`) REFERENCES `personnel` (`MatriculePersonnel`),
  CONSTRAINT `affectation_ibfk_2` FOREIGN KEY (`IdTache`) REFERENCES `tache` (`IdTache`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table gestionprojetsentreprise.affectation: ~0 rows (approximately)
REPLACE INTO `affectation` (`IdAffectation`, `DateAffectation`, `HeureAffectation`, `FonctionAffectation`, `MatriculePersonnel`, `IdTache`) VALUES
	(1, '2025-01-20', '09:00:00', 'Responsable maquettage', 'EMP002', 1),
	(2, '2025-02-06', '09:00:00', 'Développeur principal', 'EMP001', 2),
	(3, '2025-02-03', '09:00:00', 'Designer graphique', 'EMP002', 3),
	(4, '2025-02-12', '09:00:00', 'Designer graphique', 'EMP002', 4),
	(5, '2025-03-02', '09:00:00', 'Développeur Back-end', 'EMP001', 5),
	(6, '2025-04-16', '09:00:00', 'Testeur', 'EMP004', 6),
	(7, '2025-04-14', NULL, '', 'EMP003', 2);

-- Dumping structure for table gestionprojetsentreprise.client
CREATE TABLE IF NOT EXISTS `client` (
  `IdClient` int NOT NULL AUTO_INCREMENT,
  `NomClient` varchar(255) NOT NULL,
  `AdresseClient` varchar(255) DEFAULT NULL,
  `EmailClient` varchar(255) DEFAULT NULL,
  `TelClient` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`IdClient`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table gestionprojetsentreprise.client: ~4 rows (approximately)
REPLACE INTO `client` (`IdClient`, `NomClient`, `AdresseClient`, `EmailClient`, `TelClient`) VALUES
	(1, 'Entreprise ABC', '123 Rue du Commerce, Paris', 'contact@entrepriseabc.fr', '01 23 45 67 89'),
	(2, 'Société XYZ', '456 Avenue des Affaires, Lyon', 'info@societe-xyz.fr', '04 56 78 90 12'),
	(3, 'Tech Innov', '789 Boulevard de la Technologie, Marseille', 'contact@techinnov.fr', '03 21 43 65 87'),
	(4, 'Design Studio', '321 Rue des Créateurs, Bordeaux', 'hello@designstudio.fr', '222 05 67 89 01 23');

-- Dumping structure for table gestionprojetsentreprise.personnel
CREATE TABLE IF NOT EXISTS `personnel` (
  `MatriculePersonnel` varchar(255) NOT NULL,
  `NomPersonnel` varchar(255) NOT NULL,
  `PrenomPersonnel` varchar(255) NOT NULL,
  `EmailPersonnel` varchar(255) DEFAULT NULL,
  `TelPersonnel` varchar(255) DEFAULT NULL,
  `CodeService` varchar(255) DEFAULT NULL,
  `CompetencesPersonnel` text,
  `CommentairesPersonnel` text,
  PRIMARY KEY (`MatriculePersonnel`),
  KEY `CodeService` (`CodeService`),
  CONSTRAINT `personnel_ibfk_1` FOREIGN KEY (`CodeService`) REFERENCES `service` (`CodeService`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table gestionprojetsentreprise.personnel: ~4 rows (approximately)
REPLACE INTO `personnel` (`MatriculePersonnel`, `NomPersonnel`, `PrenomPersonnel`, `EmailPersonnel`, `TelPersonnel`, `CodeService`, `CompetencesPersonnel`, `CommentairesPersonnel`) VALUES
	('EMP001', 'Dupont', 'Jean', 'jean.dupont@entreprise.fr', '237 06 12 34 56 78', 'DEV', 'PHP, JavaScript, MySQL', 'Excellent développeur.'),
	('EMP002', 'Martin', 'Sophie', 'sophie.martin@entreprise.fr', '06 98 76 54 32', 'DESIGN', 'Photoshop, Illustrator', 'Très créative.'),
	('EMP003', 'Durand', 'Paul', 'paul.durand@entreprise.fr', '06 45 67 89 01', 'MARKETING', 'SEO, SEA, Google Ads', 'Expert en marketing digital.'),
	('EMP004', 'Lemoine', 'Claire', 'claire.lemoine@entreprise.fr', '06 78 90 12 34', 'SUPPORT', 'Support client, Gestion des tickets', 'Très réactive.');

-- Dumping structure for table gestionprojetsentreprise.projet
CREATE TABLE IF NOT EXISTS `projet` (
  `IdProjet` int NOT NULL AUTO_INCREMENT,
  `TitreProjet` varchar(255) NOT NULL,
  `DescriptionProjet` varchar(255) DEFAULT NULL,
  `CoutProjet` int DEFAULT NULL,
  `DateDebutProjet` date DEFAULT NULL,
  `DateFinProjet` date DEFAULT NULL,
  `EtatProjet` int DEFAULT NULL,
  `IdClient` int DEFAULT NULL,
  `IdTypeProjet` int DEFAULT NULL,
  PRIMARY KEY (`IdProjet`),
  KEY `IdClient` (`IdClient`),
  KEY `IdTypeProjet` (`IdTypeProjet`),
  CONSTRAINT `projet_ibfk_1` FOREIGN KEY (`IdClient`) REFERENCES `client` (`IdClient`),
  CONSTRAINT `projet_ibfk_2` FOREIGN KEY (`IdTypeProjet`) REFERENCES `typeprojet` (`IdTypeProjet`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table gestionprojetsentreprise.projet: ~4 rows (approximately)
REPLACE INTO `projet` (`IdProjet`, `TitreProjet`, `DescriptionProjet`, `CoutProjet`, `DateDebutProjet`, `DateFinProjet`, `EtatProjet`, `IdClient`, `IdTypeProjet`) VALUES
	(1, 'Site E-commerce', 'Création d\'un site e-commerce complet', 6000, '2025-01-15', '2025-04-30', 1, 1, 1),
	(2, 'Refonte Logo', 'Modernisation de l\'identité visuelle', 2500, '2025-02-01', '2025-02-28', 2, 2, 2),
	(3, 'Application Mobile', 'Développement d\'une application mobile', 10000, '2025-03-01', '2025-06-30', 1, 3, 3),
	(4, 'Audit Technique', 'Audit des systèmes existants', 3000, '2025-04-01', '2025-04-15', 3, 4, 4),
	(5, 'project', 'new project', 8000, '2025-04-14', '2025-05-14', 1, 4, 3);

-- Dumping structure for table gestionprojetsentreprise.reglement
CREATE TABLE IF NOT EXISTS `reglement` (
  `IdReglement` int NOT NULL AUTO_INCREMENT,
  `DateReglement` date DEFAULT NULL,
  `HeureReglement` time DEFAULT NULL,
  `MontantReglement` int DEFAULT NULL,
  `IdClient` int DEFAULT NULL,
  PRIMARY KEY (`IdReglement`),
  KEY `IdClient` (`IdClient`),
  CONSTRAINT `reglement_ibfk_1` FOREIGN KEY (`IdClient`) REFERENCES `client` (`IdClient`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table gestionprojetsentreprise.reglement: ~4 rows (approximately)
REPLACE INTO `reglement` (`IdReglement`, `DateReglement`, `HeureReglement`, `MontantReglement`, `IdClient`) VALUES
	(1, '2025-01-15', '10:30:00', 3000, 1),
	(2, '2025-02-01', '14:45:00', 1500, 2),
	(3, '2025-03-10', '11:00:00', 5000, 3),
	(4, '2025-04-05', '09:15:00', 2000, 4),
	(5, '2025-04-14', '14:44:00', 300000000, 1);

-- Dumping structure for table gestionprojetsentreprise.service
CREATE TABLE IF NOT EXISTS `service` (
  `CodeService` varchar(255) NOT NULL,
  `LibelleService` varchar(255) NOT NULL,
  `DescriptionService` text,
  PRIMARY KEY (`CodeService`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table gestionprojetsentreprise.service: ~4 rows (approximately)
REPLACE INTO `service` (`CodeService`, `LibelleService`, `DescriptionService`) VALUES
	('DESIGN', 'Design Graphique', 'Service spécialisé dans la création graphique.'),
	('DEV', 'Développement', 'Service dédié au développement de logiciels et applications.'),
	('MARKETING', 'Marketing', 'Service responsable des campagnes marketing.'),
	('SUPPORT', 'Support Technique', 'Service d\'assistance technique.'),
	('SVC_67fd3010221a0', 'service', 'test'),
	('SVC_67fd332cb2891', 'new test', NULL),
	('SVC_67fd34a1b4510', 'test1', 'meno'),
	('SVC_67fd35249f868', 'bla', 'bla');

-- Dumping structure for table gestionprojetsentreprise.tache
CREATE TABLE IF NOT EXISTS `tache` (
  `IdTache` int NOT NULL AUTO_INCREMENT,
  `LibelleTache` varchar(255) NOT NULL,
  `DateEnregTache` date DEFAULT NULL,
  `DateDebutTache` date DEFAULT NULL,
  `DateFinTache` date DEFAULT NULL,
  `EtatTache` int DEFAULT NULL,
  `IdProjet` int DEFAULT NULL,
  PRIMARY KEY (`IdTache`),
  KEY `IdProjet` (`IdProjet`),
  CONSTRAINT `tache_ibfk_1` FOREIGN KEY (`IdProjet`) REFERENCES `projet` (`IdProjet`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table gestionprojetsentreprise.tache: ~6 rows (approximately)
REPLACE INTO `tache` (`IdTache`, `LibelleTache`, `DateEnregTache`, `DateDebutTache`, `DateFinTache`, `EtatTache`, `IdProjet`) VALUES
	(1, 'Maquettage des pages', '2025-01-16', '2025-01-20', '2025-02-05', 3, 1),
	(2, 'Développement Front-end', '2025-01-16', '2025-02-06', '2025-03-15', 2, 1),
	(3, 'Création des propositions', '2025-02-02', '2025-02-03', '2025-02-10', 3, 2),
	(4, 'Finalisation du logo', '2025-02-11', '2025-02-12', '2025-02-25', 2, 2),
	(5, 'Développement Back-end', '2025-03-02', '2025-03-10', '2025-04-15', 1, 3),
	(6, 'Tests et validation', '2025-04-16', '2025-04-20', '2025-05-01', 1, 3),
	(7, 'test ', '2025-04-14', '2025-04-14', '2025-04-21', 1, 3),
	(8, 'test ', '2025-04-14', '2025-04-14', '2025-04-21', 2, 3);

-- Dumping structure for table gestionprojetsentreprise.typeprojet
CREATE TABLE IF NOT EXISTS `typeprojet` (
  `IdTypeProjet` int NOT NULL AUTO_INCREMENT,
  `LibelleTypeProjet` varchar(255) NOT NULL,
  `ForfaitCoutTypeProjet` int DEFAULT NULL,
  PRIMARY KEY (`IdTypeProjet`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table gestionprojetsentreprise.typeprojet: ~4 rows (approximately)
REPLACE INTO `typeprojet` (`IdTypeProjet`, `LibelleTypeProjet`, `ForfaitCoutTypeProjet`) VALUES
	(1, 'Développement Web', 5000),
	(2, 'Refonte Graphique', 3000),
	(3, 'Application Mobile', 8000),
	(4, 'Audit Technique', 2000);

-- Dumping structure for table gestionprojetsentreprise.utilisateur
CREATE TABLE IF NOT EXISTS `utilisateur` (
  `IdUtilisateur` int NOT NULL AUTO_INCREMENT,
  `NomUtilisateur` varchar(255) NOT NULL,
  `MotDePasse` varchar(255) NOT NULL,
  `Role` varchar(50) NOT NULL,
  PRIMARY KEY (`IdUtilisateur`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table gestionprojetsentreprise.utilisateur: ~2 rows (admin123, carole123)
REPLACE INTO `utilisateur` (`IdUtilisateur`, `NomUtilisateur`, `MotDePasse`, `Role`) VALUES
	(1, 'admin', '$2y$10$z8N0o7STLArlhfwpfr0Ek.u76pYsb4ChFipMdZsIk1XeeUbtZyYxe', 'admin'),
	(4, 'carole', '$2y$10$2xdV1smzD4o2FX437YJRj.2CnUjodQjq0s2YEe/N8rOrkinz6EQje', 'personnel');

