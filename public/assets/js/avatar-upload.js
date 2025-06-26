/**
 * Avatar Upload Functionality
 * Handles click-to-upload for profile avatars with auto-submit
 */
document.addEventListener("DOMContentLoaded", function () {
  const avatarImage = document.getElementById("avatar-image");
  const defaultAvatarContainer = document.getElementById("default-avatar-container");
  const fileInput = document.getElementById("avatar");
  const form = document.getElementById("avatar-form");

  if (!fileInput || !form) return;

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

  // Handle file selection with auto-submit
  fileInput.addEventListener("change", function (event) {
    const file = event.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function (e) {
        if (avatarImage) {
          // Se tivermos uma imagem real, atualize-a e mostre o loading
          avatarImage.src = e.target.result;
          const container = avatarImage.parentNode;
          showLoadingOverlay(container);
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
