<?php

namespace App\Observers;

use App\Exceptions\WebsiteLimitReachedException;
use App\Models\Client;
use App\Models\MonitoredWebsite;

class MonitoredWebsiteObserver
{
    public function saving(MonitoredWebsite $website): void
    {
        $limit = (int) config('services.website_monitor.max_websites_per_client', 10);
        $client = Client::query()->find($website->client_id);

        if ($client === null) {
            return;
        }

        $existingCount = MonitoredWebsite::query()
            ->where('client_id', $website->client_id)
            ->when($website->exists, fn ($query) => $query->whereKeyNot($website->getKey()))
            ->count();

        if ($existingCount >= $limit) {
            throw WebsiteLimitReachedException::forClient($client->email, $limit);
        }
    }
}
