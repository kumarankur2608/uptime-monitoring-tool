<?php

namespace Tests\Unit;

use App\Exceptions\WebsiteLimitReachedException;
use App\Models\Client;
use App\Models\MonitoredWebsite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonitoredWebsiteObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_prevents_more_than_ten_websites_for_a_client(): void
    {
        $client = Client::factory()->create([
            'email' => 'limited@example.com',
        ]);

        MonitoredWebsite::factory()->for($client)->count(10)->create();

        $this->expectException(WebsiteLimitReachedException::class);
        $this->expectExceptionMessage('Client limited@example.com can only monitor 10 websites.');

        MonitoredWebsite::factory()->for($client)->create();
    }
}
