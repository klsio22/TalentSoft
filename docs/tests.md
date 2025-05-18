# Testes Automatizados - TalentSoft

Este documento descreve a estrutura e implementação dos testes automatizados no sistema TalentSoft.

## Estrutura de Testes

Os testes foram organizados em diferentes categorias:

1. **Testes de Aceitação**: Simulam interações do usuário com o sistema através da interface web.

2. **Testes Unitários**: Testam componentes individuais do sistema em isolamento.

3. **Análise Estática**: Verificação de qualidade de código e padrões de codificação.

## Ferramentas Utilizadas

- **PHPUnit**: Framework para testes unitários
- **Codeception**: Framework para testes de aceitação/end-to-end
- **PHPStan**: Ferramenta de análise estática para PHP
- **PHP CodeSniffer**: Verificação de padrões de código

## Testes Implementados

### Testes de Aceitação (`tests/Acceptance/`)

#### Autenticação (`Auth/AuthenticationCest.php`)

- Acesso à página de login
- Login para cada tipo de usuário (admin, HR, user)
- Login com credenciais inválidas
- Processo de logout

#### Controle de Acesso (`Access/AccessRestrictionCest.php`)

- Acesso a páginas restritas sem autenticação
- Restrições de acesso para usuários comuns
- Restrições de acesso para usuários de RH
- Acesso completo para administradores

#### Mensagens Flash (`UI/FlashMessagesCest.php`)

- Mensagens de sucesso no login
- Mensagens de erro em login inválido
- Mensagens de logout
- Mensagens de acesso negado
- Comportamento de auto-fade (verificação dos elementos HTML)

### Testes Unitários (`tests/Unit/`)

#### Modelo Employee (`Models/EmployeeTest.php`)

- Criação de funcionário
- Busca de funcionário por email
- Autenticação de funcionário
- Verificações de papel (admin, HR, user)

#### Sistema de Autenticação (`Lib/AuthTest.php`)

- Login e verificação de usuário
- Logout
- Verificações de papel do usuário autenticado

#### Sistema de Mensagens Flash (`Lib/FlashMessageTest.php`)

- Mensagens de sucesso, erro, aviso e informativas
- Obtenção e limpeza de mensagens

## Como Executar os Testes

Para facilitar a execução dos testes, foi criado um script shell que executa todas as verificações:

```bash
./run_tests.sh
```

Este script executa sequencialmente:

1. Análise estática com PHPStan
2. Verificação de padrões de código com PHP CodeSniffer
3. Testes unitários com PHPUnit
4. Testes de aceitação com Codeception (requer Docker em execução)

Para executar categorias específicas de testes:

```bash
# Apenas testes unitários
vendor/bin/phpunit --testsuite unit

# Apenas testes de aceitação
vendor/bin/codecept run acceptance

# Apenas análise estática
vendor/bin/phpstan analyse --level=5
vendor/bin/phpcs --standard=phpcs.xml

# Testes específicos
vendor/bin/codecept run acceptance Auth/AuthenticationCest
vendor/bin/phpunit tests/Unit/Models/EmployeeTest.php
```

## Usuários de Teste

Os testes são executados com os seguintes usuários:

- **Admin**: klesio@admin.com / 123456
- **RH**: caio@rh.com / 123456
- **Usuário comum**: flavio@user.com / 123456

## Boas Práticas

1. **Independência**: Cada teste é independente e não depende de resultados de outros testes.
2. **Banco de Dados**: Os testes criam e destroem o banco de dados para garantir um ambiente limpo.
3. **Cobertura**: Os testes cobrem os principais aspectos do sistema: autenticação, controle de acesso e feedback ao usuário.
4. **Manutenção**: Ao modificar o código, atualize os testes correspondentes.
