<?php

namespace Tests\Acceptance\Profile;

use Tests\Acceptance\BaseAcceptanceCest;
use Tests\Support\AcceptanceTester;

/**
 * Testes de aceitação para a funcionalidade de imagem de perfil
 *
 * Esta classe testa as funcionalidades relacionadas ao upload,
 * visualização e remoção de imagens de perfil de usuário
 */
class ProfileImageCest extends BaseAcceptanceCest
{
    private const LOGIN_URL = '/login';
    private const PROFILE_URL = '/profile';
    private const DEFAULT_PASSWORD = '123456';
    private const USER_EMAIL = 'flavio@user.com';

    private const ERROR_MESSAGE_SELECTOR = '.flash-message.danger';
    private const AVATAR_INPUT_ID = 'avatar';
    private const UPLOAD_BUTTON_ID = 'upload-btn';
    private const REMOVE_BUTTON_SELECTOR = '//button[@title="Remover foto"]';
    private const AVATAR_IMAGE_SELECTOR = '//img[@alt="Foto de perfil"]';
    private const DEFAULT_AVATAR_SELECTOR = '//div[contains(@class, "bg-gradient-to-br")]//i[contains(@class, "fa-user")]';

    /**
     * Método auxiliar para fazer login como usuário padrão
     */
    private function loginAsUser(AcceptanceTester $tester): void
    {
        $tester->amOnPage(self::LOGIN_URL);
        $tester->fillField('email', self::USER_EMAIL);
        $tester->fillField('password', self::DEFAULT_PASSWORD);
        $tester->click('Entrar');
        $tester->wait(1);
    }

    /**
     * Método auxiliar para navegar até a página de perfil
     */
    private function goToProfilePage(AcceptanceTester $tester): void
    {
        $tester->amOnPage(self::PROFILE_URL);
    }

    /**
     * Teste para verificar a interface de upload de imagem de perfil
     */
    public function testProfileImageUploadInterface(AcceptanceTester $tester): void
    {
        $this->loginAsUser($tester);
        $this->goToProfilePage($tester);

        // Verificar se estamos na página de perfil
        $tester->seeInCurrentUrl(self::PROFILE_URL);

        // Verificar se o campo de upload de avatar está presente no DOM
        // Usamos seeElementInDOM em vez de seeElement porque o input pode estar oculto via CSS
        $tester->seeElementInDOM('#' . self::AVATAR_INPUT_ID);

        // Verificar se o botão de upload está inicialmente oculto
        $tester->dontSeeElement('#' . self::UPLOAD_BUTTON_ID);

        // Anexar um arquivo ao campo de upload
        $tester->attachFile(self::AVATAR_INPUT_ID, 'imgs/default-avatar.jpg');

        // Verificar se o botão de upload ficou visível após anexar o arquivo
        $tester->waitForElementVisible('#' . self::UPLOAD_BUTTON_ID, 5);
        $tester->seeElement('#' . self::UPLOAD_BUTTON_ID);
    }

    /**
     * Teste para upload de imagem de perfil válida
     */
    public function testValidImageUpload(AcceptanceTester $tester): void
    {
        $this->loginAsUser($tester);
        $this->goToProfilePage($tester);

        // Anexar uma imagem válida
        $tester->attachFile(self::AVATAR_INPUT_ID, 'imgs/default-avatar.jpg');

        // Verificar se o botão de upload está visível
        $tester->waitForElementVisible('#' . self::UPLOAD_BUTTON_ID, 5);

        // Clicar no botão de upload
        $tester->click('#' . self::UPLOAD_BUTTON_ID);

        // Aguardar redirecionamento após o envio do formulário
        $tester->wait(3); // Aumentado para 3 segundos

        // Verificar se estamos na página de perfil
        $tester->seeInCurrentUrl(self::PROFILE_URL);

        // Verificar se o upload foi bem-sucedido - usando seletores mais genéricos e robustos
        $tester->waitForElementVisible('.flash-message', 10); // Aumentado para 10 segundos
        $tester->waitForText('Sua foto de perfil foi atualizada com sucesso.', 10); // Esperar pelo texto

        // Verificar se a imagem de perfil está sendo exibida
        $tester->waitForElementVisible(self::AVATAR_IMAGE_SELECTOR, 5);
        $tester->seeElement(self::AVATAR_IMAGE_SELECTOR);
    }

    /**
     * Teste para upload de imagem de perfil inválida (formato não permitido)
     */
    public function testInvalidFormatImageUpload(AcceptanceTester $tester): void
    {
        $this->loginAsUser($tester);
        $this->goToProfilePage($tester);

        // Anexar uma imagem com formato inválido (SVG)
        $tester->attachFile(self::AVATAR_INPUT_ID, 'imgs/invalid_format.svg');

        // Verificar se o botão de upload está visível
        $tester->waitForElementVisible('#' . self::UPLOAD_BUTTON_ID, 5);

        // Clicar no botão de upload
        $tester->click('#' . self::UPLOAD_BUTTON_ID);

        // Aguardar redirecionamento após o envio do formulário
        $tester->wait(2);

        // Verificar se o upload falhou com a mensagem correta
        $tester->waitForElementVisible(self::ERROR_MESSAGE_SELECTOR, 5);
        $tester->see('Tipo de arquivo inválido', self::ERROR_MESSAGE_SELECTOR); // Mensagem exata de erro de formato
    }

    /**
     * Teste para upload de imagem de perfil inválida (tamanho excedido)
     */
    public function testOversizedImageUpload(AcceptanceTester $tester): void
    {
        $this->loginAsUser($tester);
        $this->goToProfilePage($tester);

        // Anexar um arquivo muito grande
        $tester->attachFile(self::AVATAR_INPUT_ID, 'imgs/oversized_image.jpg');

        // O JavaScript deve tornar o botão de upload visível
        $tester->waitForElementVisible('#' . self::UPLOAD_BUTTON_ID, 5);

        // Clicar no botão de upload
        $tester->click('#' . self::UPLOAD_BUTTON_ID);

        // Aguardar redirecionamento após o envio do formulário
        $tester->wait(3);

        // Verificar que o upload falhou devido ao tamanho excessivo
        // O servidor retorna HTTP 413 (Request Entity Too Large) para arquivos muito grandes
        // Isso é um comportamento esperado e confirma que o limite de tamanho está funcionando
        $tester->see('413', 'h1'); // Confirma que recebemos o erro 413
        $tester->see('Request Entity Too Large', 'h1'); // Confirma a mensagem de erro
    }

    /**
     * Teste para remoção de imagem de perfil
     */
    public function testAvatarRemoval(AcceptanceTester $tester): void
    {
        $this->loginAsUser($tester);
        $this->goToProfilePage($tester);

        // Primeiro fazer upload de uma imagem para garantir que existe algo para remover
        $tester->attachFile(self::AVATAR_INPUT_ID, 'imgs/default-avatar.jpg');
        $tester->waitForElementVisible('#' . self::UPLOAD_BUTTON_ID, 5);
        $tester->click('#' . self::UPLOAD_BUTTON_ID);
        $tester->wait(3);

        // Verificar se a imagem foi carregada com sucesso
        $tester->waitForElementVisible(self::AVATAR_IMAGE_SELECTOR, 5);

        // Verificar se o botão de remover está presente
        $tester->seeElement(self::REMOVE_BUTTON_SELECTOR);

        // Clicar no botão de remover
        $tester->click(self::REMOVE_BUTTON_SELECTOR);

        // Aguardar redirecionamento após a remoção
        $tester->wait(3); // Aumentado para 3 segundos

        // Verificar se estamos na página de perfil
        $tester->seeInCurrentUrl(self::PROFILE_URL);

        // Verificar se a remoção foi bem-sucedida - usando seletores mais genéricos e robustos
        $tester->waitForElementVisible('.flash-message', 10); // Aumentado para 10 segundos
        $tester->waitForText('Sua foto de perfil foi removida com sucesso.', 10); // Texto exato da mensagem de remoção

        // Verificar se voltou para o avatar padrão
        $tester->waitForElementVisible(self::DEFAULT_AVATAR_SELECTOR, 5);
        $tester->seeElement(self::DEFAULT_AVATAR_SELECTOR);
    }
}
