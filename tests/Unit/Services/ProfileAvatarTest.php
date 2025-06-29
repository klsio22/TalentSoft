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
     * @var \PHPUnit\Framework\MockObject\MockObject&Employee
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
        $profileAvatar = new ProfileAvatar($this->modelMock);
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
        $profileAvatar = $this->getMockBuilder(ProfileAvatar::class)
            ->setConstructorArgs([$this->modelMock])
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
}
