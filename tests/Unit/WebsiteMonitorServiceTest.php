<?php

namespace Tests\Unit;

use App\Models\MonitoredWebsite;
use App\Services\WebsiteMonitorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WebsiteMonitorServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_a_reachable_result_for_successful_responses(): void
    {
        $website = MonitoredWebsite::factory()->create([
            'url' => 'https://up.example.com',
        ]);

        Http::fake([
            'https://up.example.com' => Http::response('<html></html>', 200),
        ]);

        $result = app(WebsiteMonitorService::class)->check($website);

        $this->assertTrue($result->isReachable());
        $this->assertSame(200, $result->statusCode);
        $this->assertNull($result->errorMessage);
    }

    public function test_it_returns_a_down_result_for_http_errors(): void
    {
        $website = MonitoredWebsite::factory()->create([
            'url' => 'https://down.example.com',
        ]);

        Http::fake([
            'https://down.example.com' => Http::response('Bad gateway', 502),
        ]);

        $result = app(WebsiteMonitorService::class)->check($website);

        $this->assertFalse($result->isReachable());
        $this->assertSame(502, $result->statusCode);
        $this->assertSame('Unexpected HTTP status 502.', $result->errorMessage);
    }
}
