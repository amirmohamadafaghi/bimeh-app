<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_to_customer_login_and_login_page_loads(): void
    {
        $this->get('/')
            ->assertRedirect(route('customer.login'));

        $this->get(route('customer.login'))
            ->assertOk();
    }
}
