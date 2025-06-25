<?php

namespace App\Services;

use Core\Constants\Constants;
use Core\Database\ActiveRecord\Model;

/**
 * Class to handle profile avatar operations
 *
 * @property-read Model $model The model that has the avatar property
 */
class ProfileAvatar
{
    /** @var array<string, mixed> $image */
    private array $image;

    /**
     * @param Model $model The model that has the avatar_name property
     * @param array<string, mixed> $validations
     */
    public function __construct(
        private Model $model,
        private array $validations = []
    ) {
        // Ensure the model has the required properties
        if (!property_exists($model, 'avatar_name') && !isset($model->avatar_name)) {
            // For PHPStan, this is just a runtime check
            // The actual model classes should have this property defined
        }
    }

    /**
     * Get the path to the avatar image
     *
     * @return string The URL path to the avatar image
     */
    public function path(): string
    {
        if (isset($this->model->avatar_name) && $this->model->avatar_name) {
            $filePath = $this->getAbsoluteSavedFilePath();

          // Check if file exists before calling md5_file
            if (file_exists($filePath)) {
                // Generate MD5 hash of the avatar file to use as cache buster in URL
                $hash = md5_file($filePath);

                // Return the avatar URL with hash parameter to force browser to reload when file changes
                return $this->baseDir() . $this->model->avatar_name . '?' . $hash;
            } else {
              // If file doesn't exist, return without hash
                return $this->baseDir() . $this->model->avatar_name;
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

        if (!$this->isValidImage()) {
            return false;
        }

        if ($this->updateFile()) {
            $this->model->update([
            'avatar_name' => $this->getFileName(),
            ]);

            return true;
        }

        return false;
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
            throw new \RuntimeException(
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
        if (isset($this->model->avatar_name) && $this->model->avatar_name) {
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
        if (!isset($this->model->avatar_name) || !$this->model->avatar_name) {
            return false; // No avatar to remove
        }

        try {
            $filePath = $this->getAbsoluteSavedFilePath();

            // Delete the file if it exists
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Update the model to remove avatar reference
            return $this->model->update([
                'avatar_name' => null
            ]);
        } catch (\RuntimeException $e) {
            // Handle file system errors
            return false;
        } catch (\InvalidArgumentException $e) {
            // Handle validation errors
            return false;
        } catch (\Exception $e) {
            // Fallback for any other errors
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

    /**
     * Get the absolute path to the saved avatar file
     *
     * @return string The absolute file path
     */
    private function getAbsoluteSavedFilePath(): string
    {
        if (!isset($this->model->avatar_name) || !$this->model->avatar_name) {
            return Constants::rootPath()->join('public' . $this->baseDir())->join('default.png');
        }
        return Constants::rootPath()->join('public' . $this->baseDir())->join($this->model->avatar_name);
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
