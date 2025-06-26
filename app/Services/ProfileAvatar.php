<?php

namespace App\Services;

use App\Interfaces\HasAvatar;
use Core\Constants\Constants;
use Core\Database\ActiveRecord\Model;

class ProfileAvatar
{
    /** @var array<string, mixed> $image */
    private array $image;

    /** @var Model&HasAvatar */
    private Model $model;

  /**
   * @param Model $model Model with avatar functionality
   * @param array<string, mixed> $validations
   * @phpstan-param Model&HasAvatar $model
   */
    public function __construct(
        Model $model,
        private array $validations = []
    ) {
        if (!$model instanceof HasAvatar) {
            throw new \InvalidArgumentException('Model must implement HasAvatar interface');
        }
        $this->model = $model;
    }

    public function path(): string
    {
        $avatarName = $this->model->getAvatarName();
        if ($avatarName) {
            $filePath = $this->getAbsoluteSavedFilePath();

          // Check if file exists before calling md5_file
            if (file_exists($filePath)) {
                // Generate MD5 hash of the avatar file to use as cache buster in URL
                $hash = md5_file($filePath);

                // Return the avatar URL with hash parameter to force browser to reload when file changes
                return $this->baseDir() . $avatarName . '?' . $hash;
            } else {
              // If file doesn't exist, return without hash
                return $this->baseDir() . $avatarName;
            }
        }

        return "/assets/images/defaults/avatar.png";
    }

  /**
   * @param array<string, mixed> $image
   */
    public function update(array $image): bool
    {
        $this->image = $image;

        // Validação da imagem
        if (!$this->isValidImage()) {
            return false;
        }

        try {
            // Atualiza o arquivo
            if (!$this->updateFile()) {
                return false;
            }

            // Define o nome do avatar no modelo
            $fileName = $this->getFileName();
            return $this->model->setAvatarName($fileName);
        } catch (\Exception $e) {
            // Em caso de erro, limpar qualquer arquivo que tenha sido carregado
            $this->removeOldImage();
            return false;
        }
    }

    protected function updateFile(): bool
    {
        if (empty($this->getTmpFilePath())) {
            return false;
        }

        $this->removeOldImage();

        $resp = move_uploaded_file(
            $this->getTmpFilePath(),
            $this->getAbsoluteDestinationPath()
        );

        if (!$resp) {
            $error = error_get_last();
            throw new \InvalidArgumentException(
                'Failed to move uploaded file: ' . ($error['message'] ?? 'Unknown error')
            );
        }

        return true;
    }

    private function getTmpFilePath(): string
    {
        return $this->image['tmp_name'];
    }

    /**
     * Remove old image file without updating the model
     * Used internally during file updates
     */
    public function removeOldImage(): void
    {
        $avatarName = $this->model->getAvatarName();
        if ($avatarName) {
            $filePath = $this->getAbsoluteSavedFilePath();
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }

    /**
     * Public method to remove avatar completely
     * Removes the file and updates the database
     *
     * @return bool True if removal was successful
     */
    public function remove(): bool
    {
        $avatarName = $this->model->getAvatarName();
        if (!$avatarName) {
            return false; // No avatar to remove
        }

        try {
            $filePath = $this->getAbsoluteSavedFilePath();

            // Delete the file if it exists
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Update the model to remove avatar reference
            return $this->model->setAvatarName(null);
        } catch (\Exception $e) {
            return false;
        }
    }

    private function getFileName(): string
    {
        $file_name_splitted  = explode('.', $this->image['name']);
        $file_extension = end($file_name_splitted);
      // Include model type and ID in filename to ensure uniqueness
        return $this->model::table() . '_' . $this->model->id . '_avatar.' . $file_extension;
    }

    private function getAbsoluteDestinationPath(): string
    {
        return $this->storeDir() . $this->getFileName();
    }

    private function baseDir(): string
    {
        return "/assets/uploads/";
    }

    private function storeDir(): string
    {
        $path = Constants::rootPath()->join('public' . $this->baseDir());
        if (!is_dir($path)) {
            mkdir(directory: $path, recursive: true);
        }

        return $path;
    }

    private function getAbsoluteSavedFilePath(): string
    {
        $avatarName = $this->model->getAvatarName();
        if (!$avatarName) {
            throw new \InvalidArgumentException('Avatar name is not set');
        }
        return Constants::rootPath()->join('public' . $this->baseDir())->join($avatarName);
    }

    public function isValidImage(): bool
    {
        if (isset($this->validations['extension'])) {
            $this->validateImageExtension();
        }

        if (isset($this->validations['size'])) {
            $this->validateImageSize();
        }

        return $this->model->errors('avatar') === null;
    }

    private function validateImageExtension(): void
    {
        $file_name_splitted  = explode('.', $this->image['name']);
        $file_extension = end($file_name_splitted);

        if (!in_array($file_extension, $this->validations['extension'])) {
            $this->model->addError('avatar', 'Extensão de arquivo inválida');
        }
    }

    private function validateImageSize(): void
    {
        if ($this->image['size'] > $this->validations['size']) {
            $this->model->addError('avatar', 'Tamanho do arquivo inválido');
        }
    }
}
