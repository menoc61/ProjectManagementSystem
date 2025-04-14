-- Ensure the database is created and selected
CREATE DATABASE IF NOT EXISTS GestionProjetsEntreprise;
USE GestionProjetsEntreprise;

-- Drop existing tables to avoid conflicts
DROP TABLE IF EXISTS AFFECTATION;
DROP TABLE IF EXISTS REGLEMENT;
DROP TABLE IF EXISTS TACHE;
DROP TABLE IF EXISTS PROJET;
DROP TABLE IF EXISTS PERSONNEL;
DROP TABLE IF EXISTS SERVICE;
DROP TABLE IF EXISTS TYPEPROJET;
DROP TABLE IF EXISTS CLIENT;
DROP TABLE IF EXISTS UTILISATEUR;

-- Recreate tables
CREATE TABLE CLIENT (
  IdClient INT PRIMARY KEY AUTO_INCREMENT,
  NomClient VARCHAR(255) NOT NULL,
  AdresseClient VARCHAR(255),
  EmailClient VARCHAR(255),
  TelClient VARCHAR(255)
);

CREATE TABLE TYPEPROJET (
  IdTypeProjet INT PRIMARY KEY AUTO_INCREMENT,
  LibelleTypeProjet VARCHAR(255) NOT NULL,
  ForfaitCoutTypeProjet INT
);

CREATE TABLE SERVICE (
  CodeService VARCHAR(255) PRIMARY KEY,
  LibelleService VARCHAR(255) NOT NULL,
  DescriptionService TEXT
);

CREATE TABLE PERSONNEL (
  MatriculePersonnel VARCHAR(255) PRIMARY KEY,
  NomPersonnel VARCHAR(255) NOT NULL,
  PrenomPersonnel VARCHAR(255) NOT NULL,
  EmailPersonnel VARCHAR(255),
  TelPersonnel VARCHAR(255),
  CodeService VARCHAR(255),
  CompetencesPersonnel TEXT,
  CommentairesPersonnel TEXT,
  FOREIGN KEY (CodeService) REFERENCES SERVICE(CodeService)
);

CREATE TABLE PROJET (
  IdProjet INT PRIMARY KEY AUTO_INCREMENT,
  TitreProjet VARCHAR(255) NOT NULL,
  DescriptionProjet VARCHAR(255),
  CoutProjet INT,
  DateDebutProjet DATE,
  DateFinProjet DATE,
  EtatProjet INT, -- 1: En cours, 2: Terminé, 3: Annulé
  IdClient INT,
  IdTypeProjet INT,
  FOREIGN KEY (IdClient) REFERENCES CLIENT(IdClient),
  FOREIGN KEY (IdTypeProjet) REFERENCES TYPEPROJET(IdTypeProjet)
);

CREATE TABLE TACHE (
  IdTache INT PRIMARY KEY AUTO_INCREMENT,
  LibelleTache VARCHAR(255) NOT NULL,
  DateEnregTache DATE,
  DateDebutTache DATE,
  DateFinTache DATE,
  EtatTache INT, -- 1: À faire, 2: En cours, 3: Terminée, 4: Annulée
  IdProjet INT,
  FOREIGN KEY (IdProjet) REFERENCES PROJET(IdProjet)
);

CREATE TABLE REGLEMENT (
  IdReglement INT PRIMARY KEY AUTO_INCREMENT,
  DateReglement DATE,
  HeureReglement TIME,
  MontantReglement INT,
  IdClient INT,
  FOREIGN KEY (IdClient) REFERENCES CLIENT(IdClient)
);

CREATE TABLE AFFECTATION (
  IdAffectation INT PRIMARY KEY AUTO_INCREMENT,
  DateAffectation DATE,
  HeureAffectation TIME,
  FonctionAffectation VARCHAR(255),
  MatriculePersonnel VARCHAR(255),
  IdTache INT,
  FOREIGN KEY (MatriculePersonnel) REFERENCES PERSONNEL(MatriculePersonnel),
  FOREIGN KEY (IdTache) REFERENCES TACHE(IdTache)
);

CREATE TABLE UTILISATEUR (
  IdUtilisateur INT AUTO_INCREMENT PRIMARY KEY,
  NomUtilisateur VARCHAR(255) NOT NULL,
  MotDePasse VARCHAR(255) NOT NULL,
  Role VARCHAR(50) NOT NULL
);

-- Insert dummy data into CLIENT
INSERT INTO CLIENT (NomClient, AdresseClient, EmailClient, TelClient) VALUES
('Entreprise ABC', '123 Rue du Commerce, Paris', 'contact@entrepriseabc.fr', '01 23 45 67 89'),
('Société XYZ', '456 Avenue des Affaires, Lyon', 'info@societe-xyz.fr', '04 56 78 90 12'),
('Tech Innov', '789 Boulevard de la Technologie, Marseille', 'contact@techinnov.fr', '03 21 43 65 87'),
('Design Studio', '321 Rue des Créateurs, Bordeaux', 'hello@designstudio.fr', '05 67 89 01 23');

-- Insert dummy data into TYPEPROJET
INSERT INTO TYPEPROJET (LibelleTypeProjet, ForfaitCoutTypeProjet) VALUES
('Développement Web', 5000),
('Refonte Graphique', 3000),
('Application Mobile', 8000),
('Audit Technique', 2000);

-- Insert dummy data into SERVICE
INSERT INTO SERVICE (CodeService, LibelleService, DescriptionService) VALUES
('DEV', 'Développement', 'Service dédié au développement de logiciels et applications.'),
('DESIGN', 'Design Graphique', 'Service spécialisé dans la création graphique.'),
('MARKETING', 'Marketing', 'Service responsable des campagnes marketing.'),
('SUPPORT', 'Support Technique', 'Service d\'assistance technique.');

-- Insert dummy data into PERSONNEL
INSERT INTO PERSONNEL (MatriculePersonnel, NomPersonnel, PrenomPersonnel, EmailPersonnel, TelPersonnel, CodeService, CompetencesPersonnel, CommentairesPersonnel) VALUES
('EMP001', 'Dupont', 'Jean', 'jean.dupont@entreprise.fr', '06 12 34 56 78', 'DEV', 'PHP, JavaScript, MySQL', 'Excellent développeur.'),
('EMP002', 'Martin', 'Sophie', 'sophie.martin@entreprise.fr', '06 98 76 54 32', 'DESIGN', 'Photoshop, Illustrator', 'Très créative.'),
('EMP003', 'Durand', 'Paul', 'paul.durand@entreprise.fr', '06 45 67 89 01', 'MARKETING', 'SEO, SEA, Google Ads', 'Expert en marketing digital.'),
('EMP004', 'Lemoine', 'Claire', 'claire.lemoine@entreprise.fr', '06 78 90 12 34', 'SUPPORT', 'Support client, Gestion des tickets', 'Très réactive.');

-- Insert dummy data into PROJET
INSERT INTO PROJET (TitreProjet, DescriptionProjet, CoutProjet, DateDebutProjet, DateFinProjet, EtatProjet, IdClient, IdTypeProjet) VALUES
('Site E-commerce', 'Création d\'un site e-commerce complet', 6000, '2025-01-15', '2025-04-30', 1, 1, 1),
('Refonte Logo', 'Modernisation de l\'identité visuelle', 2500, '2025-02-01', '2025-02-28', 2, 2, 2),
('Application Mobile', 'Développement d\'une application mobile', 10000, '2025-03-01', '2025-06-30', 1, 3, 3),
('Audit Technique', 'Audit des systèmes existants', 3000, '2025-04-01', '2025-04-15', 3, 4, 4);

-- Insert dummy data into TACHE
INSERT INTO TACHE (LibelleTache, DateEnregTache, DateDebutTache, DateFinTache, EtatTache, IdProjet) VALUES
('Maquettage des pages', '2025-01-16', '2025-01-20', '2025-02-05', 3, 1),
('Développement Front-end', '2025-01-16', '2025-02-06', '2025-03-15', 2, 1),
('Création des propositions', '2025-02-02', '2025-02-03', '2025-02-10', 3, 2),
('Finalisation du logo', '2025-02-11', '2025-02-12', '2025-02-25', 2, 2),
('Développement Back-end', '2025-03-02', '2025-03-10', '2025-04-15', 1, 3),
('Tests et validation', '2025-04-16', '2025-04-20', '2025-05-01', 1, 3);

-- Insert dummy data into REGLEMENT
INSERT INTO REGLEMENT (DateReglement, HeureReglement, MontantReglement, IdClient) VALUES
('2025-01-15', '10:30:00', 3000, 1),
('2025-02-01', '14:45:00', 1500, 2),
('2025-03-10', '11:00:00', 5000, 3),
('2025-04-05', '09:15:00', 2000, 4);

-- Insert dummy data into AFFECTATION
INSERT INTO AFFECTATION (DateAffectation, HeureAffectation, FonctionAffectation, MatriculePersonnel, IdTache) VALUES
('2025-01-20', '09:00:00', 'Responsable maquettage', 'EMP002', 1),
('2025-02-06', '09:00:00', 'Développeur principal', 'EMP001', 2),
('2025-02-03', '09:00:00', 'Designer graphique', 'EMP002', 3),
('2025-02-12', '09:00:00', 'Designer graphique', 'EMP002', 4),
('2025-03-02', '09:00:00', 'Développeur Back-end', 'EMP001', 5),
('2025-04-16', '09:00:00', 'Testeur', 'EMP004', 6);

-- Insert dummy data into UTILISATEUR (admin123,user123,momeni@61)
INSERT INTO UTILISATEUR (NomUtilisateur, MotDePasse, Role) VALUES
('admin', '$2y$10$z8N0o7STLArlhfwpfr0Ek.u76pYsb4ChFipMdZsIk1XeeUbtZyYxe', 'admin'),
('user', '$2y$10$saL9RaiIV.4QwUPa5eVnZeKL39EXH3nWSci/pQsE0pFmdp48nfF/6', 'personnel');
('meno', '$2y$10$Nqj.C385625pOl2QcHJ2aurfyqfs1R.XP/pgfBXbKAR.FXMZnxMU2', 'personnel');