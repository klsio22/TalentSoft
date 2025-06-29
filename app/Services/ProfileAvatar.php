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
     * Constantes para validação de arquivos de avatar
     */
    public const DEFAULT_ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
    public const DEFAULT_MAX_SIZE = 2097152; // 2MB (2 * 1024 * 1024)

    /**
     * Mapa de extensões para tipos MIME
     * @var array<string, string>
     */
    private const MIME_MAP = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'webp' => 'image/webp'
    ];

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

        // Se não foram fornecidas validações, usa os valores padrão
        if (empty($this->validations)) {
            $this->validations = [
                'extension' => self::DEFAULT_ALLOWED_EXTENSIONS,
                'size' => self::DEFAULT_MAX_SIZE
            ];
        } else {
            // Garante que as chaves necessárias estão definidas
            if (!isset($this->validations['extension'])) {
                $this->validations['extension'] = self::DEFAULT_ALLOWED_EXTENSIONS;
            }
            if (!isset($this->validations['size'])) {
                $this->validations['size'] = self::DEFAULT_MAX_SIZE;
            }
        }
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
        $result = false;

        // Realizar todas as validações através do método isValidImage
        if ($this->isValidImage()) {
            try {
                // Atualiza o arquivo
                if ($this->updateFile()) {
                    // Define o nome do avatar no modelo
                    $fileName = $this->getFileName();
                    $result = $this->model->setAvatarName($fileName);
                }
            } catch (\Exception $e) {
                // Em caso de erro, limpar qualquer arquivo que tenha sido carregado
                $this->removeOldImage();
                $this->model->addError('avatar', 'Erro ao processar o arquivo: ' . $e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Valida se o arquivo é realmente uma imagem usando o tipo MIME
     *
     * @return bool True se o arquivo for uma imagem válida
     */
    private function validateMimeType(): bool
    {
        // Verifica se o arquivo existe
        if (!isset($this->image['tmp_name']) || empty($this->image['tmp_name'])) {
            return false;
        }

        // Gera lista de tipos MIME permitidos a partir das extensões de arquivo permitidas
        $allowedMimes = [];
        foreach ($this->validations['extension'] as $ext) {
            if (isset(self::MIME_MAP[$ext])) {
                $allowedMimes[] = self::MIME_MAP[$ext];
            }
        }

        // Se não houver tipos MIME permitidos, usa tipos padrão
        if (empty($allowedMimes)) {
            $allowedMimes = array_values(self::MIME_MAP);
        }

        // Verifica o tipo MIME real do arquivo
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($this->image['tmp_name']);

        return in_array($mimeType, $allowedMimes);
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
        // Verificar tipo MIME primeiro
        if (!$this->validateMimeType()) {
            $this->model->addError('avatar', 'O arquivo enviado não é uma imagem válida. Por favor, envie apenas imagens.');
            return false;
        }

        // Validar extensão
        if (isset($this->validations['extension'])) {
            $this->validateImageExtension();
        }

        // Validar tamanho
        if (isset($this->validations['size'])) {
            $this->validateImageSize();
        }

        // Verifica se houve erros de validação
        return $this->model->errors('avatar') === null;
    }

    private function validateImageExtension(): void
    {
        $file_name_splitted  = explode('.', $this->image['name']);
        $file_extension = strtolower(end($file_name_splitted));

        if (!in_array($file_extension, $this->validations['extension'])) {
            $extensoes = implode(', ', $this->validations['extension']);
            $this->model->addError('avatar', "Extensão de arquivo inválida. Extensões permitidas: {$extensoes}");
        }
    }

    private function validateImageSize(): void
    {
        if ($this->image['size'] > $this->validations['size']) {
            $maxSize = $this->formatBytes($this->validations['size']);
            $this->model->addError('avatar', "O arquivo excede o tamanho máximo permitido ({$maxSize})");
        }
    }

    /**
     * Formata bytes para unidades legíveis (KB, MB, etc)
     *
     * @param int $bytes Número de bytes
     * @param int $precision Precisão decimal
     * @return string Valor formatado com unidade
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
