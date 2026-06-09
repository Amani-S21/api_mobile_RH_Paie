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
    Password VARCHAR(100) NOT NULL,  -- Mot de passe en clair
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
INSERT IGNORE INTO Rôle (Id, Nom) VALUES 
(1, 'Administrateur'), 
(2, 'RH'), 
(3, 'Employé');

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
-- INSERT IGNORE INTO Utilisateurs (Id, UserName, Email, Password, IdRôle) VALUES 
-- (1, 'admin', 'admin@rhpaie.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

INSERT INTO Utilisateurs (Id, UserName, Email, Password, IdRôle) VALUES 
(1, 'admin', 'admin@rhpaie.com', 'admin123', 1);

-- Insertion d'employés de démonstration
INSERT IGNORE INTO Employé (Id, Matricule, Nom, PostNom, Prénom, Sexe, DateNaissance, Téléphone, Email, Poste, SalaireBase, Statut, IdDepartement) VALUES 
(1, 'EMP001', 'JUIF', 'LE', 'NOIR', 'M', '2000-05-15', '243987542634', 'juif.diallo@rhpaie.com', 'Directeur Général', 15000, 'Actif', 1),
(2, 'EMP002', 'AMANI', 'Peace', 'Fatou', 'M', '1990-10-20', '243987542635', 'peace.fall@rhpaie.com', 'Responsable RH', 8000, 'Actif', 2),
(3, 'EMP003', 'NDIAYE', 'Ndiaye', 'Moussa', 'M', '1992-03-25', '243987542636', 'moussa.ndiaye@rhpaie.com', 'Développeur', 7000, 'Actif', 3),
(4, 'EMP004', 'Merveille', 'Sow', 'Aminata', 'F', '1988-07-12', '243987542637', 'aminata.sow@rhpaie.com', 'Comptable', 7500, 'Actif', 4);

-- Insertion de primes de démonstration
INSERT IGNORE INTO Primes (IdEmployé, Libellé, Montant) VALUES 
(1, 'Prime de performance', 1500),
(1, 'Prime de transport', 500),
(2, 'Prime de responsabilité', 800),
(3, 'Prime technique', 600),
(4, 'Prime d\'ancienneté', 400);

-- Insertion de congés de démonstration
INSERT IGNORE INTO Congé (IdTypeCongé, IdEmployé, Motif, DateDébut, DateFin, Statut) VALUES 
(1, 2, 'Vacances annuelles', '2024-07-01', '2024-07-15', 'Approuvé'),
(1, 3, 'Repos', '2024-08-10', '2024-08-25', 'En attente'),
(2, 4, 'Consultation médicale', '2024-06-20', '2024-06-22', 'Approuvé'),
(1, 5, 'Voyage familial', '2024-09-01', '2024-09-14', 'En attente'),
(3, 6, 'Affaires personnelles', '2024-07-25', '2024-07-30', 'Refusé');

-- Insertion de paiements de démonstration
INSERT IGNORE INTO Paiement (IdEmployé, Mois, MontantNet, ModePaiement, DatePaiement, StatutPaiement) VALUES 
(1, '2024-01', 16000, 'Virement bancaire', '2024-01-28', 'Payé'),
(2, '2024-01', 8500, 'Virement bancaire', '2024-01-28', 'Payé'),
(3, '2024-01', 7200, 'Espèces', '2024-01-28', 'Payé'),
(4, '2024-01', 7500, 'Virement bancaire', '2024-01-28', 'Payé'),
(1, '2024-02', 16000, 'Virement bancaire', '2024-02-28', 'Payé'),
(2, '2024-02', 8500, 'Virement bancaire', '2024-02-28', 'Payé'),
(3, '2024-02', 7200, 'Espèces', '2024-02-28', 'Payé'),
(4, '2024-02', 7500, 'Virement bancaire', '2024-02-28', 'Payé');

-- Insertion de présences de démonstration (mois de juin 2024)
INSERT IGNORE INTO Présence (IdEmployé, Date, HeureArrivée, HeureSortie, Statut) VALUES 
(1, '2024-06-01', '08:30:00', '17:30:00', 'Présent'),
(2, '2024-06-01', '08:45:00', '17:30:00', 'Présent'),
(3, '2024-06-01', '09:00:00', '17:30:00', 'Retard'),
(4, '2024-06-01', '08:30:00', '17:30:00', 'Présent'),
(1, '2024-06-02', '08:30:00', '17:30:00', 'Présent'),
(2, '2024-06-02', NULL, NULL, 'Absent'),
(3, '2024-06-02', '08:30:00', '17:30:00', 'Présent'),
(4, '2024-06-02', '08:30:00', '17:30:00', 'Présent');




-- -- Recréer la table Utilisateurs (sans hash, mot de passe en clair)
-- CREATE TABLE IF NOT EXISTS Utilisateurs (
--     Id INT PRIMARY KEY AUTO_INCREMENT,
--     UserName VARCHAR(50) NOT NULL UNIQUE,
--     Email VARCHAR(100) NOT NULL UNIQUE,
--     Password VARCHAR(100) NOT NULL,  -- Mot de passe en clair
--     IdRôle INT,
--     FOREIGN KEY (IdRôle) REFERENCES Rôle(Id)
-- );

-- -- Insérer l'utilisateur admin avec mot de passe en clair
-- DELETE FROM Utilisateurs WHERE Email = 'admin@rhpaie.com';
-- INSERT INTO Utilisateurs (Id, UserName, Email, Password, IdRôle) VALUES 
-- (1, 'admin', 'admin@rhpaie.com', 'admin123', 1);
