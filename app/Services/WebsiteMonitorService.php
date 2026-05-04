<?php

namespace App\Services;

use App\Models\MonitoredWebsite;
use App\Support\WebsiteCheckResult;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Throwable;

class WebsiteMonitorService
{
    public function check(MonitoredWebsite $website): WebsiteCheckResult
    {
        $timeout = max(1, (int) config('services.website_monitor.timeout_seconds', 10));
        $userAgent = (string) config('services.website_monitor.user_agent', config('app.name').' uptime bot');

        try {
            $response = Http::accept('text/html')
                ->connectTimeout($timeout)
                ->timeout($timeout)
                ->withUserAgent($userAgent)
                ->get($website->monitoringUrl());

            if ($response->successful()) {
                return WebsiteCheckResult::up($response->status());
            }

            return WebsiteCheckResult::down(
                $response->status(),
                "Unexpected HTTP status {$response->status()}."
            );
        } catch (ConnectionException $exception) {
            return WebsiteCheckResult::down(null, $exception->getMessage());
        } catch (Throwable $exception) {
            return WebsiteCheckResult::down(null, $exception->getMessage());
        }
    }
}
