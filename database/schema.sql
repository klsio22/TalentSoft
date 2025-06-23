SET
  foreign_key_checks = 0;

-- Tabela de papéis/roles
DROP TABLE IF EXISTS Roles;

CREATE TABLE
  Roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT
  );

-- Tabela de funcionários
DROP TABLE IF EXISTS Employees;

CREATE TABLE
  Employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    cpf VARCHAR(14) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    birth_date DATE,
    role_id INT NOT NULL,
    salary DECIMAL(10, 2),
    hire_date DATE NOT NULL,
    status ENUM ('Active', 'Inactive') DEFAULT 'Active',
    address VARCHAR(255),
    city VARCHAR(100),
    state VARCHAR(100),
    zipcode VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    avatar_name VARCHAR(255),
    FOREIGN KEY (role_id) REFERENCES Roles (id)
  );

-- Tabela de credenciais de usuários
DROP TABLE IF EXISTS UserCredentials;

CREATE TABLE
  UserCredentials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES Employees (id)
  );

-- Tabela de projetos
DROP TABLE IF EXISTS Projects;

CREATE TABLE
  Projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    start_date DATE,
    end_date DATE,
    status ENUM (
      'Em aberto', 
      'Em teste', 
      'Interno', 
      'Em andamento', 
      'Em aprovação cliente', 
      'Em aprovação interna', 
      'Em revisão', 
      'Em cache', 
      'Em espera', 
      'Cancelado', 
      'Em pausa', 
      'Concluído', 
      'Colocar em produção', 
      'Em Produção'
    ) DEFAULT 'Em aberto',
    budget DECIMAL(10, 2) DEFAULT 0.00
  );

-- Tabela de relacionamento entre funcionários e projetos
DROP TABLE IF EXISTS Employee_Projects;

CREATE TABLE
  Employee_Projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    project_id INT NOT NULL,
    role VARCHAR(100),
    FOREIGN KEY (employee_id) REFERENCES Employees (id),
    FOREIGN KEY (project_id) REFERENCES Projects (id)
  );

-- Tabela de usuários
DROP TABLE IF EXISTS Users;

CREATE TABLE
  Users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    encrypted_password VARCHAR(255) NOT NULL,
    avatar_name VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  );

-- Tabela de notificações para funcionários
DROP TABLE IF EXISTS Notifications;

CREATE TABLE
  Notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    type ENUM (
      'Registration',
      'Termination',
      'Project',
      'Approval'
    ) NOT NULL,
    message TEXT NOT NULL,
    sent_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM ('Read', 'Unread') DEFAULT 'Unread',
    FOREIGN KEY (employee_id) REFERENCES Employees (id)
  );

-- Tabela de aprovações foi removida
-- O controle de acesso agora é feito através do campo status na tabela Employees
-- status ENUM ('Active', 'Inactive') DEFAULT 'Active'

-- Inserir papéis padrão
INSERT INTO
  Roles (name, description)
VALUES
  (
    'admin',
    'Administrador com acesso completo ao sistema'
  ),
  (
    'hr',
    'Recursos humanos com acesso a funções de RH'
  ),
  ('user', 'Usuário comum com acesso limitado');

SET
  foreign_key_checks = 1;
