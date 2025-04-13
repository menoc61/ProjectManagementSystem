CREATE DATABASE IF NOT EXISTS GestionProjetsEntreprise;
USE GestionProjetsEntreprise;

-- TABLE CLIENT
CREATE TABLE CLIENT (
    IdClient INT AUTO_INCREMENT PRIMARY KEY,
    NomClient VARCHAR(100),
    AdresseClient VARCHAR(255),
    EmailClient VARCHAR(100),
    TelClient VARCHAR(20)
);

-- TABLE TYPEPROJET
CREATE TABLE TYPEPROJET (
    IdTypeProjet INT AUTO_INCREMENT PRIMARY KEY,
    LibelleTypeProjet VARCHAR(100),
    ForfaitCoutTypeProjet INT
);

-- TABLE PROJET
CREATE TABLE PROJET (
    IdProjet INT AUTO_INCREMENT PRIMARY KEY,
    TitreProjet VARCHAR(150),
    DescriptionProjet TEXT,
    CoutProjet INT,
    DateDebutProjet DATE,
    DateFinProjet DATE,
    EtatProjet INT,
    IdClient INT,
    IdTypeProjet INT,
    FOREIGN KEY (IdClient) REFERENCES CLIENT(IdClient),
    FOREIGN KEY (IdTypeProjet) REFERENCES TYPEPROJET(IdTypeProjet)
);

-- TABLE TACHE
CREATE TABLE TACHE (
    IdTache INT AUTO_INCREMENT PRIMARY KEY,
    LibelleTache VARCHAR(150),
    DateEnregTache DATE,
    DateDebutTache DATE,
    DateFinTache DATE,
    EtatTache INT,
    IdProjet INT,
    FOREIGN KEY (IdProjet) REFERENCES PROJET(IdProjet)
);

-- TABLE REGLEMENT
CREATE TABLE REGLEMENT (
    IdReglement INT AUTO_INCREMENT PRIMARY KEY,
    DateReglement DATE,
    HeureReglement TIME,
    MontantReglement INT,
    IdClient INT,
    FOREIGN KEY (IdClient) REFERENCES CLIENT(IdClient)
);

-- TABLE SERVICE
CREATE TABLE SERVICE (
    CodeService VARCHAR(20) PRIMARY KEY,
    LibelleService VARCHAR(100)
);

-- TABLE PERSONNEL
CREATE TABLE PERSONNEL (
    MatriculePersonnel VARCHAR(20) PRIMARY KEY,
    NomPersonnel VARCHAR(100),
    PrenomPersonnel VARCHAR(100),
    EmailPersonnel VARCHAR(100),
    TelPersonnel VARCHAR(20),
    CodeService VARCHAR(20),
    FOREIGN KEY (CodeService) REFERENCES SERVICE(CodeService)
);

-- TABLE AFFECTATION
CREATE TABLE AFFECTATION (
    IdAffectation INT AUTO_INCREMENT PRIMARY KEY,
    DateAffectation DATE,
    HeureAffectation TIME,
    FonctionAffectation VARCHAR(100),
    MatriculePersonnel VARCHAR(20),
    IdTache INT,
    FOREIGN KEY (MatriculePersonnel) REFERENCES PERSONNEL(MatriculePersonnel),
    FOREIGN KEY (IdTache) REFERENCES TACHE(IdTache)
);
