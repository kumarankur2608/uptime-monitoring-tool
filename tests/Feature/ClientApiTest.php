<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\MonitoredWebsite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_clients_for_the_select_menu(): void
    {
        $alpha = Client::factory()->create(['email' => 'alpha@example.com']);
        $zeta = Client::factory()->create(['email' => 'zeta@example.com']);

        MonitoredWebsite::factory()->for($alpha)->count(2)->create();
        MonitoredWebsite::factory()->for($zeta)->count(1)->create();

        $response = $this->getJson('/api/clients');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.email', 'alpha@example.com')
            ->assertJsonPath('data.0.websites_count', 2)
            ->assertJsonPath('data.1.email', 'zeta@example.com')
            ->assertJsonPath('data.1.websites_count', 1);
    }

    public function test_it_lists_websites_for_a_selected_client(): void
    {
        $client = Client::factory()->create();

        MonitoredWebsite::factory()->for($client)->create(['url' => 'https://zeta.example.com']);
        MonitoredWebsite::factory()->for($client)->create(['url' => 'https://alpha.example.com']);

        $response = $this->getJson("/api/clients/{$client->id}/websites");

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.url', 'https://alpha.example.com')
            ->assertJsonPath('data.1.url', 'https://zeta.example.com');
    }
}
