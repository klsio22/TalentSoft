<?php

namespace Tests\Unit\Lib;

use Lib\FlashMessage;
use PHPUnit\Framework\TestCase;

class FlashMessageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash_messages'] = [];
    }

    protected function tearDown(): void
    {
        $_SESSION['flash_messages'] = [];

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        parent::tearDown();
    }

    public function test_success(): void
    {
        FlashMessage::success('Success message');
        $flash = FlashMessage::get();

        $this->assertArrayHasKey('success', $flash);
        $this->assertEquals('Success message', $flash['success']);
    }

    public function test_danger(): void
    {
        FlashMessage::danger('Danger message');
        $flash = FlashMessage::get();

        $this->assertArrayHasKey('danger', $flash);
        $this->assertEquals('Danger message', $flash['danger']);
    }

    public function test_warning(): void
    {
        FlashMessage::warning('Warning message');
        $flash = FlashMessage::get();

        $this->assertArrayHasKey('warning', $flash);
        $this->assertEquals('Warning message', $flash['warning']);
    }

    public function test_info(): void
    {
        FlashMessage::info('Info message');
        $flash = FlashMessage::get();

        $this->assertArrayHasKey('info', $flash);
        $this->assertEquals('Info message', $flash['info']);
    }

    public function test_get(): void
    {
        FlashMessage::success('Success message');
        FlashMessage::danger('Danger message');
        FlashMessage::warning('Warning message');
        FlashMessage::info('Info message');

        $flash = FlashMessage::get();
        $this->assertEmpty(FlashMessage::get());

        $this->assertArrayHasKey('success', $flash);
        $this->assertEquals('Success message', $flash['success']);

        $this->assertArrayHasKey('danger', $flash);
        $this->assertEquals('Danger message', $flash['danger']);

        $this->assertArrayHasKey('warning', $flash);
        $this->assertEquals('Warning message', $flash['warning']);

        $this->assertArrayHasKey('info', $flash);
        $this->assertEquals('Info message', $flash['info']);
    }

    public function test_getMessages(): void
    {
        FlashMessage::success('Success message');
        FlashMessage::danger('Error message');

        $messages = FlashMessage::get();

        $this->assertIsArray($messages);
        $this->assertNotEmpty($messages);
        $this->assertEmpty($_SESSION['flash_messages']);
    }
}
