<?php

namespace Tests\Feature;

use App\Jobs\CheckWebsiteJob;
use App\Models\MonitoredWebsite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class DispatchWebsiteChecksCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_dispatches_a_job_for_each_monitored_website(): void
    {
        MonitoredWebsite::factory()->count(3)->create();

        Bus::fake();

        $this->artisan('monitor:websites')
            ->expectsOutput('Dispatched 3 website checks.')
            ->assertSuccessful();

        Bus::assertDispatchedTimes(CheckWebsiteJob::class, 3);
    }
}
