<?php

namespace Tests\Unit\Services;

use App\Interfaces\HasAvatar;
use App\Models\Employee;
use App\Services\ProfileAvatar;
use Core\Constants\Constants;
use Core\Database\ActiveRecord\Model;
use Tests\TestCase;

/**
 * Testes para o serviço ProfileAvatar
 */
class ProfileAvatarTest extends TestCase
{
    /**
     * O mock do modelo Employee para testes
     *
     * @var \PHPUnit\Framework\MockObject\MockObject&Employee&\App\Interfaces\HasAvatar
     */
    private $modelMock;

    public function setUp(): void
    {
        parent::setUp();

        // Criar um mock para o Model que implementa HasAvatar
        $this->modelMock = $this->getMockBuilder(Employee::class)
            ->onlyMethods(['getAvatarName', 'table', 'errors', 'addError'])
            ->getMock();

        // Configurar métodos básicos do mock
        $this->modelMock->method('getAvatarName')->willReturn(null);
        $this->modelMock->id = 123;
        $this->modelMock->method('table')->willReturn('Employees');
    }

    /**
     * Testa se o construtor do ProfileAvatar aceita um modelo válido
     */
    public function testConstructorAcceptsValidModel(): void
    {
        // Usar uma declaração de tipo para ajudar o PHPStan a entender que o mock implementa HasAvatar
        /** @var \App\Interfaces\HasAvatar&\Core\Database\ActiveRecord\Model $model */
        $model = $this->modelMock;
        $profileAvatar = new ProfileAvatar($model);
        $this->assertInstanceOf(ProfileAvatar::class, $profileAvatar);
    }

    /**
     * Testa se o construtor lança exceção quando o modelo não implementa HasAvatar
     */
    public function testConstructorThrowsExceptionWhenModelDoesntImplementHasAvatar(): void
    {
        $invalidModelMock = $this->createMock(Model::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Model must implement HasAvatar interface');

        // @phpstan-ignore-next-line - Estamos testando intencionalmente um erro de tipo
        new ProfileAvatar($invalidModelMock);
    }

    /**
     * Testa o método update com arquivo inválido
     */
    public function testUpdateWithInvalidFile(): void
    {
        // Configurar o mock para retornar mensagem de erro
        $this->modelMock->method('errors')
            ->with('avatar')
            ->willReturn('O arquivo enviado não é uma imagem válida');

        // Criar uma instância de ProfileAvatar com o mock
        /** @var \App\Interfaces\HasAvatar&\Core\Database\ActiveRecord\Model $model */
        $model = $this->modelMock;
        $profileAvatar = $this->getMockBuilder(ProfileAvatar::class)
            ->setConstructorArgs([$model])
            ->onlyMethods(['isValidImage'])
            ->getMock();

        // Mock para isValidImage retornar false (simulando falha na validação)
        $profileAvatar->method('isValidImage')->willReturn(false);

        // Executar o método update com um arquivo inválido simulado
        $result = $profileAvatar->update([
            'name' => 'fake.pdf',
            'type' => 'application/pdf',
            'tmp_name' => '/tmp/test.pdf',
            'error' => UPLOAD_ERR_OK,
            'size' => 1024 * 1024
        ]);

        // O resultado deve ser false indicando falha no upload
        $this->assertFalse($result, 'Update deve falhar com arquivo inválido');
    }

    /**
     * Testa a rejeição de arquivos que não são imagens através do método update
     */
    public function testValidateRejectsNonImageFile(): void
    {
        // Configurar o mock para verificar a chamada ao método addError
        $this->modelMock->expects($this->once())
            ->method('addError')
            ->with(
                $this->equalTo('avatar'),
                $this->stringContains('imagem válida')
            );

        // Configurar o mock para retornar mensagem de erro quando errors() for chamado
        $this->modelMock->method('errors')
            ->with('avatar')
            ->willReturn('O arquivo enviado não é uma imagem válida');

        // Criar arquivo de teste de texto (não é uma imagem válida)
        $tmpFile = tempnam(sys_get_temp_dir(), 'test_invalid_');
        file_put_contents($tmpFile, 'Este não é um arquivo de imagem válido');

        try {
            // Criar instância real do ProfileAvatar para testar a validação real
            /** @var \App\Interfaces\HasAvatar&\Core\Database\ActiveRecord\Model $model */
            $model = $this->modelMock;
            $profileAvatar = new ProfileAvatar($model);

            // Executar o método update com dados de um arquivo de texto
            $result = $profileAvatar->update([
                'name' => 'texto.jpg', // Disfarçado como JPG
                'type' => 'text/plain',
                'tmp_name' => $tmpFile,
                'error' => UPLOAD_ERR_OK,
                'size' => filesize($tmpFile)
            ]);

            // Verificar que o resultado é false (falha na validação)
            $this->assertFalse($result, 'O arquivo de texto não deve passar na validação de imagem');
        } finally {
            // Limpeza do arquivo temporário
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
        }
    }

    /**
     * Testa se um arquivo grande é rejeitado pelo método update
     */
    public function testRejectsOversizedFile(): void
    {
        // Configurar validações com tamanho máximo pequeno
        $validations = [
            'extension' => ['jpg', 'jpeg', 'png'], // Extensões permitidas
            'size' => 1024 // Tamanho máximo de 1KB
        ];

        // Configurar o mock para verificar mensagem sobre tamanho
        $this->modelMock->expects($this->atLeastOnce())
            ->method('addError')
            ->with(
                $this->equalTo('avatar'),
                $this->callback(function ($message) {
                    return strpos($message, 'tamanho máximo') !== false;
                })
            );

        // Criar arquivo temporário maior que o limite
        $tmpFile = tempnam(sys_get_temp_dir(), 'test_large_');
        file_put_contents($tmpFile, str_repeat('X', 2048)); // 2KB > 1KB limite

        try {
            // Simular upload de um arquivo JPG com tamanho maior que o permitido
            // Como o arquivo real é um arquivo texto, vamos garantir que o tipo MIME
            // seja tratado corretamente criando um mock específico para este teste
            $fileData = [
                'name' => 'large_file.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => $tmpFile,
                'error' => UPLOAD_ERR_OK,
                'size' => filesize($tmpFile) // Maior que o tamanho máximo
            ];

            // Usar update diretamente, que internamente chama validateImageSize
            // Configurar errors para retornar mensagem de erro quando consultado
            $this->modelMock->method('errors')
                ->with('avatar')
                ->willReturn('O arquivo excede o tamanho máximo permitido');

            // Em vez de criar mock da classe, vamos usar reflexão para acessar e testar diretamente o método validateImageSize
            /** @var \App\Interfaces\HasAvatar&\Core\Database\ActiveRecord\Model $model */
            $model = $this->modelMock;
            $profileAvatar = new ProfileAvatar($model, $validations);

            // Usar reflexão para definir a propriedade image e chamar diretamente validateImageSize
            $reflection = new \ReflectionObject($profileAvatar);

            // Definir a propriedade image
            $imageProp = $reflection->getProperty('image');
            $imageProp->setAccessible(true);
            $imageProp->setValue($profileAvatar, $fileData);

            // Acessar diretamente o método validateImageSize
            $validateImageSize = $reflection->getMethod('validateImageSize');
            $validateImageSize->setAccessible(true);
            $validateImageSize->invoke($profileAvatar);

            // Não precisamos mais verificar o resultado, apenas se addError foi chamado
            // O método PHPUnit->expects já fará essa verificação quando o teste terminar
        } finally {
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
        }
    }

    /**
     * Testa diretamente o método validateImageExtension
     */
    public function testRejectsInvalidExtension(): void
    {
        // Criar ProfileAvatar com restrição de extensões
        /** @var \App\Interfaces\HasAvatar&\Core\Database\ActiveRecord\Model $model */
        $model = $this->modelMock;
        $profileAvatar = new ProfileAvatar(
            $model,
            ['extension' => ['jpg', 'png']] // Apenas JPG e PNG permitidos
        );

        // Testar que o método addError é chamado com mensagem apropriada
        $this->modelMock->expects($this->once())
            ->method('addError')
            ->with(
                $this->equalTo('avatar'),
                $this->callback(function ($message) {
                    return strpos($message, 'Extensão de arquivo inválida') !== false;
                })
            );

        // Método direto para chamar validateImageExtension após setar a imagem
        $setImageAndValidateExtension = function ($profileAvatar, $fileName) {
            $reflection = new \ReflectionObject($profileAvatar);

            // Definir a propriedade image
            $imageProp = $reflection->getProperty('image');
            $imageProp->setAccessible(true);
            $imageProp->setValue($profileAvatar, [
                'name' => $fileName,
                'type' => 'image/gif',
                'tmp_name' => '/tmp/test',
                'error' => UPLOAD_ERR_OK,
                'size' => 1024
            ]);

            // Chamar o método privado validateImageExtension
            $method = $reflection->getMethod('validateImageExtension');
            $method->setAccessible(true);
            $method->invoke($profileAvatar);
        };

        // Executar o teste com extensão inválida (.gif)
        $setImageAndValidateExtension($profileAvatar, 'test.gif');
    }

    /**
     * Testa a formatação de bytes
     */
    public function testFormatBytes(): void
    {
        /** @var \App\Interfaces\HasAvatar&\Core\Database\ActiveRecord\Model $model */
        $model = $this->modelMock;
        $profileAvatar = new ProfileAvatar($model);

        // Usar reflexão para acessar método privado
        $reflection = new \ReflectionClass(ProfileAvatar::class);
        $method = $reflection->getMethod('formatBytes');
        $method->setAccessible(true);

        // Testar diferentes valores
        $this->assertEquals('1 KB', $method->invoke($profileAvatar, 1024));
        $this->assertEquals('1 MB', $method->invoke($profileAvatar, 1048576));
        $this->assertEquals('1.5 MB', $method->invoke($profileAvatar, 1572864));
        $this->assertEquals('0 B', $method->invoke($profileAvatar, 0));
    }
}
