<?php

namespace Tests\Feature;

use App\Enums\WebsiteStatus;
use App\Jobs\CheckWebsiteJob;
use App\Mail\WebsiteDownMail;
use App\Models\Client;
use App\Models\MonitoredWebsite;
use App\Services\WebsiteMonitorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CheckWebsiteJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_marks_a_website_down_and_sends_a_single_alert_per_outage(): void
    {
        $client = Client::factory()->create([
            'email' => 'alerts@example.com',
        ]);

        $website = MonitoredWebsite::factory()->for($client)->create([
            'url' => 'https://example.com',
            'status' => WebsiteStatus::Up,
        ]);

        Http::fake([
            'https://example.com' => Http::response('Down', 500),
        ]);

        Mail::fake();

        $job = new CheckWebsiteJob($website);
        $job->handle(app(WebsiteMonitorService::class));
        $job->handle(app(WebsiteMonitorService::class));

        $website->refresh();

        $this->assertSame(WebsiteStatus::Down, $website->status);
        $this->assertNotNull($website->last_failed_at);
        $this->assertNotNull($website->down_notified_at);
        $this->assertSame(500, $website->last_response_code);
        $this->assertSame('Unexpected HTTP status 500.', $website->last_error_message);

        Mail::assertQueued(WebsiteDownMail::class, function (WebsiteDownMail $mail) use ($client): bool {
            return $mail->hasTo($client->email);
        });
        Mail::assertQueuedCount(1);
    }

    public function test_it_clears_the_down_state_after_a_successful_check(): void
    {
        $website = MonitoredWebsite::factory()->create([
            'url' => 'https://healthy.example.com',
            'status' => WebsiteStatus::Down,
            'last_error_message' => 'Timed out.',
            'last_response_code' => null,
            'last_failed_at' => now()->subMinutes(15),
            'down_notified_at' => now()->subMinutes(15),
        ]);

        Http::fake([
            'https://healthy.example.com' => Http::response('OK', 200),
        ]);

        Mail::fake();

        (new CheckWebsiteJob($website))->handle(app(WebsiteMonitorService::class));

        $website->refresh();

        $this->assertSame(WebsiteStatus::Up, $website->status);
        $this->assertNull($website->last_failed_at);
        $this->assertNull($website->last_error_message);
        $this->assertNull($website->down_notified_at);
        $this->assertSame(200, $website->last_response_code);

        Mail::assertNothingQueued();
    }
}
