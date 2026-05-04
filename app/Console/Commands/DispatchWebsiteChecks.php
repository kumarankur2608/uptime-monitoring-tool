<?php

namespace App\Console\Commands;

use App\Jobs\CheckWebsiteJob;
use App\Models\MonitoredWebsite;
use Illuminate\Console\Command;

class DispatchWebsiteChecks extends Command
{
    protected $signature = 'monitor:websites';

    protected $description = 'Dispatch uptime checks for every monitored website.';

    public function handle(): int
    {
        $chunkSize = max(1, (int) config('services.website_monitor.dispatch_chunk_size', 100));
        $queue = (string) config('services.website_monitor.queue', 'monitoring');
        $dispatched = 0;

        MonitoredWebsite::query()
            ->orderBy('id')
            ->chunkById($chunkSize, function ($websites) use (&$dispatched, $queue): void {
                foreach ($websites as $website) {
                    CheckWebsiteJob::dispatch($website)->onQueue($queue);
                    $dispatched++;
                }
            });

        $this->info("Dispatched {$dispatched} website checks.");

        return self::SUCCESS;
    }
}
