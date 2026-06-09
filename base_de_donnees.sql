-- Création de la base de données
CREATE DATABASE IF NOT EXISTS rh_paie_db;
USE rh_paie_db;

-- Table Rôle
CREATE TABLE IF NOT EXISTS Rôle (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    Nom VARCHAR(50) NOT NULL
);

-- Table Utilisateurs
CREATE TABLE IF NOT EXISTS Utilisateurs (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    UserName VARCHAR(50) NOT NULL UNIQUE,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    IdRôle INT,
    FOREIGN KEY (IdRôle) REFERENCES Rôle(Id)
);

-- Table Département
CREATE TABLE IF NOT EXISTS Departement (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    Nom VARCHAR(100) NOT NULL,
    Description TEXT
);

-- Table Employé
CREATE TABLE IF NOT EXISTS Employé (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    Matricule VARCHAR(50) NOT NULL UNIQUE,
    Nom VARCHAR(50) NOT NULL,
    PostNom VARCHAR(50),
    Prénom VARCHAR(50) NOT NULL,
    Sexe ENUM('M', 'F') NOT NULL,
    DateNaissance DATE NOT NULL,
    Téléphone VARCHAR(20),
    Email VARCHAR(100) UNIQUE,
    Poste VARCHAR(100) NOT NULL,
    SalaireBase DECIMAL(10,2) NOT NULL,
    Statut ENUM('Actif', 'Inactif', 'En congé') DEFAULT 'Actif',
    IdUtilisateur INT,
    IdDepartement INT,
    FOREIGN KEY (IdUtilisateur) REFERENCES Utilisateurs(Id),
    FOREIGN KEY (IdDepartement) REFERENCES Departement(Id)
);

-- Table Dossiers
CREATE TABLE IF NOT EXISTS Dossiers (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    IdEmployé INT NOT NULL,
    CV VARCHAR(255),
    Contrat VARCHAR(255),
    DateEnregistrement DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (IdEmployé) REFERENCES Employé(Id)
);

-- Table Primes
CREATE TABLE IF NOT EXISTS Primes (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    IdEmployé INT NOT NULL,
    Libellé VARCHAR(100) NOT NULL,
    Montant DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (IdEmployé) REFERENCES Employé(Id)
);

-- Table TypeCongé
CREATE TABLE IF NOT EXISTS TypeCongé (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    Designation VARCHAR(100) NOT NULL
);

-- Table Congé
CREATE TABLE IF NOT EXISTS Congé (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    IdTypeCongé INT NOT NULL,
    IdEmployé INT NOT NULL,
    Motif TEXT,
    DateDébut DATE NOT NULL,
    DateFin DATE NOT NULL,
    Statut ENUM('En attente', 'Approuvé', 'Refusé') DEFAULT 'En attente',
    DateDemande DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (IdTypeCongé) REFERENCES TypeCongé(Id),
    FOREIGN KEY (IdEmployé) REFERENCES Employé(Id)
);

-- Table Retenues
CREATE TABLE IF NOT EXISTS Retenues (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    Libellé VARCHAR(100) NOT NULL,
    Montant DECIMAL(10,2) NOT NULL
);

-- Table Paiement
CREATE TABLE IF NOT EXISTS Paiement (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    IdEmployé INT NOT NULL,
    Mois VARCHAR(7) NOT NULL,
    MontantNet DECIMAL(10,2) NOT NULL,
    ModePaiement ENUM('Espèces', 'Virement bancaire', 'Chèque') NOT NULL,
    DatePaiement DATE NOT NULL,
    StatutPaiement ENUM('Payé', 'En attente', 'Annulé') DEFAULT 'En attente',
    FOREIGN KEY (IdEmployé) REFERENCES Employé(Id)
);

-- Table Présence
CREATE TABLE IF NOT EXISTS Présence (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    IdEmployé INT NOT NULL,
    Date DATE NOT NULL,
    HeureArrivée TIME,
    HeureSortie TIME,
    Statut ENUM('Présent', 'Absent', 'Retard') DEFAULT 'Présent',
    FOREIGN KEY (IdEmployé) REFERENCES Employé(Id),
    UNIQUE KEY unique_presence (IdEmployé, Date)
);

-- Insertion des données initiales
INSERT IGNORE INTO Rôle (Id, Nom) VALUES (1, 'Administrateur'), (2, 'RH'), (3, 'Employé');

INSERT IGNORE INTO TypeCongé (Id, Designation) VALUES 
(1, 'Congé annuel'),
(2, 'Congé maladie'),
(3, 'Congé sans solde'),
(4, 'Congé maternité');

INSERT IGNORE INTO Retenues (Id, Libellé, Montant) VALUES 
(1, 'CNSS', 500),
(2, 'INPP', 100),
(3, 'Assurance maladie', 300);

INSERT IGNORE INTO Departement (Id, Nom, Description) VALUES 
(1, 'Direction', 'Direction générale'),
(2, 'Ressources Humaines', 'Gestion du personnel'),
(3, 'Informatique', 'Développement et infrastructure'),
(4, 'Comptabilité', 'Gestion financière'),
(5, 'Marketing', 'Communication et publicité');

-- Insertion d'un utilisateur admin (mot de passe: admin123)
INSERT IGNORE INTO Utilisateurs (Id, UserName, Email, Password, IdRôle) VALUES 
(1, 'admin', 'admin@rhpaie.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- Insertion d'employés de démonstration
INSERT IGNORE INTO Employé (Id, Matricule, Nom, PostNom, Prénom, Sexe, DateNaissance, Téléphone, Email, Poste, SalaireBase, Statut, IdDepartement) VALUES 
(1, 'EMP001', 'JUIF', 'LE', 'Noir', 'M', '2014-05-15', '+243987542634', 'juif.diallo@rhpaie.com', 'Directeur Général', 15000, 'Actif', 1),