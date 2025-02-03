CREATE TABLE IF NOT EXISTS roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS employees (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  cpf VARCHAR(14) UNIQUE NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(20),
  birth_date DATE,
  role_id INT,
  salary DECIMAL(10, 2),
  hire_date DATE,
  status ENUM ('Active', 'Inactive') DEFAULT 'Active',
  termination_date DATE,
  termination_reason VARCHAR(255),
  address_street VARCHAR(255),
  address_number VARCHAR(10),
  address_complement VARCHAR(100),
  address_neighborhood VARCHAR(100),
  address_city VARCHAR(100),
  address_state VARCHAR(2),
  address_zipcode VARCHAR(10),
  nationality VARCHAR(50),
  marital_status ENUM ('Single', 'Married', 'Divorced', 'Widowed'),
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (role_id) REFERENCES roles (id)
);
CREATE TABLE IF NOT EXISTS projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  start_date DATE,
  end_date DATE,
  status ENUM ('In Progress', 'Completed', 'Canceled') DEFAULT 'In Progress',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS employees_projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  employee_id INT,
  project_id INT,
  role VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (employee_id) REFERENCES employees (id),
  FOREIGN KEY (project_id) REFERENCES projects (id)
);
CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  employee_id INT,
  type ENUM (
    'New Registration',
    'Termination',
    'Project Assignment',
    'Pending Approvals'
  ),
  message TEXT,
  sent_date DATE,
  status ENUM ('Read', 'Unread') DEFAULT 'Unread',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (employee_id) REFERENCES employees (id)
);
CREATE TABLE IF NOT EXISTS approvals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  employee_id INT,
  project_id INT,
  status ENUM ('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
  request_date DATE,
  approval_date DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (employee_id) REFERENCES employees (id),
  FOREIGN KEY (project_id) REFERENCES projects (id)
);
CREATE TABLE IF NOT EXISTS projects_employees_report (
  id INT AUTO_INCREMENT PRIMARY KEY,
  project_id INT,
  employee_id INT,
  report_date DATE,
  role VARCHAR(100),
  project_status ENUM ('In Progress', 'Completed', 'Canceled'),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (project_id) REFERENCES projects (id),
  FOREIGN KEY (employee_id) REFERENCES employees (id)
);
