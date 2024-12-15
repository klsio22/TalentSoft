<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class RouteTest extends TestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

    public function test_root_route()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    public function test_404_route()
    {
        $response = $this->get('/404');
        $response->assertStatus(200);
        $response->assertViewIs('errors.404');
    }

    public function test_admin_login_route()
    {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
        $response->assertViewIs('auth.admin_login');
    }

    public function test_home_route_requires_authentication()
    {
        $response = $this->get('/home');
        $response->assertRedirect('/');
    }

    public function test_home_admin_route_requires_admin_role()
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);

        $response = $this->get('/home/admin');
        $response->assertStatus(403);
    }

    public function test_home_admin_route_with_admin_role()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get('/home/admin');
        $response->assertStatus(200);
        $response->assertViewIs('home.admin');

    }
}
