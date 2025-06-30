/**
 * Avatar Upload Functionality
 * Handles click-to-upload for profile avatars with auto-submit
 * Inclui validações de arquivo e feedback visual
 */
document.addEventListener("DOMContentLoaded", function () {
  const avatarImage = document.getElementById("avatar-image");
  const defaultAvatarContainer = document.getElementById("default-avatar-container");
  const fileInput = document.getElementById("avatar");
  const form = document.getElementById("avatar-form");

  if (!fileInput || !form) return;

  // Configurações de validação (manter sincronizadas com o backend)
  const validations = {
    extensions: ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'], // Deve corresponder a ProfileAvatar::DEFAULT_ALLOWED_EXTENSIONS
    types: ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp'],
    maxSize: 2 * 1024 * 1024 // 2MB - Deve corresponder a ProfileAvatar::DEFAULT_MAX_SIZE
  };

  // Formatar bytes para exibição amigável
  function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
  }

  // Adiciona feedback visual de carregamento
  function showLoadingOverlay(element) {
    // Criar overlay de carregamento
    const overlay = document.createElement('div');
    overlay.className = 'avatar-loading-overlay';
    overlay.id = 'loading-overlay';

    // Adicionar ícone de carregamento
    const spinner = document.createElement('div');
    spinner.className = 'avatar-spinner';
    overlay.appendChild(spinner);

    // Adicionar ao elemento pai
    element.appendChild(overlay);
  }

  // When clicking on the avatar image, trigger the file input
  if (avatarImage) {
    avatarImage.addEventListener("click", function () {
      fileInput.click();
    });
  }

  // When clicking on default avatar container, trigger file input too
  if (defaultAvatarContainer) {
    defaultAvatarContainer.addEventListener("click", function () {
      fileInput.click();
    });
  }

  // Função para mostrar uma mensagem de erro
  function showErrorMessage(message) {
    // Verificar se já existe uma mensagem de erro
    const existingError = document.getElementById('avatar-error-message');
    if (existingError) {
      existingError.textContent = message;
      return;
    }

    // Criar o elemento de mensagem
    const errorDiv = document.createElement('div');
    errorDiv.id = 'avatar-error-message';
    errorDiv.className = 'bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mt-2 rounded';
    errorDiv.textContent = message;

    // Inserir após o formulário
    const parentElement = form.parentNode;
    parentElement.insertBefore(errorDiv, form.nextSibling);

    // Remover a mensagem após 5 segundos
    setTimeout(() => {
      if (errorDiv.parentNode) {
        errorDiv.parentNode.removeChild(errorDiv);
      }
    }, 5000);
  }

  // Função para validar tipo de arquivo
  function isValidImageType(file) {
    return validations.types.includes(file.type);
  }

  // Função para validar tamanho de arquivo
  function isValidImageSize(file) {
    return file.size <= validations.maxSize;
  }

  // Função para obter a extensão do arquivo
  function getFileExtension(file) {
    return file.name.split('.').pop().toLowerCase();
  }

  // Handle file selection with auto-submit
  fileInput.addEventListener("change", function (event) {
    const file = event.target.files[0];
    if (file) {
      // Validar tipo de arquivo
      if (!isValidImageType(file)) {
        showErrorMessage('Por favor, selecione apenas arquivos de imagem (JPEG, PNG, GIF, SVG ou WebP).');
        fileInput.value = ''; // Limpar a seleção
        return;
      }

      // Validar tamanho de arquivo
      if (!isValidImageSize(file)) {
        showErrorMessage('O arquivo deve ter no máximo ' + formatBytes(validations.maxSize) + '.');
        fileInput.value = ''; // Limpar a seleção
        return;
      }

      // Validar extensão de arquivo
      if (!validations.extensions.includes(getFileExtension(file))) {
        showErrorMessage('Extensão de arquivo não permitida. As extensões válidas são: ' + validations.extensions.join(', ') + '.');
        fileInput.value = ''; // Limpar a seleção
        return;
      }

      // Mostrar estado de carregamento imediatamente
      const container = avatarImage ? avatarImage.closest('.avatar-container') : defaultAvatarContainer.closest('.avatar-container');
      if (container) {
        const loadingOverlay = container.querySelector('.avatar-loading-overlay');
        if (loadingOverlay) {
          loadingOverlay.classList.remove('hidden');
        }
      }

      const reader = new FileReader();
      reader.onload = function (e) {
        if (avatarImage) {
          // Se tivermos uma imagem real, atualize-a e mostre o loading
          avatarImage.src = e.target.result;
        } else if (defaultAvatarContainer) {
          // Se tivermos apenas o container padrão, transforme-o em uma imagem real
          const parentDiv = defaultAvatarContainer.parentNode;

          // Criar uma nova imagem
          const newImage = document.createElement('img');
          newImage.src = e.target.result;
          newImage.id = 'avatar-image';
          newImage.alt = 'Foto de perfil';
          newImage.className = 'avatar-image';
          newImage.title = 'Clique para alterar a foto';

          // Substituir o container padrão pela nova imagem
          parentDiv.removeChild(defaultAvatarContainer);
          parentDiv.appendChild(newImage);

          // Mostrar overlay de carregamento
          showLoadingOverlay(parentDiv);

          // Configurar o evento de clique na nova imagem
          newImage.addEventListener("click", function () {
            fileInput.click();
          });
        }

        // Enviar o formulário automaticamente após um pequeno atraso
        setTimeout(() => {
          form.submit();
        }, 500);
      };
      reader.readAsDataURL(file);
    }
  });
});
