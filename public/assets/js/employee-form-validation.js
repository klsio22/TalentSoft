/**
 * Employee Form Validation System
 * Handles input masks, real-time validation, and form submission validation
 */
document.addEventListener('DOMContentLoaded', function () {
  applyInputMasks();
  setupPreventiveValidation();
});

/**
 * Apply input masks for CPF, ZIP code, and salary formatting
 */
function applyInputMasks() {
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

  // Currency formatting for salary field
  const salaryInput = document.getElementById('salary');
  if (salaryInput) {
    function applyCurrencyMask(value) {
      let numbers = value.replace(/\D/g, '');
      if (numbers.length === 0) return '';

      let formatted = (parseInt(numbers) / 100).toFixed(2);
      formatted = formatted.replace('.', ',');
      formatted = formatted.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
      return formatted;
    }

    salaryInput.addEventListener('input', function () {
      const cursorPosition = this.selectionStart;
      const oldLength = this.value.length;

      this.value = applyCurrencyMask(this.value);

      const newLength = this.value.length;
      const lengthDiff = newLength - oldLength;
      this.setSelectionRange(
        cursorPosition + lengthDiff,
        cursorPosition + lengthDiff,
      );
    });

    salaryInput.addEventListener('focus', function () {
      if (!this.value) return;
      setTimeout(() => this.select(), 0);
    });

    salaryInput.addEventListener('blur', function () {
      if (this.value) {
        this.value = applyCurrencyMask(this.value);
      }
    });
  }
}

/**
 * Setup form validation system
 */
function setupFormValidation() {
  const form = document.querySelector('form');

  if (form) {
    const isCreating = window.location.href.includes('create');
    const touchedFields = new Set();

    form.addEventListener('submit', function (event) {
      const allInputs = form.querySelectorAll('input, select, textarea');
      allInputs.forEach((input) => {
        if (input.id) touchedFields.add(input.id);
      });

      updateCustomValidation(touchedFields);

      let requiredFields = [
        'name',
        'cpf',
        'email',
        'role_id',
        'birth_date',
        'salary',
        'hire_date',
      ];

      if (isCreating) {
        requiredFields.push('password', 'password_confirmation');
      } else {
        const hasCredential = document.querySelector(
          '.bg-blue-50.border-blue-200',
        );
        const passwordField = document.getElementById('password');
        const passwordConfField = document.getElementById(
          'password_confirmation',
        );

        if (!hasCredential && passwordField && passwordConfField) {
          if (passwordField.hasAttribute('required')) {
            requiredFields.push('password', 'password_confirmation');
          }
        }
      }

      let hasError = false;

      requiredFields.forEach((fieldName) => {
        const field = document.getElementById(fieldName);
        if (field && !field.value.trim()) {
          field.classList.add('border-red-300');

          let errorMsg = field.nextElementSibling;
          if (!errorMsg || !errorMsg.classList.contains('text-red-600')) {
            errorMsg = document.createElement('p');
            errorMsg.className = 'mt-1 text-sm text-red-600 absolute';
            errorMsg.textContent = 'Este campo é obrigatório';
            field.parentNode.insertBefore(errorMsg, field.nextSibling);
          }

          hasError = true;
        }
      });

      if (!form.checkValidity() || hasError) {
        event.preventDefault();
        event.stopPropagation();
      }

      form.classList.add('was-validated');
    });

    const inputs = form.querySelectorAll('input, select, textarea');
    inputs.forEach((input) => {
      input.addEventListener('focus', function () {
        if (this.id) touchedFields.add(this.id);
      });

      input.addEventListener('input', function () {
        if (touchedFields.has(this.id)) {
          this.classList.remove('border-red-300');
          const errorMsg = this.nextElementSibling;
          if (errorMsg && errorMsg.classList.contains('text-red-600')) {
            errorMsg.remove();
          }
        }
      });

      input.addEventListener('blur', function () {
        if (touchedFields.has(this.id)) {
          const currentFieldSet = new Set([this.id]);
          updateCustomValidation(currentFieldSet);
        }
      });
    });
  }
}

/**
 * Run validation checks for CPF, email, and required fields
 */
function updateCustomValidation(touchedFields = null) {
  validateCPFField(touchedFields);
  validateEmailField(touchedFields);
  validateRequiredFields(touchedFields);
}

/**
 * Validate CPF using Brazilian algorithm
 */
function validateCPFField(touchedFields = null) {
  const cpfInput = document.getElementById('cpf');
  if (cpfInput && (!touchedFields || touchedFields.has('cpf'))) {
    if (cpfInput.value && !isValidCPF(cpfInput.value)) {
      cpfInput.setCustomValidity('Formato de CPF inválido');
      showFieldError(cpfInput, 'CPF inválido');
    } else {
      cpfInput.setCustomValidity('');
      clearFieldError(cpfInput);
    }
  }
}

/**
 * Validate email format
 */
function validateEmailField(touchedFields = null) {
  const emailInput = document.getElementById('email');
  if (emailInput && (!touchedFields || touchedFields.has('email'))) {
    if (emailInput.value && !isValidEmail(emailInput.value)) {
      emailInput.setCustomValidity('Formato de email inválido');
      showFieldError(emailInput, 'Email inválido');
    } else {
      emailInput.setCustomValidity('');
      clearFieldError(emailInput);
    }
  }
}

/**
 * Validate required fields based on form type
 */
function validateRequiredFields(touchedFields = null) {
  const isCreating = window.location.href.includes('create');

  let requiredFields = [
    'name',
    'email',
    'cpf',
    'role_id',
    'birth_date',
    'salary',
    'hire_date',
  ];

  if (isCreating) {
    requiredFields.push('password', 'password_confirmation');
  } else {
    const passwordField = document.getElementById('password');
    const passwordConfField = document.getElementById('password_confirmation');

    if (passwordField && passwordField.hasAttribute('required')) {
      requiredFields.push('password');
    }
    if (passwordConfField && passwordConfField.hasAttribute('required')) {
      requiredFields.push('password_confirmation');
    }
  }

  requiredFields.forEach((fieldId) => {
    const field = document.getElementById(fieldId);
    if (field && (!touchedFields || touchedFields.has(fieldId))) {
      if (!field.value.trim()) {
        field.setCustomValidity('Este campo é obrigatório');
      } else {
        field.setCustomValidity('');
      }
    }
  });
}

/**
 * Validate Brazilian CPF using official algorithm
 */
function isValidCPF(cpf) {
  cpf = cpf.replace(/[^\d]/g, '');

  if (cpf.length !== 11) return false;

  if (/^(\d)\1{10}$/.test(cpf)) return false;

  let sum = 0;
  for (let i = 1; i <= 9; i++) {
    sum += parseInt(cpf.substring(i - 1, i)) * (11 - i);
  }

  let remainder = (sum * 10) % 11;
  if (remainder === 10 || remainder === 11) remainder = 0;
  if (remainder !== parseInt(cpf.substring(9, 10))) return false;

  sum = 0;
  for (let i = 1; i <= 10; i++) {
    sum += parseInt(cpf.substring(i - 1, i)) * (12 - i);
  }

  remainder = (sum * 10) % 11;
  if (remainder === 10 || remainder === 11) remainder = 0;
  return remainder === parseInt(cpf.substring(10, 11));
}

/**
 * Validate email format using standard regex pattern
 */
function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}

/**
 * Validate salary field - must be a positive number
 */
function isValidSalary(salary) {
  if (!salary.trim()) return false;

  const cleanSalary = salary.replace(/[R$\s]/g, '');

  if (!/^[\d,.]+$/.test(cleanSalary)) return false;

  const numericValue = parseFloat(
    cleanSalary.replace(/\./g, '').replace(',', '.'),
  );
  return !isNaN(numericValue) && numericValue >= 0;
}

/**
 * Display error message for a specific field
 */
function showFieldError(field, errorMessage) {
  field.classList.add('border-red-300');
  field.style.borderColor = '#f87171';

  clearFieldError(field);

  const errorElem = document.createElement('div');
  errorElem.className = 'error-message mt-1 text-sm text-red-600 absolute';
  errorElem.style.color = '#dc2626';
  errorElem.textContent = errorMessage;

  field.parentNode.insertBefore(errorElem, field.nextSibling);
}

/**
 * Remove error styling and messages from a field
 */
function clearFieldError(field) {
  field.classList.remove('border-red-300');
  field.style.borderColor = '';

  const errorMessages = field.parentNode.querySelectorAll('.error-message');
  errorMessages.forEach((elem) => elem.remove());

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
 * Handle password field validation when confirmation field changes
 */
function validatePasswordConfirmation(fieldId, touchedFields) {
  if (fieldId === 'password_confirmation') {
    const passwordField = document.getElementById('password');
    if (passwordField && touchedFields.has('password')) {
      validateFieldWithTouchedCheck(passwordField, touchedFields);
    }
  }
}

/**
 * Handle password confirmation field validation when password field changes
 */
function validatePasswordField(fieldId, touchedFields) {
  if (fieldId === 'password') {
    const passwordConfField = document.getElementById('password_confirmation');
    if (passwordConfField && touchedFields.has('password_confirmation')) {
      validateFieldWithTouchedCheck(passwordConfField, touchedFields);
    }
  }
}

/**
 * Handle input validation for password fields
 */
function handlePasswordInput(field, fieldId, touchedFields) {
  validateFieldWithTouchedCheck(field, touchedFields);
  validatePasswordConfirmation(fieldId, touchedFields);
  validatePasswordField(fieldId, touchedFields);
}

/**
 * Handle input validation for non-password fields
 */
function handleNonPasswordInput(field, touchedFields) {
  if (field.value.trim()) {
    clearFieldError(field);
  } else {
    validateFieldWithTouchedCheck(field, touchedFields);
  }
}

/**
 * Setup real-time validation for form fields
 */
function setupPreventiveValidation() {
  const form = document.querySelector('form');
  if (!form) return;

  const isCreating =
    window.location.href.includes('create') ||
    window.location.href.includes('test-validation') ||
    window.location.href.includes('test-debug');

  let requiredFields = [
    'name',
    'cpf',
    'email',
    'role_id',
    'birth_date',
    'salary',
    'hire_date',
  ];

  if (isCreating) {
    requiredFields.push('password', 'password_confirmation');
  }

  const touchedFields = new Set();

  requiredFields.forEach((fieldId) => {
    const field = document.getElementById(fieldId);
    if (!field) return;

    field.addEventListener('focus', function () {
      touchedFields.add(fieldId);
    });

    field.addEventListener('blur', function () {
      if (touchedFields.has(fieldId)) {
        validateFieldWithTouchedCheck(this, touchedFields);
      }
    });

    if (
      field.type === 'text' ||
      field.type === 'email' ||
      field.type === 'password' ||
      field.type === 'date'
    ) {
      field.addEventListener('input', function () {
        if (touchedFields.has(fieldId)) {
          if (field.type === 'password') {
            handlePasswordInput(this, fieldId, touchedFields);
          } else {
            handleNonPasswordInput(this, touchedFields);
          }
        }
      });
    }

    if (field.tagName === 'SELECT') {
      field.addEventListener('change', function () {
        touchedFields.add(fieldId);
        validateFieldWithTouchedCheck(this, touchedFields);
      });
    }
  });

  form.addEventListener('submit', function (event) {
    requiredFields.forEach((fieldId) => {
      touchedFields.add(fieldId);
    });

    if (!validateAllRequiredFieldsWithTouchedCheck(touchedFields)) {
      event.preventDefault();
      showGeneralAlert();
    }
  });
}

/**
 * Validate a specific field considering which fields have been touched
 */
function validateFieldWithTouchedCheck(field, touchedFields) {
  if (!touchedFields.has(field.id)) {
    return true;
  }

  let isValid = true;
  let errorMessage = '';

  if (field.hasAttribute('required') && !field.value.trim()) {
    isValid = false;
    errorMessage = 'Este campo é obrigatório';
  }

  if (isValid) {
    const validationResult = validateSpecificField(field);
    isValid = validationResult.isValid;
    if (!isValid) {
      errorMessage = validationResult.errorMessage;
    }
  }

  if (!isValid) {
    showFieldError(field, errorMessage);
  } else {
    clearFieldError(field);
  }

  return isValid;
}

/**
 * Validate all required fields considering which ones have been touched
 */
function validateAllRequiredFieldsWithTouchedCheck(touchedFields) {
  const isCreating = window.location.href.includes('create');

  let requiredFields = [
    'name',
    'cpf',
    'email',
    'role_id',
    'birth_date',
    'salary',
    'hire_date',
  ];

  if (isCreating) {
    requiredFields.push('password', 'password_confirmation');
  } else {
    const passwordField = document.getElementById('password');
    const passwordConfField = document.getElementById('password_confirmation');

    if (passwordField && passwordField.hasAttribute('required')) {
      requiredFields.push('password');
    }
    if (passwordConfField && passwordConfField.hasAttribute('required')) {
      requiredFields.push('password_confirmation');
    }
  }

  let allValid = true;

  requiredFields.forEach((fieldId) => {
    const field = document.getElementById(fieldId);
    if (field && !validateFieldWithTouchedCheck(field, touchedFields)) {
      allValid = false;
    }
  });

  return allValid;
}

/**
 * Show general alert for unfilled required fields
 */
function showGeneralAlert() {
  const isCreating = window.location.href.includes('create');

  let requiredFields = [
    { id: 'name', label: 'Nome Completo' },
    { id: 'cpf', label: 'CPF' },
    { id: 'email', label: 'Email' },
    { id: 'role_id', label: 'Cargo' },
    { id: 'birth_date', label: 'Data de Nascimento' },
    { id: 'salary', label: 'Salário' },
    { id: 'hire_date', label: 'Data de Contratação' },
  ];

  if (isCreating) {
    requiredFields.push(
      { id: 'password', label: 'Senha' },
      { id: 'password_confirmation', label: 'Confirmação de Senha' },
    );
  } else {
    const passwordField = document.getElementById('password');
    const passwordConfField = document.getElementById('password_confirmation');

    if (passwordField && passwordField.hasAttribute('required')) {
      requiredFields.push({ id: 'password', label: 'Senha' });
    }
    if (passwordConfField && passwordConfField.hasAttribute('required')) {
      requiredFields.push({
        id: 'password_confirmation',
        label: 'Confirmação de Senha',
      });
    }
  }

  const emptyFields = [];

  requiredFields.forEach((field) => {
    const element = document.getElementById(field.id);
    if (element && !element.value.trim()) {
      emptyFields.push(field.label);
    }
  });

  if (emptyFields.length > 0) {
    const fieldsHtml = emptyFields
      .map(
        (field) =>
          `<div style="display: flex; align-items: center; gap: 8px; margin: 5px 0;">
        <span style="color: #f59e0b;">•</span>
        <span>${field}</span>
      </div>`,
      )
      .join('');

    const message = `
      <div style="text-align: left;">
        <p style="margin-bottom: 15px; font-weight: 500;">Por favor, preencha os seguintes campos obrigatórios:</p>
        ${fieldsHtml}
      </div>
    `;

    if (typeof showCustomModal === 'function') {
      showCustomModal('Campos Obrigatórios', message, 'warning');
    } else {
      const fieldsText = emptyFields.join(', ');
      alert(
        `Por favor, preencha os seguintes campos obrigatórios:\n\n${fieldsText}`,
      );
    }
  }
}

/**
 * Validate CPF field
 */
function validateCPFSpecific(field) {
  if (!isValidCPF(field.value)) {
    return { isValid: false, errorMessage: 'CPF inválido' };
  }
  return { isValid: true, errorMessage: '' };
}

/**
 * Validate email field
 */
function validateEmailSpecific(field) {
  if (!isValidEmail(field.value)) {
    return { isValid: false, errorMessage: 'Email inválido' };
  }
  return { isValid: true, errorMessage: '' };
}

/**
 * Validate password field
 */
function validatePasswordSpecific(field) {
  const isCreating = window.location.href.includes('create');

  if (isCreating && !field.value.trim()) {
    return {
      isValid: false,
      errorMessage: 'Senha é obrigatória',
    };
  }

  if (field.value.trim() && field.value.length < 6) {
    return {
      isValid: false,
      errorMessage: 'Senha deve ter pelo menos 6 caracteres',
    };
  }

  return { isValid: true, errorMessage: '' };
}

/**
 * Validate password confirmation field
 */
function validatePasswordConfirmationSpecific(field) {
  const passwordField = document.getElementById('password');
  const isCreating = window.location.href.includes('create');

  if (isCreating && !field.value.trim()) {
    return {
      isValid: false,
      errorMessage: 'Confirmação de senha é obrigatória',
    };
  }

  if (passwordField && (field.value || passwordField.value)) {
    if (field.value !== passwordField.value) {
      return { isValid: false, errorMessage: 'Senhas não coincidem' };
    }
  }

  return { isValid: true, errorMessage: '' };
}

/**
 * Validate salary field
 */
function validateSalarySpecific(field) {
  if (!isValidSalary(field.value)) {
    return {
      isValid: false,
      errorMessage:
        'Formato de salário inválido. Use apenas números, vírgulas e pontos.',
    };
  }
  return { isValid: true, errorMessage: '' };
}

/**
 * Validate specific fields based on field ID
 */
function validateSpecificField(field) {
  switch (field.id) {
    case 'cpf':
      return validateCPFSpecific(field);
    case 'email':
      return validateEmailSpecific(field);
    case 'password':
      return validatePasswordSpecific(field);
    case 'password_confirmation':
      return validatePasswordConfirmationSpecific(field);
    case 'salary':
      return validateSalarySpecific(field);
    default:
      return { isValid: true, errorMessage: '' };
  }
}
