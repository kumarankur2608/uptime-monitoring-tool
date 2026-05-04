<?php

namespace App\Jobs;

use App\Enums\WebsiteStatus;
use App\Mail\WebsiteDownMail;
use App\Models\MonitoredWebsite;
use App\Services\WebsiteMonitorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class CheckWebsiteJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 20;

    public int $tries = 1;

    public bool $deleteWhenMissingModels = true;

    public function __construct(public MonitoredWebsite $website)
    {
    }

    public function handle(WebsiteMonitorService $monitor): void
    {
        $website = $this->website->fresh(['client']);

        if ($website === null) {
            return;
        }

        $result = $monitor->check($website);

        if ($result->isReachable()) {
            $website->forceFill([
                'status' => WebsiteStatus::Up,
                'last_checked_at' => now(),
                'last_response_code' => $result->statusCode,
                'last_error_message' => null,
                'last_failed_at' => null,
                'down_notified_at' => null,
            ])->save();

            return;
        }

        $alreadyAlerted = $website->status === WebsiteStatus::Down && $website->down_notified_at !== null;

        $website->forceFill([
            'status' => WebsiteStatus::Down,
            'last_checked_at' => now(),
            'last_response_code' => $result->statusCode,
            'last_error_message' => $result->errorMessage,
            'last_failed_at' => now(),
        ])->save();

        if ($alreadyAlerted) {
            return;
        }

        Mail::to($website->client->email)->queue(new WebsiteDownMail($website));

        $website->forceFill([
            'down_notified_at' => now(),
        ])->save();
    }
}
