/**
 * Validações para o formulário de funcionários
 */
document.addEventListener('DOMContentLoaded', function () {
  // Aplicar máscaras
  applyInputMasks();

  // Aplicar validações ao formulário
  setupFormValidation();
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
}

/**
 * Configura validação de formulário
 */
function setupFormValidation() {
  const form = document.querySelector('.needs-validation');

  if (form) {
    // Adicionar validação personalizada no envio do formulário
    form.addEventListener('submit', function (event) {
      updateCustomValidation();

      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }

      form.classList.add('was-validated');
    });

    // Validar em tempo real para melhor experiência do usuário
    const inputs = form.querySelectorAll('input, select, textarea');
    inputs.forEach((input) => {
      input.addEventListener('blur', updateCustomValidation);
      input.addEventListener('input', updateCustomValidation);
    });
  }
}

/**
 * Atualiza as validações personalizadas
 */
function updateCustomValidation() {
  // Validação CPF
  const cpfInput = document.getElementById('cpf');
  if (cpfInput && cpfInput.value) {
    if (!isValidCPF(cpfInput.value)) {
      cpfInput.setCustomValidity('CPF inválido');
    } else {
      cpfInput.setCustomValidity('');
    }
  }

  // Validação Email
  const emailInput = document.getElementById('email');
  if (emailInput && emailInput.value) {
    if (!isValidEmail(emailInput.value)) {
      emailInput.setCustomValidity('Email inválido');
    } else {
      emailInput.setCustomValidity('');
    }
  }

  // Validação de senhas correspondentes
  const password = document.getElementById('password');
  const passwordConfirmation = document.getElementById('password_confirmation');

  if (password && passwordConfirmation) {
    // Na criação, senha é obrigatória
    const isCreating = window.location.href.includes('create');

    // Se estamos criando ou ambos os campos tem valor, verificar correspondência
    if (
      (isCreating || (password.value && passwordConfirmation.value)) &&
      password.value !== passwordConfirmation.value
    ) {
      passwordConfirmation.setCustomValidity('As senhas não correspondem');
    } else {
      passwordConfirmation.setCustomValidity('');
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
  if (remainder !== parseInt(cpf.substring(10, 11))) return false;

  return true;
}

/**
 * Valida se um email é válido
 */
function isValidEmail(email) {
  const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
  return re.test(email);
}
