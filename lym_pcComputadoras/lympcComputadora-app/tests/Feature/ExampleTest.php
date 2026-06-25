<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_redirects_to_the_legacy_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/php/login.php');
    }

    public function test_the_legacy_login_is_served_through_laravel(): void
    {
        $response = $this->get('/php/login.php');

        $response->assertOk();
        $response->assertSee('Login - L&M PC Computadoras');
    }
}
