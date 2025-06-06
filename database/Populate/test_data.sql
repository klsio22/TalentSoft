-- Script para inserir dados de teste para paginação
-- Execute este script no MySQL para criar dados suficientes para testar a paginação
-- Primeiro, garantir que temos roles suficientes
INSERT IGNORE INTO Roles (name, description)
VALUES
    ('developer', 'Desenvolvedor'),
    ('manager', 'Gerente'),
    ('analyst', 'Analista'),
    ('designer', 'Designer'),
    ('tester', 'Testador');

-- Inserir funcionários de teste (mais de 10 para testar paginação)
INSERT INTO
    Employees (
        name,
        cpf,
        email,
        birth_date,
        role_id,
        salary,
        hire_date,
        status,
        address,
        city,
        state,
        zipcode,
        notes
    )
VALUES
    (
        'João Silva Santos',
        '123.456.789-01',
        'joao.silva@teste.com',
        '1990-01-15',
        1,
        5000.00,
        '2023-01-10',
        'Active',
        'Rua A, 123',
        'São Paulo',
        'SP',
        '01234-567',
        'Funcionário de teste'
    ),
    (
        'Maria Oliveira Costa',
        '234.567.890-12',
        'maria.oliveira@teste.com',
        '1985-03-22',
        2,
        7000.00,
        '2022-05-15',
        'Active',
        'Rua B, 456',
        'Rio de Janeiro',
        'RJ',
        '12345-678',
        'Funcionário de teste'
    ),
    (
        'Pedro Santos Lima',
        '345.678.901-23',
        'pedro.santos@teste.com',
        '1992-07-30',
        1,
        4500.00,
        '2023-03-20',
        'Active',
        'Rua C, 789',
        'Belo Horizonte',
        'MG',
        '23456-789',
        'Funcionário de teste'
    ),
    (
        'Ana Costa Ferreira',
        '456.789.012-34',
        'ana.costa@teste.com',
        '1988-11-12',
        3,
        5500.00,
        '2022-08-10',
        'Active',
        'Rua D, 321',
        'Porto Alegre',
        'RS',
        '34567-890',
        'Funcionário de teste'
    ),
    (
        'Carlos Lima Souza',
        '567.890.123-45',
        'carlos.lima@teste.com',
        '1995-05-18',
        4,
        4800.00,
        '2023-06-01',
        'Active',
        'Rua E, 654',
        'Salvador',
        'BA',
        '45678-901',
        'Funcionário de teste'
    ),
    (
        'Fernanda Rocha Alves',
        '678.901.234-56',
        'fernanda.rocha@teste.com',
        '1987-09-25',
        2,
        6500.00,
        '2022-12-05',
        'Active',
        'Rua F, 987',
        'Fortaleza',
        'CE',
        '56789-012',
        'Funcionário de teste'
    ),
    (
        'Ricardo Pereira Martins',
        '789.012.345-67',
        'ricardo.pereira@teste.com',
        '1991-02-14',
        1,
        5200.00,
        '2023-02-28',
        'Active',
        'Rua G, 147',
        'Curitiba',
        'PR',
        '67890-123',
        'Funcionário de teste'
    ),
    (
        'Juliana Almeida Ribeiro',
        '890.123.456-78',
        'juliana.almeida@teste.com',
        '1989-12-08',
        5,
        4700.00,
        '2022-11-15',
        'Active',
        'Rua H, 258',
        'Recife',
        'PE',
        '78901-234',
        'Funcionário de teste'
    ),
    (
        'Bruno Martins Castro',
        '901.234.567-89',
        'bruno.martins@teste.com',
        '1993-04-03',
        3,
        5300.00,
        '2023-04-12',
        'Active',
        'Rua I, 369',
        'Goiânia',
        'GO',
        '89012-345',
        'Funcionário de teste'
    ),
    (
        'Camila Silva Dias',
        '012.345.678-90',
        'camila.silva@teste.com',
        '1986-08-20',
        1,
        4900.00,
        '2022-09-20',
        'Active',
        'Rua J, 741',
        'Belém',
        'PA',
        '90123-456',
        'Funcionário de teste'
    ),
    (
        'Eduardo Santos Nunes',
        '123.456.789-02',
        'eduardo.santos@teste.com',
        '1994-06-15',
        2,
        6200.00,
        '2023-05-10',
        'Active',
        'Rua K, 852',
        'Manaus',
        'AM',
        '01234-568',
        'Funcionário de teste'
    ),
    (
        'Patricia Lima Barbosa',
        '234.567.890-13',
        'patricia.lima@teste.com',
        '1990-10-28',
        4,
        4600.00,
        '2022-07-25',
        'Active',
        'Rua L, 963',
        'Brasília',
        'DF',
        '12345-679',
        'Funcionário de teste'
    ),
    (
        'Rodrigo Silva Moura',
        '345.678.901-24',
        'rodrigo.silva@teste.com',
        '1987-01-10',
        1,
        5100.00,
        '2023-01-05',
        'Active',
        'Rua M, 159',
        'Florianópolis',
        'SC',
        '23456-790',
        'Funcionário de teste'
    ),
    (
        'Luciana Costa Teixeira',
        '456.789.012-35',
        'luciana.costa@teste.com',
        '1992-03-17',
        3,
        5400.00,
        '2022-10-30',
        'Active',
        'Rua N, 357',
        'Vitória',
        'ES',
        '34567-801',
        'Funcionário de teste'
    ),
    (
        'Felipe Oliveira Cardoso',
        '567.890.123-46',
        'felipe.oliveira@teste.com',
        '1985-11-05',
        2,
        6800.00,
        '2022-06-18',
        'Active',
        'Rua O, 468',
        'João Pessoa',
        'PB',
        '45678-912',
        'Funcionário de teste'
    ),
    (
        'Gabriela Santos Cavalcanti',
        '678.901.234-57',
        'gabriela.santos@teste.com',
        '1996-07-22',
        5,
        4400.00,
        '2023-07-08',
        'Active',
        'Rua P, 579',
        'Aracaju',
        'SE',
        '56789-023',
        'Funcionário de teste'
    ),
    (
        'Thiago Pereira Mota',
        '789.012.345-68',
        'thiago.pereira@teste.com',
        '1989-12-30',
        1,
        5000.00,
        '2022-12-12',
        'Active',
        'Rua Q, 680',
        'Teresina',
        'PI',
        '67890-134',
        'Funcionário de teste'
    ),
    (
        'Renata Alves Borges',
        '890.123.456-79',
        'renata.alves@teste.com',
        '1993-09-14',
        3,
        5250.00,
        '2023-03-15',
        'Active',
        'Rua R, 791',
        'Natal',
        'RN',
        '78901-245',
        'Funcionário de teste'
    ),
    (
        'Diego Costa Soares',
        '901.234.567-80',
        'diego.costa@teste.com',
        '1988-05-07',
        4,
        4850.00,
        '2022-08-22',
        'Active',
        'Rua S, 802',
        'Maceió',
        'AL',
        '89012-356',
        'Funcionário de teste'
    ),
    (
        'Amanda Silva Paiva',
        '012.345.678-91',
        'amanda.silva@teste.com',
        '1991-01-25',
        2,
        6300.00,
        '2023-02-14',
        'Active',
        'Rua T, 913',
        'Campo Grande',
        'MS',
        '90123-467',
        'Funcionário de teste'
    ),
    (
        'Lucas Martins Macedo',
        '123.456.789-03',
        'lucas.martins@teste.com',
        '1986-08-11',
        1,
        4950.00,
        '2022-11-08',
        'Active',
        'Rua U, 024',
        'Cuiabá',
        'MT',
        '01234-578',
        'Funcionário de teste'
    ),
    (
        'Priscila Santos Torres',
        '234.567.890-14',
        'priscila.santos@teste.com',
        '1994-04-19',
        5,
        4650.00,
        '2023-06-20',
        'Active',
        'Rua V, 135',
        'Palmas',
        'TO',
        '12345-689',
        'Funcionário de teste'
    ),
    (
        'Gustavo Lima Reis',
        '345.678.901-25',
        'gustavo.lima@teste.com',
        '1990-12-02',
        3,
        5350.00,
        '2022-09-05',
        'Active',
        'Rua W, 246',
        'Boa Vista',
        'RR',
        '23456-701',
        'Funcionário de teste'
    ),
    (
        'Tatiana Oliveira Galvão',
        '456.789.012-36',
        'tatiana.oliveira@teste.com',
        '1987-06-26',
        2,
        6100.00,
        '2023-04-30',
        'Active',
        'Rua X, 357',
        'Macapá',
        'AP',
        '34567-812',
        'Funcionário de teste'
    ),
    (
        'Marcelo Costa Antunes',
        '567.890.123-47',
        'marcelo.costa@teste.com',
        '1995-02-13',
        1,
        5050.00,
        '2022-12-20',
        'Active',
        'Rua Y, 468',
        'Rio Branco',
        'AC',
        '45678-923',
        'Funcionário de teste'
    );