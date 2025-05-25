/**
 * Validações para o formulário de funcionários
 */
document.addEventListener('DOMContentLoaded', function () {
  // Aplicar máscaras
  applyInputMasks();

  // Aplicar validações ao formulário
  setupFormValidation();

  // Adicionar validação preventiva em tempo real
  setupPreventiveValidation();
});

/**
 * Aplica máscaras de entrada para campos específicos
 */
function applyInputMasks() {
  // Máscara para CPF
  const cpfInput = document.getElementById('cpf');
  if (cpfInput) {
    cpfInput.addEventListener('input', function () {
      let value = this.value.replace(/\D/g, '');
      if (value.length <= 11) {
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        this.value = value;
      }
    });
  }

  // Máscara para CEP
  const cepInput = document.getElementById('zipcode');
  if (cepInput) {
    cepInput.addEventListener('input', function () {
      let value = this.value.replace(/\D/g, '');
      if (value.length <= 8) {
        value = value.replace(/(\d{5})(\d)/, '$1-$2');
        this.value = value;
      }
    });
  }

  // Máscara para Salário
  const salaryInput = document.getElementById('salary');
  if (salaryInput) {
    salaryInput.addEventListener('input', function () {
      let value = this.value.replace(/\D/g, '');
      if (value.length > 0) {
        // Formatar como moeda brasileira
        value = (parseInt(value) / 100).toFixed(2);
        value = value.replace('.', ',');
        value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        this.value = value;
      }
    });

    // Ao focar, limpar formatação para edição
    salaryInput.addEventListener('focus', function () {
      if (this.value) {
        let value = this.value.replace(/[^\d,]/g, '');
        this.value = value;
      }
    });

    // Ao perder foco, aplicar formatação
    salaryInput.addEventListener('blur', function () {
      if (this.value) {
        let value = this.value.replace(/\D/g, '');
        if (value.length > 0) {
          value = (parseInt(value) / 100).toFixed(2);
          value = value.replace('.', ',');
          value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
          this.value = value;
        }
      }
    });
  }
}

/**
 * Configura validação de formulário
 */
function setupFormValidation() {
  const form = document.querySelector('form');

  if (form) {
    // Adicionar validação personalizada no envio do formulário
    form.addEventListener('submit', function (event) {
      // Executar validações personalizadas
      updateCustomValidation();

      // Validar campos obrigatórios
      const requiredFields = [
        'name',
        'cpf',
        'email',
        'role_id',
        'birth_date',
        'salary',
        'hire_date',
        'password',
        'password_confirmation',
      ];
      let hasError = false;

      requiredFields.forEach((fieldName) => {
        const field = document.getElementById(fieldName);
        if (field && !field.value.trim()) {
          field.classList.add('border-red-300');

          // Adicionar mensagem de erro se ainda não existir
          let errorMsg = field.nextElementSibling;
          if (!errorMsg || !errorMsg.classList.contains('text-red-600')) {
            errorMsg = document.createElement('p');
            errorMsg.className = 'mt-1 text-sm text-red-600';
            errorMsg.textContent = 'Este campo é obrigatório';
            field.parentNode.insertBefore(errorMsg, field.nextSibling);
          }

          hasError = true;
        }
      });

      // Verificar se o formulário é válido
      if (!form.checkValidity() || hasError) {
        event.preventDefault();
        event.stopPropagation();
      }

      form.classList.add('was-validated');
    });

    // Validar em tempo real para melhor experiência do usuário
    const inputs = form.querySelectorAll('input, select, textarea');
    inputs.forEach((input) => {
      // Remover mensagens de erro quando o usuário começa a digitar
      input.addEventListener('input', function () {
        this.classList.remove('border-red-300');
        const errorMsg = this.nextElementSibling;
        if (errorMsg && errorMsg.classList.contains('text-red-600')) {
          errorMsg.remove();
        }
      });

      input.addEventListener('blur', updateCustomValidation);
    });
  }
}

/**
 * Atualiza as validações personalizadas
 */
function updateCustomValidation() {
  validateCPFField();
  validateEmailField();
  validateRequiredFields();
  validatePasswordFields();
}

/**
 * Valida campo CPF
 */
function validateCPFField() {
  const cpfInput = document.getElementById('cpf');
  if (cpfInput) {
    if (cpfInput.value && !isValidCPF(cpfInput.value)) {
      cpfInput.setCustomValidity('CPF inválido');
      showFieldError(cpfInput, 'CPF inválido');
    } else {
      cpfInput.setCustomValidity('');
      clearFieldError(cpfInput);
    }
  }
}

/**
 * Valida campo Email
 */
function validateEmailField() {
  const emailInput = document.getElementById('email');
  if (emailInput) {
    if (emailInput.value && !isValidEmail(emailInput.value)) {
      emailInput.setCustomValidity('Email inválido');
      showFieldError(emailInput, 'Email inválido');
    } else {
      emailInput.setCustomValidity('');
      clearFieldError(emailInput);
    }
  }
}

/**
 * Valida campos obrigatórios
 */
function validateRequiredFields() {
  const requiredFields = [
    'name',
    'email',
    'cpf',
    'role_id',
    'birth_date',
    'salary',
    'hire_date',
  ];
  requiredFields.forEach((fieldId) => {
    const field = document.getElementById(fieldId);
    if (field && !field.value.trim()) {
      field.setCustomValidity('Campo obrigatório');
    } else if (field) {
      field.setCustomValidity('');
    }
  });
}

/**
 * Valida campos de senha
 */
function validatePasswordFields() {
  const password = document.getElementById('password');
  const passwordConfirmation = document.getElementById('password_confirmation');

  if (password && passwordConfirmation) {
    const isCreating = window.location.href.includes('create');

    // Verificar correspondência de senhas
    if (
      (isCreating || (password.value && passwordConfirmation.value)) &&
      password.value !== passwordConfirmation.value
    ) {
      passwordConfirmation.setCustomValidity('As senhas não correspondem');
      showFieldError(passwordConfirmation, 'As senhas não correspondem');
    } else {
      passwordConfirmation.setCustomValidity('');
      clearFieldError(passwordConfirmation);
    }

    // Verificar senha obrigatória na criação
    if (isCreating && !password.value) {
      password.setCustomValidity('A senha é obrigatória');
      showFieldError(password, 'A senha é obrigatória');
    } else if (password.value && password.value.length < 6) {
      password.setCustomValidity('A senha deve ter pelo menos 6 caracteres');
      showFieldError(password, 'A senha deve ter pelo menos 6 caracteres');
    } else {
      password.setCustomValidity('');
      clearFieldError(password);
    }
  }
}

/**
 * Valida se um CPF é válido
 */
function isValidCPF(cpf) {
  // Remove caracteres não numéricos
  cpf = cpf.replace(/[^\d]/g, '');

  // Verifica se tem 11 dígitos
  if (cpf.length !== 11) return false;

  // Verifica se todos os dígitos são iguais
  if (/^(\d)\1{10}$/.test(cpf)) return false;

  // Validação de dígitos verificadores
  let sum = 0;
  let remainder;

  // Primeiro dígito verificador
  for (let i = 1; i <= 9; i++) {
    sum += parseInt(cpf.substring(i - 1, i)) * (11 - i);
  }

  remainder = (sum * 10) % 11;
  if (remainder === 10 || remainder === 11) remainder = 0;
  if (remainder !== parseInt(cpf.substring(9, 10))) return false;

  // Segundo dígito verificador
  sum = 0;
  for (let i = 1; i <= 10; i++) {
    sum += parseInt(cpf.substring(i - 1, i)) * (12 - i);
  }

  remainder = (sum * 10) % 11;
  if (remainder === 10 || remainder === 11) remainder = 0;
  return remainder === parseInt(cpf.substring(10, 11));
}

/**
 * Valida se um email é válido
 */
function isValidEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email);
}

/**
 * Valida se um salário é válido
 */
function isValidSalary(salary) {
  if (!salary.trim()) return false;

  // Permitir apenas números, vírgulas, pontos e espaços
  const cleanSalary = salary.replace(/[R$\s]/g, '');

  // Verificar se contém apenas números, vírgulas e pontos
  if (!/^[\d,.]+$/.test(cleanSalary)) return false;

  // Verificar se é um número válido após limpeza
  const numericValue = parseFloat(
    cleanSalary.replace(/\./g, '').replace(',', '.'),
  );
  return !isNaN(numericValue) && numericValue >= 0;
}

/**
 * Mostra uma mensagem de erro para um campo
 */
function showFieldError(field, errorMessage) {
  // Adicionar borda vermelha
  field.classList.add('border-red-300');

  // Verificar se já existe uma mensagem de erro
  let errorElem = field.nextElementSibling;
  if (errorElem && errorElem.tagName === 'SMALL') {
    // Se for um small de ajuda, procure o próximo elemento
    errorElem = errorElem.nextElementSibling;
  }

  if (!errorElem || !errorElem.classList.contains('text-red-600')) {
    // Criar novo elemento de erro se não existir
    errorElem = document.createElement('p');
    errorElem.className = 'mt-1 text-sm text-red-600';

    // Adicionar após o campo
    if (field.nextElementSibling) {
      field.parentNode.insertBefore(
        errorElem,
        field.nextElementSibling.nextSibling,
      );
    } else {
      field.parentNode.appendChild(errorElem);
    }
  }

  // Atualizar a mensagem
  errorElem.textContent = errorMessage;
}

/**
 * Remove a mensagem de erro de um campo
 */
function clearFieldError(field) {
  field.classList.remove('border-red-300');

  // Procurar pela mensagem de erro
  let currentElem = field.nextElementSibling;
  while (currentElem) {
    if (
      currentElem.classList &&
      currentElem.classList.contains('text-red-600')
    ) {
      currentElem.remove();
      break;
    }
    currentElem = currentElem.nextElementSibling;
  }
}

/**
 * Configura validação preventiva em tempo real
 */
function setupPreventiveValidation() {
  const form = document.querySelector('form');
  if (!form) return;

  // Validar campos obrigatórios conforme o usuário digita
  const requiredFields = [
    'name',
    'cpf',
    'email',
    'role_id',
    'birth_date',
    'salary',
    'hire_date',
    'password',
    'password_confirmation',
  ];

  requiredFields.forEach((fieldId) => {
    const field = document.getElementById(fieldId);
    if (!field) return;

    // Validação quando o campo perde o foco
    field.addEventListener('blur', function () {
      validateField(this);
    });

    // Validação em tempo real para campos de texto
    if (
      field.type === 'text' ||
      field.type === 'email' ||
      field.type === 'password' ||
      field.type === 'date'
    ) {
      field.addEventListener('input', function () {
        // Remover erro se o campo não estiver mais vazio
        if (this.value.trim()) {
          clearFieldError(this);
        }
      });
    }

    // Validação para select
    if (field.tagName === 'SELECT') {
      field.addEventListener('change', function () {
        validateField(this);
      });
    }
  });

  // Interceptar tentativa de envio para mostrar alerta geral
  form.addEventListener('submit', function (event) {
    if (!validateAllRequiredFields()) {
      event.preventDefault();
      showGeneralAlert();
    }
  });
}

/**
 * Valida um campo específico
 */
function validateField(field) {
  let isValid = true;
  let errorMessage = '';

  // Verificar se é campo obrigatório
  if (field.hasAttribute('required') && !field.value.trim()) {
    isValid = false;
    errorMessage = 'Este campo é obrigatório';
  }

  // Validações específicas por tipo de campo
  if (field.value.trim() && isValid) {
    const validationResult = validateSpecificField(field);
    isValid = validationResult.isValid;
    errorMessage = validationResult.errorMessage;
  }

  // Mostrar ou limpar erro
  if (!isValid) {
    showFieldError(field, errorMessage);
  } else {
    clearFieldError(field);
  }

  return isValid;
}

/**
 * Valida campos específicos baseado no ID
 */
function validateSpecificField(field) {
  switch (field.id) {
    case 'cpf': {
      if (!isValidCPF(field.value)) {
        return { isValid: false, errorMessage: 'CPF inválido' };
      }
      break;
    }

    case 'email': {
      if (!isValidEmail(field.value)) {
        return { isValid: false, errorMessage: 'Email inválido' };
      }
      break;
    }

    case 'password': {
      const isCreating = window.location.href.includes('create');
      if (isCreating && field.value.length < 6) {
        return {
          isValid: false,
          errorMessage: 'A senha deve ter pelo menos 6 caracteres',
        };
      }
      break;
    }

    case 'password_confirmation': {
      const passwordField = document.getElementById('password');
      if (passwordField && field.value !== passwordField.value) {
        return { isValid: false, errorMessage: 'As senhas não correspondem' };
      }
      break;
    }

    case 'salary': {
      if (!isValidSalary(field.value)) {
        return {
          isValid: false,
          errorMessage:
            'Formato de salário inválido. Use apenas números, vírgulas e pontos.',
        };
      }
      break;
    }
  }

  return { isValid: true, errorMessage: '' };
}

/**
 * Valida todos os campos obrigatórios
 */
function validateAllRequiredFields() {
  const requiredFields = [
    'name',
    'cpf',
    'email',
    'role_id',
    'birth_date',
    'salary',
    'hire_date',
    'password',
    'password_confirmation',
  ];
  let allValid = true;

  requiredFields.forEach((fieldId) => {
    const field = document.getElementById(fieldId);
    if (field && !validateField(field)) {
      allValid = false;
    }
  });

  return allValid;
}

/**
 * Mostra alerta geral para campos obrigatórios não preenchidos
 */
function showGeneralAlert() {
  const requiredFields = [
    { id: 'name', label: 'Nome completo' },
    { id: 'cpf', label: 'CPF' },
    { id: 'email', label: 'Email' },
    { id: 'role_id', label: 'Cargo' },
    { id: 'birth_date', label: 'Data de nascimento' },
    { id: 'salary', label: 'Salário' },
    { id: 'hire_date', label: 'Data de contratação' },
    { id: 'password', label: 'Senha' },
    { id: 'password_confirmation', label: 'Confirmação de senha' },
  ];

  requiredFields.forEach((field) => {
    const element = document.getElementById(field.id);
    if (element && !element.value.trim()) {
      emptyFields.push(field.label);
    }
  });
}
